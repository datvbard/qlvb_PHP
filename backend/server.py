from fastapi import FastAPI, APIRouter, HTTPException, Depends, status, UploadFile, File
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
from datetime import datetime, timezone, timedelta
import jwt
import bcrypt
import io
import pandas as pd
from fastapi.responses import StreamingResponse
import tempfile
import shutil
from google_drive_service import drive_service

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

# ============ MODELS ============

class User(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    username: str
    name: str
    email: EmailStr
    role: str = "user"  # user, admin
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

# Category (Danh mục văn bản)
class Category(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    name: str
    type: str  # chuyen_mon, dang
    icon: str = "📄"
    order: int = 0
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class CategoryCreate(BaseModel):
    name: str
    type: str
    icon: Optional[str] = "📄"
    order: Optional[int] = 0

class CategoryUpdate(BaseModel):
    name: Optional[str] = None
    icon: Optional[str] = None
    order: Optional[int] = None

# Menu Item (Menu nghiệp vụ)
class MenuItem(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    title: str
    path: str
    icon: str = "📋"
    order: int = 0
    parent_id: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class MenuItemCreate(BaseModel):
    title: str
    path: str
    icon: Optional[str] = "📋"
    order: Optional[int] = 0
    parent_id: Optional[str] = None

class MenuItemUpdate(BaseModel):
    title: Optional[str] = None
    path: Optional[str] = None
    icon: Optional[str] = None
    order: Optional[int] = None
    parent_id: Optional[str] = None

# File Attachment
class FileAttachment(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    document_id: str
    filename: str
    google_drive_id: Optional[str] = None
    google_drive_url: Optional[str] = None
    mime_type: str
    size: int
    uploaded_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Document
class Document(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    code: str
    title: str
    category_id: str
    assignee: str
    expiry_date: str
    summary: str
    status: str
    created_by: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class DocumentCreate(BaseModel):
    code: str
    title: str
    category_id: str
    assignee: str
    expiry_date: str
    summary: str

class DocumentUpdate(BaseModel):
    code: Optional[str] = None
    title: Optional[str] = None
    category_id: Optional[str] = None
    assignee: Optional[str] = None
    expiry_date: Optional[str] = None
    summary: Optional[str] = None

class DocumentStats(BaseModel):
    total: int
    active: int
    expiring: int
    expired: int

# ============ HELPER FUNCTIONS ============

def get_password_hash(password: str) -> str:
    password_bytes = password.encode('utf-8')
    salt = bcrypt.gensalt()
    hashed = bcrypt.hashpw(password_bytes, salt)
    return hashed.decode('utf-8')

def verify_password(plain_password: str, hashed_password: str) -> bool:
    password_bytes = plain_password.encode('utf-8')
    hashed_bytes = hashed_password.encode('utf-8')
    return bcrypt.checkpw(password_bytes, hashed_bytes)

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

async def get_admin_user(current_user: User = Depends(get_current_user)):
    if current_user.role != "admin":
        raise HTTPException(status_code=403, detail="Admin access required")
    return current_user

def calculate_document_status(expiry_date_str: str) -> str:
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

# ============ AUTH ROUTES ============

@api_router.post("/auth/register", response_model=Token)
async def register(user_data: UserCreate):
    existing_user = await db.users.find_one({"username": user_data.username})
    if existing_user:
        raise HTTPException(status_code=400, detail="Username already exists")
    
    existing_email = await db.users.find_one({"email": user_data.email})
    if existing_email:
        raise HTTPException(status_code=400, detail="Email already exists")
    
    user_dict = user_data.dict()
    password = user_dict.pop('password')
    hashed_password = get_password_hash(password)
    
    # First user is admin
    user_count = await db.users.count_documents({})
    role = "admin" if user_count == 0 else "user"
    
    user = User(**user_dict, role=role)
    user_to_store = user.dict()
    user_to_store['password_hash'] = hashed_password
    
    await db.users.insert_one(user_to_store)
    
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

# ============ CATEGORY ROUTES ============

@api_router.get("/categories", response_model=List[Category])
async def get_categories(current_user: User = Depends(get_current_user)):
    categories = await db.categories.find().sort('order', 1).to_list(1000)
    return [Category(**cat) for cat in categories]

@api_router.get("/categories/type/{cat_type}", response_model=List[Category])
async def get_categories_by_type(cat_type: str, current_user: User = Depends(get_current_user)):
    categories = await db.categories.find({"type": cat_type}).sort('order', 1).to_list(1000)
    return [Category(**cat) for cat in categories]

@api_router.post("/categories", response_model=Category)
async def create_category(cat_data: CategoryCreate, admin: User = Depends(get_admin_user)):
    existing = await db.categories.find_one({"name": cat_data.name, "type": cat_data.type})
    if existing:
        raise HTTPException(status_code=400, detail="Danh mục này đã tồn tại")
    
    category = Category(**cat_data.dict())
    await db.categories.insert_one(category.dict())
    return category

@api_router.put("/categories/{cat_id}", response_model=Category)
async def update_category(cat_id: str, cat_data: CategoryUpdate, admin: User = Depends(get_admin_user)):
    existing = await db.categories.find_one({"id": cat_id})
    if not existing:
        raise HTTPException(status_code=404, detail="Category not found")
    
    update_data = {k: v for k, v in cat_data.dict().items() if v is not None}
    
    if update_data:
        await db.categories.update_one({"id": cat_id}, {"$set": update_data})
    
    updated_cat = await db.categories.find_one({"id": cat_id})
    return Category(**updated_cat)

@api_router.delete("/categories/{cat_id}")
async def delete_category(cat_id: str, admin: User = Depends(get_admin_user)):
    # Check if any documents use this category
    doc_count = await db.documents.count_documents({"category_id": cat_id})
    if doc_count > 0:
        raise HTTPException(status_code=400, detail=f"Không thể xóa danh mục đang được sử dụng bởi {doc_count} văn bản")
    
    result = await db.categories.delete_one({"id": cat_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Category not found")
    return {"message": "Category deleted successfully"}

# ============ MENU ROUTES ============

@api_router.get("/menu-items", response_model=List[MenuItem])
async def get_menu_items(current_user: User = Depends(get_current_user)):
    menu_items = await db.menu_items.find().sort('order', 1).to_list(1000)
    return [MenuItem(**item) for item in menu_items]

@api_router.post("/menu-items", response_model=MenuItem)
async def create_menu_item(menu_data: MenuItemCreate, admin: User = Depends(get_admin_user)):
    menu_item = MenuItem(**menu_data.dict())
    await db.menu_items.insert_one(menu_item.dict())
    return menu_item

@api_router.put("/menu-items/{menu_id}", response_model=MenuItem)
async def update_menu_item(menu_id: str, menu_data: MenuItemUpdate, admin: User = Depends(get_admin_user)):
    existing = await db.menu_items.find_one({"id": menu_id})
    if not existing:
        raise HTTPException(status_code=404, detail="Menu item not found")
    
    update_data = {k: v for k, v in menu_data.dict().items() if v is not None}
    
    if update_data:
        await db.menu_items.update_one({"id": menu_id}, {"$set": update_data})
    
    updated_menu = await db.menu_items.find_one({"id": menu_id})
    return MenuItem(**updated_menu)

@api_router.delete("/menu-items/{menu_id}")
async def delete_menu_item(menu_id: str, admin: User = Depends(get_admin_user)):
    result = await db.menu_items.delete_one({"id": menu_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Menu item not found")
    return {"message": "Menu item deleted successfully"}

# ============ DOCUMENT ROUTES ============

@api_router.get("/documents", response_model=List[Document])
async def get_documents(
    category_id: Optional[str] = None,
    search: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    query = {}
    
    if category_id:
        query['category_id'] = category_id
    
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
    # Delete associated files from Google Drive
    files = await db.files.find({"document_id": doc_id}).to_list(1000)
    for file in files:
        if file.get('google_drive_id') and drive_service.is_configured():
            drive_service.delete_file(file['google_drive_id'])
    
    # Delete file records
    await db.files.delete_many({"document_id": doc_id})
    
    # Delete document
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
    
    # Get category names
    categories = await db.categories.find().to_list(1000)
    cat_map = {cat['id']: cat['name'] for cat in categories}
    
    data = []
    for doc in documents:
        data.append({
            'Mã Văn Bản': doc.get('code', ''),
            'Tên Văn Bản': doc.get('title', ''),
            'Danh Mục': cat_map.get(doc.get('category_id', ''), ''),
            'Cán Bộ Cập Nhật': doc.get('assignee', ''),
            'Ngày Hết Hạn': doc.get('expiry_date', ''),
            'Trạng Thái': doc.get('status', ''),
            'Tóm Tắt': doc.get('summary', '')
        })
    
    df = pd.DataFrame(data)
    
    output = io.BytesIO()
    with pd.ExcelWriter(output, engine='openpyxl') as writer:
        df.to_excel(writer, index=False, sheet_name='Văn Bản')
    output.seek(0)
    
    return StreamingResponse(
        output,
        media_type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        headers={'Content-Disposition': 'attachment; filename=van-ban.xlsx'}
    )

# ============ FILE ROUTES ============

@api_router.post("/documents/{doc_id}/upload")
async def upload_file(
    doc_id: str,
    file: UploadFile = File(...),
    current_user: User = Depends(get_current_user)
):
    # Check if document exists
    doc = await db.documents.find_one({"id": doc_id})
    if not doc:
        raise HTTPException(status_code=404, detail="Document not found")
    
    if not drive_service.is_configured():
        raise HTTPException(status_code=503, detail="Google Drive not configured. Please configure credentials.")
    
    # Save file temporarily
    temp_dir = tempfile.mkdtemp()
    temp_file_path = os.path.join(temp_dir, file.filename)
    
    try:
        with open(temp_file_path, 'wb') as buffer:
            shutil.copyfileobj(file.file, buffer)
        
        # Upload to Google Drive
        drive_result = drive_service.upload_file(
            temp_file_path,
            file.filename,
            file.content_type
        )
        
        # Save file record
        file_attachment = FileAttachment(
            document_id=doc_id,
            filename=file.filename,
            google_drive_id=drive_result['id'],
            google_drive_url=drive_result['web_view_link'],
            mime_type=file.content_type,
            size=os.path.getsize(temp_file_path)
        )
        
        await db.files.insert_one(file_attachment.dict())
        
        return {
            "message": "File uploaded successfully",
            "file": file_attachment.dict()
        }
    
    finally:
        # Cleanup
        if os.path.exists(temp_file_path):
            os.remove(temp_file_path)
        if os.path.exists(temp_dir):
            os.rmdir(temp_dir)

@api_router.get("/documents/{doc_id}/files", response_model=List[FileAttachment])
async def get_document_files(
    doc_id: str,
    current_user: User = Depends(get_current_user)
):
    files = await db.files.find({"document_id": doc_id}).to_list(1000)
    return [FileAttachment(**f) for f in files]

@api_router.delete("/files/{file_id}")
async def delete_file(
    file_id: str,
    current_user: User = Depends(get_current_user)
):
    file = await db.files.find_one({"id": file_id})
    if not file:
        raise HTTPException(status_code=404, detail="File not found")
    
    # Delete from Google Drive
    if file.get('google_drive_id') and drive_service.is_configured():
        drive_service.delete_file(file['google_drive_id'])
    
    # Delete record
    await db.files.delete_one({"id": file_id})
    
    return {"message": "File deleted successfully"}

# ============ SETUP ============

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
