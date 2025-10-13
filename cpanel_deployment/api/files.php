<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$pathParts = explode('/', trim($path, '/'));

try {
    $user = getCurrentUser();
    $db = getDb();
    
    if ($method === 'DELETE' && isset($pathParts[0])) {
        // DELETE FILE
        $fileId = $pathParts[0];
        
        // Get file info
        $stmt = $db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if (!$file) {
            sendError('File not found', 404);
        }
        
        // Delete from Google Drive if configured
        if (GOOGLE_DRIVE_ENABLED && $file['google_drive_id']) {
            try {
                require_once 'google_drive.php';
                $driveService = getGoogleDriveService();
                if ($driveService) {
                    $driveService->files->delete($file['google_drive_id']);
                }
            } catch (Exception $e) {
                logError("Google Drive delete error: " . $e->getMessage());
            }
        }
        
        // Delete local file if exists
        $localPath = UPLOAD_PATH . '/' . $file['id'] . '_' . $file['filename'];
        if (file_exists($localPath)) {
            unlink($localPath);
        }
        
        // Delete from database
        $stmt = $db->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        
        sendJSON(['message' => 'File deleted successfully']);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Files Error: ' . $e->getMessage());
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

?>
