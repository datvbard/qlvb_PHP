from fastapi import FastAPI, APIRouter, HTTPException, Depends, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field, EmailStr
from typing import List, Optional
import uuid
from datetime import datetime, timezone, timedelta, date
import jwt
import bcrypt
import io
import pandas as pd
from fastapi.responses import StreamingResponse

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

# Security
security = HTTPBearer()
SECRET_KEY = os.environ.get('SECRET_KEY', 'your-secret-key-change-in-production')
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 60 * 24 * 7  # 7 days

app = FastAPI()
api_router = APIRouter(prefix="/api")

# Models
class User(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    username: str
    name: str
    email: EmailStr
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class UserCreate(BaseModel):
    username: str
    password: str
    name: str
    email: EmailStr

class UserLogin(BaseModel):
    username: str
    password: str

class Token(BaseModel):
    access_token: str
    token_type: str
    user: User

class Document(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    code: str
    title: str
    doc_type: str
    assignee: str
    expiry_date: str  # ISO format date string
    summary: str
    status: str  # active, expiring, expired
    created_by: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class DocumentCreate(BaseModel):
    code: str
    title: str
    doc_type: str
    assignee: str
    expiry_date: str
    summary: str

class DocumentUpdate(BaseModel):
    code: Optional[str] = None
    title: Optional[str] = None
    doc_type: Optional[str] = None
    assignee: Optional[str] = None
    expiry_date: Optional[str] = None
    summary: Optional[str] = None

class DocumentStats(BaseModel):
    total: int
    active: int
    expiring: int
    expired: int

# Helper functions
def get_password_hash(password):
    # Truncate password to 72 bytes for bcrypt compatibility
    password_bytes = password.encode('utf-8')[:72]
    return pwd_context.hash(password_bytes)

def verify_password(plain_password, hashed_password):
    # Truncate password to 72 bytes for bcrypt compatibility
    password_bytes = plain_password.encode('utf-8')[:72]
    return pwd_context.verify(password_bytes, hashed_password)

def create_access_token(data: dict):
    to_encode = data.copy()
    expire = datetime.now(timezone.utc) + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

async def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security)):
    try:
        token = credentials.credentials
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        username: str = payload.get("sub")
        if username is None:
            raise HTTPException(status_code=401, detail="Invalid authentication credentials")
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expired")
    except jwt.JWTError:
        raise HTTPException(status_code=401, detail="Invalid authentication credentials")
    
    user = await db.users.find_one({"username": username})
    if user is None:
        raise HTTPException(status_code=401, detail="User not found")
    return User(**user)

def calculate_document_status(expiry_date_str: str) -> str:
    """Calculate status based on expiry date"""
    try:
        expiry = datetime.fromisoformat(expiry_date_str).date()
        today = datetime.now(timezone.utc).date()
        days_diff = (expiry - today).days
        
        if days_diff < 0:
            return "expired"
        elif days_diff <= 7:
            return "expiring"
        else:
            return "active"
    except:
        return "active"

# Auth routes
@api_router.post("/auth/register", response_model=Token)
async def register(user_data: UserCreate):
    # Check if username exists
    existing_user = await db.users.find_one({"username": user_data.username})
    if existing_user:
        raise HTTPException(status_code=400, detail="Username already exists")
    
    # Check if email exists
    existing_email = await db.users.find_one({"email": user_data.email})
    if existing_email:
        raise HTTPException(status_code=400, detail="Email already exists")
    
    # Create user
    user_dict = user_data.dict()
    password = user_dict.pop('password')
    hashed_password = get_password_hash(password)
    
    user = User(**user_dict)
    user_to_store = user.dict()
    user_to_store['password_hash'] = hashed_password
    
    await db.users.insert_one(user_to_store)
    
    # Create token
    access_token = create_access_token(data={"sub": user.username})
    
    return Token(access_token=access_token, token_type="bearer", user=user)

@api_router.post("/auth/login", response_model=Token)
async def login(credentials: UserLogin):
    user = await db.users.find_one({"username": credentials.username})
    if not user or not verify_password(credentials.password, user['password_hash']):
        raise HTTPException(status_code=401, detail="Invalid username or password")
    
    user_obj = User(**user)
    access_token = create_access_token(data={"sub": user_obj.username})
    
    return Token(access_token=access_token, token_type="bearer", user=user_obj)

@api_router.get("/auth/me", response_model=User)
async def get_me(current_user: User = Depends(get_current_user)):
    return current_user

# Document routes
@api_router.get("/documents", response_model=List[Document])
async def get_documents(
    doc_type: Optional[str] = None,
    search: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    query = {}
    
    if doc_type and doc_type != "all":
        query['doc_type'] = doc_type
    
    if search:
        query['$or'] = [
            {'title': {'$regex': search, '$options': 'i'}},
            {'code': {'$regex': search, '$options': 'i'}},
            {'assignee': {'$regex': search, '$options': 'i'}},
            {'summary': {'$regex': search, '$options': 'i'}}
        ]
    
    documents = await db.documents.find(query).sort('created_at', -1).to_list(1000)
    return [Document(**doc) for doc in documents]

@api_router.post("/documents", response_model=Document)
async def create_document(
    doc_data: DocumentCreate,
    current_user: User = Depends(get_current_user)
):
    # Check if code exists
    existing = await db.documents.find_one({"code": doc_data.code})
    if existing:
        raise HTTPException(status_code=400, detail="Mã văn bản đã tồn tại")
    
    doc_dict = doc_data.dict()
    status = calculate_document_status(doc_dict['expiry_date'])
    
    document = Document(
        **doc_dict,
        status=status,
        created_by=current_user.username
    )
    
    await db.documents.insert_one(document.dict())
    return document

@api_router.put("/documents/{doc_id}", response_model=Document)
async def update_document(
    doc_id: str,
    doc_data: DocumentUpdate,
    current_user: User = Depends(get_current_user)
):
    existing = await db.documents.find_one({"id": doc_id})
    if not existing:
        raise HTTPException(status_code=404, detail="Document not found")
    
    update_data = {k: v for k, v in doc_data.dict().items() if v is not None}
    
    if 'expiry_date' in update_data:
        update_data['status'] = calculate_document_status(update_data['expiry_date'])
    
    update_data['updated_at'] = datetime.now(timezone.utc)
    
    await db.documents.update_one({"id": doc_id}, {"$set": update_data})
    
    updated_doc = await db.documents.find_one({"id": doc_id})
    return Document(**updated_doc)

@api_router.delete("/documents/{doc_id}")
async def delete_document(
    doc_id: str,
    current_user: User = Depends(get_current_user)
):
    result = await db.documents.delete_one({"id": doc_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Document not found")
    return {"message": "Document deleted successfully"}

@api_router.get("/documents/stats", response_model=DocumentStats)
async def get_document_stats(current_user: User = Depends(get_current_user)):
    all_docs = await db.documents.find().to_list(10000)
    
    total = len(all_docs)
    active = sum(1 for doc in all_docs if doc.get('status') == 'active')
    expiring = sum(1 for doc in all_docs if doc.get('status') == 'expiring')
    expired = sum(1 for doc in all_docs if doc.get('status') == 'expired')
    
    return DocumentStats(total=total, active=active, expiring=expiring, expired=expired)

@api_router.get("/documents/export")
async def export_documents(current_user: User = Depends(get_current_user)):
    documents = await db.documents.find().sort('created_at', -1).to_list(10000)
    
    # Prepare data for export
    data = []
    for doc in documents:
        data.append({
            'Mã Văn Bản': doc.get('code', ''),
            'Tên Văn Bản': doc.get('title', ''),
            'Loại Văn Bản': doc.get('doc_type', ''),
            'Cán Bộ Cập Nhật': doc.get('assignee', ''),
            'Ngày Hết Hạn': doc.get('expiry_date', ''),
            'Trạng Thái': doc.get('status', ''),
            'Tóm Tắt': doc.get('summary', '')
        })
    
    df = pd.DataFrame(data)
    
    # Create Excel file in memory
    output = io.BytesIO()
    with pd.ExcelWriter(output, engine='openpyxl') as writer:
        df.to_excel(writer, index=False, sheet_name='Văn Bản')
    output.seek(0)
    
    return StreamingResponse(
        output,
        media_type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        headers={'Content-Disposition': 'attachment; filename=van-ban.xlsx'}
    )

@api_router.get("/document-types")
async def get_document_types():
    return [
        {"value": "hop_dong", "label": "Hợp đồng", "icon": "📄"},
        {"value": "quyet_dinh", "label": "Quyết định", "icon": "📋"},
        {"value": "thong_bao", "label": "Thông báo", "icon": "📢"},
        {"value": "cong_van", "label": "Công văn", "icon": "📝"},
        {"value": "bao_cao", "label": "Báo cáo", "icon": "📊"},
        {"value": "khac", "label": "Khác", "icon": "📁"}
    ]

app.include_router(api_router)

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()
