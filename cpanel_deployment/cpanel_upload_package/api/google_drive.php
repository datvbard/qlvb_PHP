<?php

// ===================================
// GOOGLE DRIVE SERVICE
// ===================================

function getGoogleDriveService() {
    if (!GOOGLE_DRIVE_ENABLED) {
        return null;
    }
    
    if (!file_exists(GOOGLE_CREDENTIALS_FILE)) {
        logError('Google credentials file not found');
        return null;
    }
    
    try {
        require_once 'vendor/autoload.php';
        
        $client = new Google_Client();
        $client->setAuthConfig(GOOGLE_CREDENTIALS_FILE);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->setApplicationName('Document Management System');
        
        $service = new Google_Service_Drive($client);
        return $service;
        
    } catch (Exception $e) {
        logError('Google Drive service error: ' . $e->getMessage());
        return null;
    }
}

function uploadToGoogleDrive($filePath, $fileName, $mimeType) {
    try {
        $service = getGoogleDriveService();
        
        if (!$service) {
            throw new Exception('Google Drive not configured');
        }
        
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [GOOGLE_DRIVE_FOLDER_ID]
        ]);
        
        $content = file_get_contents($filePath);
        
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, webContentLink'
        ]);
        
        // Make file accessible via link
        $permission = new Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]);
        $service->permissions->create($file->id, $permission);
        
        return [
            'id' => $file->id,
            'web_view_link' => $file->webViewLink,
            'web_content_link' => $file->webContentLink
        ];
        
    } catch (Exception $e) {
        logError('Upload to Google Drive error: ' . $e->getMessage());
        throw $e;
    }
}

function deleteFromGoogleDrive($fileId) {
    try {
        $service = getGoogleDriveService();
        
        if (!$service) {
            return false;
        }
        
        $service->files->delete($fileId);
        return true;
        
    } catch (Exception $e) {
        logError('Delete from Google Drive error: ' . $e->getMessage());
        return false;
    }
}

?>