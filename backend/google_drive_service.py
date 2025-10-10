from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload, MediaIoBaseDownload
import os
import io
from typing import Optional, Dict

class GoogleDriveService:
    def __init__(self):
        self.credentials_path = os.environ.get('GOOGLE_APPLICATION_CREDENTIALS')
        self.folder_id = os.environ.get('GOOGLE_DRIVE_FOLDER_ID')
        self.service = None
        
        if self.credentials_path and os.path.exists(self.credentials_path):
            try:
                credentials = service_account.Credentials.from_service_account_file(
                    self.credentials_path,
                    scopes=['https://www.googleapis.com/auth/drive']
                )
                self.service = build('drive', 'v3', credentials=credentials)
                print("✅ Google Drive service initialized successfully")
            except Exception as e:
                print(f"⚠️ Google Drive service initialization failed: {e}")
        else:
            print("⚠️ Google Drive credentials not configured")
    
    def is_configured(self) -> bool:
        """Check if Google Drive is properly configured"""
        return self.service is not None and self.folder_id is not None
    
    def upload_file(self, file_path: str, filename: str, mime_type: str) -> Optional[Dict]:
        """Upload a file to Google Drive"""
        if not self.is_configured():
            raise Exception("Google Drive not configured")
        
        try:
            file_metadata = {
                'name': filename,
                'parents': [self.folder_id]
            }
            
            media = MediaFileUpload(file_path, mimetype=mime_type, resumable=True)
            
            file = self.service.files().create(
                body=file_metadata,
                media_body=media,
                fields='id, name, webViewLink, webContentLink'
            ).execute()
            
            # Make file accessible via link
            self.service.permissions().create(
                fileId=file.get('id'),
                body={'type': 'anyone', 'role': 'reader'}
            ).execute()
            
            return {
                'id': file.get('id'),
                'name': file.get('name'),
                'web_view_link': file.get('webViewLink'),
                'web_content_link': file.get('webContentLink')
            }
        except Exception as e:
            print(f"Error uploading file: {e}")
            raise
    
    def delete_file(self, file_id: str) -> bool:
        """Delete a file from Google Drive"""
        if not self.is_configured():
            return False
        
        try:
            self.service.files().delete(fileId=file_id).execute()
            return True
        except Exception as e:
            print(f"Error deleting file: {e}")
            return False
    
    def get_file_metadata(self, file_id: str) -> Optional[Dict]:
        """Get file metadata from Google Drive"""
        if not self.is_configured():
            return None
        
        try:
            file = self.service.files().get(
                fileId=file_id,
                fields='id, name, mimeType, size, webViewLink, webContentLink, createdTime'
            ).execute()
            return file
        except Exception as e:
            print(f"Error getting file metadata: {e}")
            return None

# Global instance
drive_service = GoogleDriveService()
