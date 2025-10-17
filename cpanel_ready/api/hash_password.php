<?php
// =====================================================
// CÔNG CỤ TẠO PASSWORD HASH
// Dùng file này để tạo password hash mới
// =====================================================

// BƯỚC 1: Đổi password bạn muốn ở đây
$new_password = "minhan";  // ← ĐỔI PASSWORD CỦA BẠN TẠI ĐÂY

// BƯỚC 2: Tạo hash
$password_hash = password_hash($new_password, PASSWORD_BCRYPT);

// BƯỚC 3: Hiển thị kết quả
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #A52A47;
            padding-bottom: 10px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        .success {
            background: #e8f5e9;
            border-left-color: #4CAF50;
        }
        .warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        .hash-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .sql-box {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 20px 0;
            overflow-x: auto;
        }
        .step {
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #A52A47;
            border-radius: 3px;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #A52A47;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Hash Generator</h1>
        
        <div class="info success">
            <strong>✅ Password hash đã được tạo thành công!</strong>
        </div>

        <div class="step">
            <span class="step-number">1</span>
            <strong>Password gốc của bạn:</strong>
            <div class="hash-box"><?php echo htmlspecialchars($new_password); ?></div>
        </div>

        <div class="step">
            <span class="step-number">2</span>
            <strong>Password hash (bcrypt):</strong>
            <div class="hash-box"><?php echo $password_hash; ?></div>
        </div>

        <div class="step">
            <span class="step-number">3</span>
            <strong>Copy câu SQL này và chạy trong phpMyAdmin:</strong>
            <div class="sql-box">UPDATE users SET password_hash = '<?php echo $password_hash; ?>' WHERE username = 'admin';</div>
        </div>

        <div class="info warning">
            <strong>⚠️ HƯỚNG DẪN THỰC HIỆN:</strong><br><br>
            <strong>Bước 1:</strong> Copy câu SQL ở trên (bôi đen và Ctrl+C)<br>
            <strong>Bước 2:</strong> Vào phpMyAdmin<br>
            <strong>Bước 3:</strong> Chọn database <code>qlvb_db</code><br>
            <strong>Bước 4:</strong> Click tab <strong>SQL</strong><br>
            <strong>Bước 5:</strong> Paste câu SQL vào và click <strong>Go</strong><br>
            <strong>Bước 6:</strong> Thử login lại với password: <strong><?php echo htmlspecialchars($new_password); ?></strong>
        </div>

        <div class="info">
            <strong>🔒 BẢO MẬT:</strong><br>
            Sau khi đổi password thành công, <strong>XÓA FILE NÀY NGAY</strong>:<br>
            <code>/public_html/api/hash_password.php</code>
        </div>

        <div class="step">
            <span class="step-number">💡</span>
            <strong>Muốn đổi password khác?</strong><br>
            1. Edit file này trong File Manager<br>
            2. Đổi giá trị <code>$new_password = "minhan";</code> thành password mới<br>
            3. Refresh trang này<br>
            4. Copy SQL mới và chạy lại
        </div>
    </div>
</body>
</html>
<?php
// =====================================================
// XÓA FILE NÀY SAU KHI DÙNG XONG!
// =====================================================
?>
