#!/bin/bash

echo "🚀 Packaging Frontend for cPanel upload..."

# Create package directory
FRONTEND_PACKAGE="/app/cpanel_deployment/frontend_upload_package"
rm -rf $FRONTEND_PACKAGE
mkdir -p $FRONTEND_PACKAGE

# Copy all files from build folder
echo "📦 Copying frontend build files..."
cp -r /app/frontend/build/* $FRONTEND_PACKAGE/

# Create README for frontend
cat > $FRONTEND_PACKAGE/README_FRONTEND.md << 'EOF'
# 📦 FRONTEND UPLOAD PACKAGE - QLVB System

## 📋 Nội dung package này:
- index.html
- static/ (CSS, JS, images)
- manifest.json
- robots.txt
- favicon.ico
- asset-manifest.json

## 🚀 HƯỚNG DẪN UPLOAD:

### Bước 1: Backup (Optional)
1. Vào cPanel File Manager
2. Vào /public_html/
3. Backup folder `static/` và file `index.html` (nếu có)

### Bước 2: Upload Frontend
1. Upload TẤT CẢ files từ package này vào `/public_html/`
2. **GHI ĐÈ** các file cũ khi được hỏi
3. Đảm bảo cấu trúc như sau:
   ```
   /public_html/
   ├── index.html (NEW - ghi đè)
   ├── static/ (NEW - ghi đè)
   │   ├── css/
   │   ├── js/
   │   └── media/
   ├── manifest.json (NEW - ghi đè)
   ├── robots.txt (NEW - ghi đè)
   ├── favicon.ico (NEW - ghi đè)
   └── api/ (GIỮ NGUYÊN - không động vào)
       ├── config.php
       ├── auth.php
       └── ...
   ```

### ⚠️ QUAN TRỌNG:
- **KHÔNG XÓA** folder `api/`
- **CHỈ GHI ĐÈ** các file frontend (index.html, static/, etc.)
- **GIỮ NGUYÊN** tất cả files trong folder `api/`

### Bước 3: Verify
1. Clear browser cache (Ctrl+Shift+Delete)
2. Truy cập: https://qlvb.phongkhcn.vn
3. Đăng nhập lại
4. Kiểm tra Dashboard hiển thị đúng

### Bước 4: Test
- [ ] Trang login hiển thị
- [ ] Đăng nhập thành công
- [ ] Dashboard hiển thị stats (không còn trang trắng)
- [ ] Menu navigation hoạt động
- [ ] Tất cả trang hoạt động (Documents, Categories, Menu)

## ✅ KẾT QUẢ MONG ĐỢI:

Sau khi upload:
- ✅ Trang login hiển thị bình thường
- ✅ Đăng nhập vào Dashboard - không còn trang trắng
- ✅ Tất cả chức năng hoạt động: CRUD, search, filter, upload
- ✅ Backend URL: https://qlvb.phongkhcn.vn

## 🐛 NẾU VẪN BỊ TRANG TRẮNG:

1. **Check browser console:**
   - Mở Developer Tools (F12)
   - Tab Console - xem có lỗi gì không
   - Tab Network - xem API calls có fail không

2. **Clear cache triệt để:**
   - Ctrl+Shift+Delete
   - Clear tất cả cache & cookies
   - Hoặc test trong Incognito mode

3. **Verify files uploaded:**
   - Check file manager có index.html mới không
   - Check folder static/ có files mới không
   - Check file size (index.html phải >1KB)

## 📞 BÁO LỖI:

Nếu vẫn bị trang trắng sau khi upload, cung cấp:
1. Screenshot browser console (F12 → Console tab)
2. Screenshot Network tab (F12 → Network tab)
3. URL đang truy cập
4. Bước nào đã làm trong hướng dẫn này

---

**Chúc bạn thành công! 🎉**
EOF

# Create .htaccess for public_html root (if not exists)
cat > $FRONTEND_PACKAGE/.htaccess << 'EOF'
# Enable Rewrite Engine
RewriteEngine On

# Redirect API requests to api folder
RewriteRule ^api/(.*)$ api/$1 [L,QSA]

# Redirect all other requests to index.html for React Router
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L,QSA]

# Enable CORS
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"

# Security Headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"

# Disable directory listing
Options -Indexes

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
EOF

# Create zip file
echo "🗜️  Creating zip file..."
cd $FRONTEND_PACKAGE/..
zip -r frontend_upload_package.zip frontend_upload_package/

echo "✅ Frontend package created successfully!"
echo "📍 Location: /app/cpanel_deployment/frontend_upload_package.zip"
echo ""
echo "📦 Package includes:"
echo "  - index.html (built with backend URL: https://qlvb.phongkhcn.vn)"
echo "  - static/ (CSS, JS, media files)"
echo "  - manifest.json, robots.txt, favicon.ico"
echo "  - .htaccess (for React Router)"
echo "  - README_FRONTEND.md (upload instructions)"
echo ""
echo "🚀 Ready to upload to cPanel /public_html/!"
echo ""
echo "⚠️  QUAN TRỌNG: Chỉ upload files này, KHÔNG xóa folder api/"
