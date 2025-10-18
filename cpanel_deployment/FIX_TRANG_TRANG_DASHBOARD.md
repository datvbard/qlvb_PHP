# 🚀 HƯỚNG DẪN NHANH - FIX TRANG TRẮNG DASHBOARD

## ❌ VẤN ĐỀ
- Đăng nhập thành công
- Redirect đến /dashboard
- Trang trắng, không có nội dung

## ✅ NGUYÊN NHÂN
Frontend đã upload nhưng **backend URL chưa đúng** trong build cũ.

## 🔧 GIẢI PHÁP - 3 BƯỚC (5 phút)

### Bước 1: Download Package Mới
File: `/app/cpanel_deployment/frontend_upload_package.zip` (871KB)

Package này đã build với:
- ✅ Backend URL: `https://qlvb.phongkhcn.vn`
- ✅ Tất cả components
- ✅ React Router đã configure đúng

### Bước 2: Upload lên cPanel

1. **Vào cPanel File Manager**
2. **Navigate đến `/public_html/`**
3. **Upload & Extract:**
   - Upload file `frontend_upload_package.zip`
   - Click chuột phải → **Extract**
   - Vào folder `frontend_upload_package/`
   - **Select All** (Ctrl+A)
   - **Move** (hoặc Copy) tất cả files ra `/public_html/`
   - **GHI ĐÈ** (Overwrite) các file cũ khi được hỏi

4. **Cấu trúc cuối cùng trong /public_html/:**
   ```
   /public_html/
   ├── index.html (MỚI - đã ghi đè)
   ├── static/ (MỚI - đã ghi đè)
   │   ├── css/
   │   ├── js/
   ├── .htaccess (MỚI - cho React Router)
   ├── manifest.json
   ├── asset-manifest.json
   └── api/ (GIỮ NGUYÊN - không động vào)
       └── ... (tất cả files PHP)
   ```

### Bước 3: Test

1. **Clear Browser Cache:**
   - Ctrl + Shift + Delete
   - Chọn "All time"
   - Clear cache & cookies

2. **Hoặc dùng Incognito Mode** (khuyến nghị)

3. **Truy cập lại:**
   - URL: `https://qlvb.phongkhcn.vn`
   - Đăng nhập lại
   - Kiểm tra Dashboard

## ✅ KẾT QUẢ MONG ĐỢI

Sau khi upload:
- ✅ Login page hiển thị bình thường
- ✅ Đăng nhập thành công
- ✅ Dashboard hiển thị **stats cards** (Total, Active, Expiring, Expired)
- ✅ Sidebar menu hoạt động
- ✅ Tất cả trang load được: Documents, Categories, Menu
- ✅ CRUD operations hoạt động
- ✅ Upload file, search, filter hoạt động

## 🐛 NẾU VẪN BỊ TRANG TRẮNG

### Check 1: Browser Console
1. Mở Developer Tools (F12)
2. Tab **Console** - xem có lỗi màu đỏ không
3. Tab **Network** - xem API calls có màu đỏ không

**Lỗi thường gặp:**
- `Failed to fetch` → Backend API không connect được
- `401 Unauthorized` → Token hết hạn, đăng nhập lại
- `CORS error` → Cần check .htaccess trong /api

### Check 2: Verify Files Uploaded
1. Trong File Manager, check `/public_html/index.html`
2. **View** file → xem có text `REACT_APP_BACKEND_URL` không
   - **KHÔNG nên** thấy text này (đã được replace khi build)
3. Check folder `/public_html/static/js/`
   - Phải có file `main.xxxxxxxx.js` (với hash mới)

### Check 3: .htaccess
1. Verify file `/public_html/.htaccess` tồn tại
2. Nội dung phải có:
   ```apache
   RewriteEngine On
   RewriteRule ^api/(.*)$ api/$1 [L,QSA]
   RewriteRule ^(.*)$ index.html [L,QSA]
   ```

## 📊 CHECKLIST HOÀN THÀNH

- [ ] Download `frontend_upload_package.zip`
- [ ] Extract và upload tất cả files vào `/public_html/`
- [ ] Ghi đè các file cũ
- [ ] Giữ nguyên folder `api/`
- [ ] Clear browser cache
- [ ] Test login
- [ ] Dashboard hiển thị đúng (không còn trang trắng)
- [ ] Test CRUD operations
- [ ] Test upload file

## 📞 NẾU CẦN HỖ TRỢ

Cung cấp:
1. Screenshot browser console (F12 → Console tab)
2. Screenshot Network tab (F12 → Network tab) - filter "api"
3. Screenshot cấu trúc file trong `/public_html/`
4. Có thông báo lỗi gì trong console không?

---

## 🎯 TÓM TẮT NHANH

```bash
1. Download: frontend_upload_package.zip
2. Upload lên: /public_html/ (ghi đè files cũ, giữ folder api/)
3. Clear cache browser
4. Test: https://qlvb.phongkhcn.vn
```

**Thời gian: ~5 phút**

---

**Package location:** `/app/cpanel_deployment/frontend_upload_package.zip` (871KB)

**Đã build với backend URL:** `https://qlvb.phongkhcn.vn` ✅

**Chúc bạn thành công! 🎉**
