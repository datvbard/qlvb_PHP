# Hướng Dẫn Cấu Hình Google Drive API

## Bước 1: Tạo Google Cloud Project

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Nhấn "Select a project" > "New Project"
3. Nhập tên project: "Document Management System"
4. Nhấn "Create"

## Bước 2: Enable Google Drive API

1. Vào menu "APIs & Services" > "Library"
2. Tìm kiếm "Google Drive API"
3. Nhấn vào "Google Drive API"
4. Nhấn "Enable"

## Bước 3: Tạo Service Account

1. Vào "APIs & Services" > "Credentials"
2. Nhấn "Create Credentials" > "Service Account"
3. Nhập thông tin:
   - Service account name: `doc-management-service`
   - Service account ID: (tự động tạo)
4. Nhấn "Create and Continue"
5. Grant role: "Editor" hoặc "Owner"
6. Nhấn "Done"

## Bước 4: Tạo Key cho Service Account

1. Trong danh sách Service Accounts, nhấn vào service account vừa tạo
2. Chọn tab "Keys"
3. Nhấn "Add Key" > "Create new key"
4. Chọn "JSON"
5. Nhấn "Create" - file JSON sẽ được tải về

## Bước 5: Tạo Google Drive Folder

1. Truy cập [Google Drive](https://drive.google.com/)
2. Tạo folder mới: "Document_Management"
3. Click chuột phải vào folder > "Share"
4. Thêm email của Service Account (có dạng: xxx@xxx.iam.gserviceaccount.com)
5. Chọn quyền "Editor"
6. Nhấn "Share"
7. Copy Folder ID từ URL (dạng: https://drive.google.com/drive/folders/[FOLDER_ID])

## Bước 6: Cấu hình Backend

1. Đổi tên file JSON vừa tải về thành `google-credentials.json`
2. Copy file vào thư mục `/app/backend/`
3. Thêm vào file `/app/backend/.env`:

```env
GOOGLE_DRIVE_FOLDER_ID=your_folder_id_here
GOOGLE_APPLICATION_CREDENTIALS=/app/backend/google-credentials.json
```

## Bước 7: Cài đặt thư viện

Thư viện đã được cài sẵn trong requirements.txt:
- google-auth
- google-api-python-client

## Bước 8: Test kết nối

Sau khi cấu hình xong, restart backend:
```bash
sudo supervisorctl restart backend
```

Kiểm tra logs:
```bash
tail -f /var/log/supervisor/backend.err.log
```

## Lưu ý bảo mật:

⚠️ **QUAN TRỌNG:**
- File `google-credentials.json` chứa thông tin nhạy cảm
- KHÔNG commit file này lên Git
- File đã được thêm vào `.gitignore`
- Chỉ admin mới có quyền truy cập file này

## Troubleshooting:

**Lỗi "Permission denied":**
- Kiểm tra lại email Service Account đã được share folder chưa
- Kiểm tra quyền của Service Account (phải là Editor)

**Lỗi "File not found":**
- Kiểm tra đường dẫn GOOGLE_APPLICATION_CREDENTIALS
- Đảm bảo file google-credentials.json tồn tại

**Lỗi "Invalid credentials":**
- Tạo lại Service Account key
- Download file JSON mới
- Cập nhật lại file google-credentials.json
