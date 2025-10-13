<?php
require_once 'config.php';

// ===================================
// FILE UPLOAD HANDLER
// ===================================

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$pathParts = explode('/', trim($path, '/'));

try {
    $user = getCurrentUser();
    $db = getDb();
    
    if ($method === 'POST' && isset($pathParts[0])) {
        // UPLOAD FILE TO DOCUMENT
        $documentId = $pathParts[0];
        
        // Verify document exists
        $stmt = $db->prepare("SELECT id FROM documents WHERE id = ?");
        $stmt->execute([$documentId]);
        if (!$stmt->fetch()) {
            sendError('Document not found', 404);
        }
        
        // Check if file uploaded
        if (!isset($_FILES['file'])) {
            sendError('No file uploaded', 400);
        }
        
        $file = $_FILES['file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            sendError('File upload error: ' . $file['error'], 400);
        }
        
        // Get file info
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $mimeType = $file['type'];
        $tmpPath = $file['tmp_name'];
        
        // Generate file ID
        $fileId = generateUUID();
        
        // Save to local first (temporary)
        $localPath = UPLOAD_PATH . '/' . $fileId . '_' . $fileName;
        
        if (!move_uploaded_file($tmpPath, $localPath)) {
            sendError('Failed to save file', 500);
        }
        
        $googleDriveId = null;
        $googleDriveUrl = null;
        
        // Upload to Google Drive if enabled
        if (GOOGLE_DRIVE_ENABLED) {
            try {
                require_once 'google_drive.php';
                $driveResult = uploadToGoogleDrive($localPath, $fileName, $mimeType);
                
                $googleDriveId = $driveResult['id'];
                $googleDriveUrl = $driveResult['web_view_link'];
                
                // Delete local file after successful upload to Drive
                unlink($localPath);
                
            } catch (Exception $e) {
                logError('Google Drive upload failed: ' . $e->getMessage());
                // Keep local file if Drive upload fails
            }
        }
        
        // Save to database
        $stmt = $db->prepare("
            INSERT INTO files (id, document_id, filename, google_drive_id, google_drive_url, mime_type, size) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $fileId,
            $documentId,
            $fileName,
            $googleDriveId,
            $googleDriveUrl,
            $mimeType,
            $fileSize
        ]);
        
        $fileRecord = [
            'id' => $fileId,
            'document_id' => $documentId,
            'filename' => $fileName,
            'google_drive_id' => $googleDriveId,
            'google_drive_url' => $googleDriveUrl,
            'mime_type' => $mimeType,
            'size' => $fileSize,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        sendJSON([
            'message' => 'File uploaded successfully',
            'file' => $fileRecord
        ], 201);
        
    } elseif ($method === 'GET' && isset($pathParts[0])) {
        // GET FILES FOR DOCUMENT
        $documentId = $pathParts[0];
        
        $stmt = $db->prepare("SELECT * FROM files WHERE document_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$documentId]);
        $files = $stmt->fetchAll();
        
        sendJSON($files);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Upload Error: ' . $e->getMessage());
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

?>