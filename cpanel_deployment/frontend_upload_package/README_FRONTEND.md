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
