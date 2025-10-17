# 🔧 HƯỚNG DẪN SỬA LỖI GOOGLE DRIVE INTEGRATION

## ❌ Vấn Đề
- Upload file **có vẻ thành công** nhưng file **không xuất hiện** trong Google Drive folder
- File được upload **không thể xem/tải xuống**

---

## 🔍 NGUYÊN NHÂN CÓ THỂ

1. **Service Account không có quyền truy cập folder**
2. **Folder ID sai hoặc không tồn tại**
3. **Google API Client chưa được cài đặt đúng**
4. **Credentials file sai hoặc thiếu**
5. **API chưa được enable trong Google Cloud Console**

---

## ✅ GIẢI PHÁP CHI TIẾT

### 📋 BƯỚC 1: Kiểm tra Service Account và Credentials

#### 1.1. Xác minh file credentials tồn tại

1. **Vào File Manager** trong cPanel
2. Navigate đến `/public_html/api/`
3. Kiểm tra file **`google-credentials.json`** có tồn tại không
4. **Download về và mở file** để kiểm tra:

```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "your-service-account@your-project.iam.gserviceaccount.com",
  "client_id": "...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "..."
}
```

5. **Quan trọng:** Lưu lại **`client_email`** - bạn sẽ cần nó ở bước sau

---

#### 1.2. Cấp quyền Service Account vào Google Drive Folder

**ĐÂY LÀ BƯỚC QUAN TRỌNG NHẤT!**

1. **Truy cập Google Drive:** `https://drive.google.com`

2. **Tìm folder có ID:** `1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
   - Cách tìm: Mở folder, URL sẽ có dạng:
   ```
   https://drive.google.com/drive/folders/1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0
   ```

3. **Click chuột phải vào folder** → **Share** (Chia sẻ)

4. **Thêm Service Account email:**
   - Paste email từ `client_email` trong file credentials
   - Ví dụ: `your-service-account@your-project.iam.gserviceaccount.com`

5. **Chọn quyền: "Editor"** (Người chỉnh sửa)
   - **KHÔNG chọn "Viewer"** - phải là **"Editor"** hoặc **"Owner"**

6. **QUAN TRỌNG:** Bỏ tick ô **"Notify people"**
   - Service account không có email thật nên không thể nhận notification

7. **Click "Share"** (Chia sẻ)

8. **Xác nhận lại:**
   - Vào folder settings (icon bánh răng)
   - Click "Share" → kiểm tra service account email có trong danh sách với role "Editor"

---

### 📦 BƯỚC 2: Cài đặt Google API PHP Client

#### 2.1. Kiểm tra Composer có sẵn không

1. **SSH vào server** (nếu có quyền SSH):
```bash
cd /home/username/public_html/api
composer --version
```

2. **Nếu composer có sẵn:**
```bash
composer require google/apiclient:^2.15
```

---

#### 2.2. Nếu KHÔNG có Composer (Manual Installation)

**Cách 1: Upload pre-installed vendor folder**

1. **Trên máy local** có composer, chạy:
```bash
mkdir temp-google-api
cd temp-google-api
composer require google/apiclient:^2.15
```

2. **Nén folder `vendor`:**
```bash
zip -r vendor.zip vendor/
```

3. **Upload `vendor.zip` lên cPanel:**
   - Vào File Manager
   - Upload vào `/public_html/api/`
   - Click chuột phải → **Extract**

4. **Kiểm tra cấu trúc:**
```
/public_html/api/
├── vendor/
│   ├── autoload.php
│   ├── composer/
│   └── google/
├── config.php
├── google_drive.php
└── ...
```

**Cách 2: Download từ GitHub**

1. Download: https://github.com/googleapis/google-api-php-client/releases
2. Giải nén và upload folder `vendor` như hướng dẫn trên

---

### ⚙️ BƯỚC 3: Cấu hình config.php

1. **Mở file `/public_html/api/config.php`**

2. **Cập nhật các thông số Google Drive:**

```php
// ===================================
// GOOGLE DRIVE CONFIGURATION
// ===================================
define('GOOGLE_DRIVE_ENABLED', true);  // ← THAY ĐỔI THÀNH true
define('GOOGLE_CREDENTIALS_FILE', __DIR__ . '/google-credentials.json');
define('GOOGLE_DRIVE_FOLDER_ID', '1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0');  // ← ĐIỀN FOLDER ID
```

3. **Save file**

---

### 🧪 BƯỚC 4: Test Google Drive Integration

#### Test 1: Chạy test_google_drive.php

1. **Truy cập:** `https://qlvb.phongkhcn.vn/api/test_google_drive.php`

2. **Kết quả mong đợi:**

```json
{
  "overall_status": "OK",
  "timestamp": "2025-01-XX XX:XX:XX",
  "tests": {
    "php_version": {
      "version": "8.0.x",
      "status": "OK"
    },
    "google_drive_enabled": {
      "value": true,
      "status": "OK"
    },
    "credentials_file": {
      "path": "/home/.../public_html/api/google-credentials.json",
      "exists": true,
      "status": "OK"
    },
    "credentials_content": {
      "type": "service_account",
      "client_email": "your-service-account@...",
      "status": "OK"
    },
    "folder_id": {
      "value": "1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0",
      "status": "OK"
    },
    "composer_autoload": {
      "path": "/home/.../public_html/api/vendor/autoload.php",
      "exists": true,
      "status": "OK"
    },
    "google_client": {
      "status": "OK",
      "message": "Google Drive service initialized successfully"
    },
    "drive_access": {
      "status": "OK",
      "files_count": 0,
      "message": "Successfully accessed Google Drive folder"
    }
  }
}
```

3. **Phân tích kết quả:**

   - ✅ **Tất cả status = "OK"**: Hoàn hảo, chuyển sang test upload
   - ❌ **credentials_file status = "FAIL"**: File credentials không tồn tại → upload lại
   - ❌ **composer_autoload status = "FAIL"**: Google API Client chưa cài → làm lại BƯỚC 2
   - ❌ **drive_access status = "FAIL"**: Service account không có quyền → làm lại BƯỚC 1.2

---

#### Test 2: Upload file thử nghiệm

1. **Tạo file test:** `/public_html/api/test_upload.php`

```php
<?php
require_once 'config.php';
require_once 'google_drive.php';

try {
    // Create a test file
    $testContent = 'This is a test file uploaded at ' . date('Y-m-d H:i:s');
    $testFile = UPLOAD_PATH . '/test_' . time() . '.txt';
    file_put_contents($testFile, $testContent);
    
    // Upload to Google Drive
    $result = uploadToGoogleDrive($testFile, 'test_upload_' . time() . '.txt', 'text/plain');
    
    // Delete local file
    unlink($testFile);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'File uploaded to Google Drive successfully',
        'file_id' => $result['id'],
        'view_link' => $result['web_view_link'],
        'download_link' => $result['web_content_link']
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
```

2. **Chạy test:** `https://qlvb.phongkhcn.vn/api/test_upload.php`

3. **Kết quả mong đợi:**

```json
{
    "status": "success",
    "message": "File uploaded to Google Drive successfully",
    "file_id": "1aBcDeFgHiJkLmNoPqRsTuVwXyZ",
    "view_link": "https://drive.google.com/file/d/1aBcDeFgHiJkLmNoPqRsTuVwXyZ/view",
    "download_link": "https://drive.google.com/uc?id=1aBcDeFgHiJkLmNoPqRsTuVwXyZ&export=download"
}
```

4. **Xác minh trong Google Drive:**
   - Truy cập folder: `https://drive.google.com/drive/folders/1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
   - Kiểm tra file `test_upload_XXXXX.txt` có xuất hiện không
   - Thử click vào file để xem nội dung

---

### 🔒 BƯỚC 5: Kiểm tra File Permissions trong Google Drive

#### Vấn đề: File upload được nhưng không xem/download được

**Nguyên nhân:** File không được set permission public

**Giải pháp:** Đã có trong code `google_drive.php` (dòng 57-61):

```php
// Make file accessible via link
$permission = new Google_Service_Drive_Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);
$service->permissions->create($file->id, $permission);
```

**Kiểm tra lại code:**

1. Mở `/public_html/api/google_drive.php`
2. Đảm bảo dòng 57-61 TỒN TẠI và KHÔNG BỊ COMMENT
3. Nếu thiếu, thêm vào sau dòng 54 (sau `$file = $service->files->create(...)`)

---

### 🔍 BƯỚC 6: Kiểm tra Google Cloud Console

1. **Truy cập:** https://console.cloud.google.com/

2. **Chọn project** của bạn (project_id trong credentials file)

3. **Enable Google Drive API:**
   - Vào **APIs & Services** → **Library**
   - Tìm **"Google Drive API"**
   - Click **"ENABLE"** (nếu chưa enable)

4. **Kiểm tra Service Account:**
   - Vào **IAM & Admin** → **Service Accounts**
   - Kiểm tra service account có tồn tại không
   - Status phải là **"Enabled"**

5. **Kiểm tra API quota:**
   - Vào **APIs & Services** → **Dashboard**
   - Click **"Google Drive API"**
   - Xem **"Quotas"** → đảm bảo chưa exceed limit

---

## 🐛 TROUBLESHOOTING

### ❌ Lỗi: "Insufficient permissions"

**Giải pháp:**
- Làm lại **BƯỚC 1.2**: Chia sẻ folder với service account email
- Đảm bảo role là **"Editor"** KHÔNG phải "Viewer"
- Đợi 1-2 phút để permission được cập nhật

---

### ❌ Lỗi: "File not found" hoặc "Invalid folder ID"

**Giải pháp:**

1. **Kiểm tra Folder ID:**
   - Mở folder trong Google Drive
   - Copy ID từ URL:
   ```
   https://drive.google.com/drive/folders/[COPY_THIS_PART]
   ```

2. **Đảm bảo folder là của cùng Google Account:**
   - Account tạo service account
   - Account owner của folder

3. **Thử tạo folder mới:**
   - Tạo folder mới trong Google Drive
   - Share với service account
   - Dùng folder ID mới

---

### ❌ Lỗi: "Failed to load credentials"

**Giải pháp:**

1. **Kiểm tra định dạng JSON:**
   - Mở `google-credentials.json`
   - Paste vào: https://jsonlint.com/
   - Fix lỗi syntax nếu có

2. **Kiểm tra line breaks trong private_key:**
   - Private key phải có `\n` giữa các dòng
   - Ví dụ: `"-----BEGIN PRIVATE KEY-----\nMIIE...\n-----END PRIVATE KEY-----\n"`

3. **Re-download credentials:**
   - Vào Google Cloud Console
   - Tạo new key cho service account
   - Download và upload lại

---

### ❌ Lỗi: "Class 'Google_Client' not found"

**Giải pháp:**
- Làm lại **BƯỚC 2**: Cài đặt Google API Client
- Kiểm tra file `/public_html/api/vendor/autoload.php` tồn tại
- Đảm bảo `require_once 'vendor/autoload.php';` trong code

---

## 📊 LOGS VÀ DEBUGGING

### Bật error logging

1. **Thêm vào đầu file `config.php`:**

```php
// Development only - REMOVE in production
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

2. **Check application logs:**
   - File logs: `/home/username/logs/error_YYYY-MM-DD.log`
   - Hoặc cPanel → **Errors** → **Error Log**

3. **Add debug logging in google_drive.php:**

```php
// Thêm vào trong function uploadToGoogleDrive
logError('Starting upload: ' . $fileName);
logError('Folder ID: ' . GOOGLE_DRIVE_FOLDER_ID);

// Sau khi upload
logError('Upload successful. File ID: ' . $file->id);
```

---

## ✅ CHECKLIST HOÀN THÀNH

- [ ] File `google-credentials.json` tồn tại và đúng định dạng
- [ ] Service account email đã được share vào folder với role **Editor**
- [ ] Google Drive API đã enabled trong Google Cloud Console
- [ ] Google API PHP Client đã cài đặt (`vendor/autoload.php` tồn tại)
- [ ] `config.php` đã set `GOOGLE_DRIVE_ENABLED = true`
- [ ] `config.php` đã set đúng `GOOGLE_DRIVE_FOLDER_ID`
- [ ] `test_google_drive.php` trả về tất cả status "OK"
- [ ] `test_upload.php` upload thành công
- [ ] File xuất hiện trong Google Drive folder
- [ ] File có thể xem và download từ link

---

## 🎉 HOÀN TẤT

Sau khi tất cả tests pass, Google Drive integration đã sẵn sàng!

**Xóa các test files:**
```bash
rm /public_html/api/test-php.php
rm /public_html/api/test-json.php
rm /public_html/api/test_upload.php
```

**Tắt error display trong production:**
```php
// Trong config.php, remove hoặc comment:
// ini_set('display_errors', 1);
```

---

**Google Drive integration đã hoạt động! Bây giờ user có thể upload file vào documents!** ✅
