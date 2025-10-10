#!/bin/bash

# ===================================
# SCRIPT BUILD VÀ CHUẨN BỊ DEPLOY
# ===================================

echo "🚀 Bắt đầu build và chuẩn bị deploy..."

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Kiểm tra folder frontend
if [ ! -d "/app/frontend" ]; then
    echo -e "${RED}❌ Folder frontend không tồn tại!${NC}"
    exit 1
fi

# Build frontend
echo -e "${YELLOW}📦 Building React frontend...${NC}"
cd /app/frontend

# Install dependencies nếu cần
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📥 Installing dependencies...${NC}"
    npm install
fi

# Build production
echo -e "${YELLOW}🔨 Building for production...${NC}"
npm run build

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Build thành công!${NC}"
else
    echo -e "${RED}❌ Build thất bại!${NC}"
    exit 1
fi

# Tạo package folder
echo -e "${YELLOW}📦 Tạo deployment package...${NC}"
cd /app
mkdir -p /app/deployment_package

# Copy backend files
echo -e "${YELLOW}📂 Copy backend files...${NC}"
cp -r /app/cpanel_deployment/api /app/deployment_package/

# Copy frontend build
echo -e "${YELLOW}📂 Copy frontend build...${NC}"
cp -r /app/frontend/build/* /app/deployment_package/

# Copy database
echo -e "${YELLOW}📂 Copy database.sql...${NC}"
cp /app/database.sql /app/deployment_package/

# Copy documentation
echo -e "${YELLOW}📄 Copy documentation...${NC}"
cp /app/cpanel_deployment/README.md /app/deployment_package/
cp /app/cpanel_deployment/HUONG_DAN_DEPLOY.md /app/deployment_package/
cp /app/backend/google_drive_config.md /app/deployment_package/

# Create .htaccess for root
echo -e "${YELLOW}📝 Creating root .htaccess...${NC}"
cat > /app/deployment_package/.htaccess << 'EOF'
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

# Create deployment notes
echo -e "${YELLOW}📋 Creating deployment notes...${NC}"
cat > /app/deployment_package/DEPLOY_NOTES.txt << EOF
DEPLOYMENT PACKAGE - HỆ THỐNG QUẢN LÝ VĂN BẢN
Domain: qlvb.phongkhcn.vn
Build Date: $(date)

===========================================
CẤU TRÚC PACKAGE
===========================================
.
├── index.html              # React app entry
├── static/                 # React static files
├── manifest.json
├── robots.txt
├── .htaccess              # Main rewrite rules
├── api/                   # Backend PHP
│   ├── config.php
│   ├── auth.php
│   ├── categories.php
│   ├── menu.php
│   ├── documents.php
│   ├── files.php
│   └── .htaccess
├── database.sql           # Import vào MySQL
├── README.md              # Thông tin package
├── HUONG_DAN_DEPLOY.md    # Hướng dẫn chi tiết
└── google_drive_config.md # Cấu hình Google Drive

===========================================
UPLOAD VÀO CPANEL
===========================================
1. Upload TẤT CẢ files vào /public_html/
2. Import database.sql vào phpMyAdmin
3. Edit api/config.php - cập nhật DB credentials
4. Set permissions:
   - Folders: 755
   - Files: 644
5. Test: https://qlvb.phongkhcn.vn

===========================================
THÔNG TIN ĐĂNG NHẬP
===========================================
Username: admin
Password: admin123

⚠️  ĐỔI PASSWORD NGAY SAU KHI DEPLOY!

===========================================
API BACKEND URL
===========================================
https://qlvb.phongkhcn.vn/api

Test API:
curl https://qlvb.phongkhcn.vn/api/auth/login \\
  -H "Content-Type: application/json" \\
  -d '{"username":"admin","password":"admin123"}'

===========================================
HỖ TRỢ
===========================================
- Đọc HUONG_DAN_DEPLOY.md cho hướng dẫn chi tiết
- Check PHP error log trong cPanel nếu có lỗi
- Verify .htaccess có mod_rewrite enabled

Good luck! 🚀
EOF

# Create zip file
echo -e "${YELLOW}🗜️  Creating zip archive...${NC}"
cd /app
zip -r deployment_package_$(date +%Y%m%d_%H%M%S).zip deployment_package/

echo -e "${GREEN}✅ Hoàn tất!${NC}"
echo ""
echo -e "${GREEN}📦 Package đã sẵn sàng tại:${NC}"
echo -e "   ${YELLOW}/app/deployment_package/${NC}"
echo ""
echo -e "${GREEN}📝 Hướng dẫn deploy:${NC}"
echo -e "   ${YELLOW}/app/deployment_package/HUONG_DAN_DEPLOY.md${NC}"
echo ""
echo -e "${GREEN}🌐 Deploy lên:${NC}"
echo -e "   ${YELLOW}https://qlvb.phongkhcn.vn${NC}"
