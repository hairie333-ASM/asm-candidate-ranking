import os
import uuid
import mimetypes
from abc import ABC, abstractmethod
from app.database import get_db

ALLOWED_EXTENSIONS = {
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'
}

DISALLOWED_EXTENSIONS = {
    'exe', 'bat', 'cmd', 'sh', 'py', 'js', 'vbs', 'msi', 'bin', 'com', 'scr', 'dll', 'pif'
}

MAX_FILE_SIZE = 15 * 1024 * 1024  # 15 MB

BASE_DIR = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
UPLOAD_DIR = os.path.join(BASE_DIR, "uploads")
os.makedirs(UPLOAD_DIR, exist_ok=True)

class StorageProvider(ABC):
    @abstractmethod
    def save_file(self, file_content: bytes, original_filename: str) -> dict:
        pass

    @abstractmethod
    def get_file_path(self, storage_path: str) -> str:
        pass

    @abstractmethod
    def delete_file(self, storage_path: str) -> bool:
        pass

class LocalStorageProvider(StorageProvider):
    def __init__(self, upload_dir=UPLOAD_DIR):
        self.upload_dir = upload_dir
        os.makedirs(self.upload_dir, exist_ok=True)

    def save_file(self, file_content: bytes, original_filename: str) -> dict:
        if len(file_content) > MAX_FILE_SIZE:
            raise ValueError(f"File exceeds maximum allowed size of {MAX_FILE_SIZE // (1024*1024)}MB.")

        ext = original_filename.rsplit('.', 1)[-1].lower() if '.' in original_filename else ''
        if ext in DISALLOWED_EXTENSIONS or ext not in ALLOWED_EXTENSIONS:
            raise ValueError(f"File type '.{ext}' is not permitted.")

        safe_stored_name = f"{uuid.uuid4().hex}.{ext}"
        destination = os.path.join(self.upload_dir, safe_stored_name)

        # Path traversal guard
        if not os.path.abspath(destination).startswith(os.path.abspath(self.upload_dir)):
            raise ValueError("Invalid storage path detected.")

        with open(destination, 'wb') as f:
            f.write(file_content)

        mime_type, _ = mimetypes.guess_type(original_filename)
        if not mime_type:
            mime_type = "application/octet-stream"

        return {
            "file_name": os.path.basename(original_filename),
            "storage_path": safe_stored_name,
            "file_type": mime_type,
            "file_size": len(file_content)
        }

    def get_file_path(self, storage_path: str) -> str:
        # Strip any directory navigation
        clean_name = os.path.basename(storage_path)
        full_path = os.path.join(self.upload_dir, clean_name)
        if not os.path.exists(full_path):
            return None
        return full_path

    def delete_file(self, storage_path: str) -> bool:
        clean_name = os.path.basename(storage_path)
        full_path = os.path.join(self.upload_dir, clean_name)
        if os.path.exists(full_path):
            os.remove(full_path)
            return True
        return False

class AzureBlobStorageProvider(StorageProvider):
    """
    Extensible storage provider for Microsoft Azure Blob Storage / OneDrive integration.
    """
    def save_file(self, file_content: bytes, original_filename: str) -> dict:
        raise NotImplementedError("Azure Blob storage provider not configured.")

    def get_file_path(self, storage_path: str) -> str:
        raise NotImplementedError("Azure Blob storage provider not configured.")

    def delete_file(self, storage_path: str) -> bool:
        raise NotImplementedError("Azure Blob storage provider not configured.")

# Default active provider
_storage_provider = LocalStorageProvider()

def get_storage_provider() -> StorageProvider:
    return _storage_provider
