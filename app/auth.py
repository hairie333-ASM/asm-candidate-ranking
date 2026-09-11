import hashlib
import hmac
import os
import secrets
from datetime import datetime, timedelta
from abc import ABC, abstractmethod
from app.database import get_db

HASH_ITERATIONS = int(os.environ.get("ASM_HASH_ITERATIONS", "100000"))

def hash_password(password: str) -> str:
    salt = os.urandom(16)
    key = hashlib.pbkdf2_hmac(
        'sha256',
        password.encode('utf-8'),
        salt,
        HASH_ITERATIONS
    )
    return f"{salt.hex()}${HASH_ITERATIONS}${key.hex()}"

def verify_password(stored_password_hash: str, provided_password: str) -> bool:
    try:
        salt_hex, iterations_str, key_hex = stored_password_hash.split('$')
        salt = bytes.fromhex(salt_hex)
        iterations = int(iterations_str)
        key = bytes.fromhex(key_hex)
        
        new_key = hashlib.pbkdf2_hmac(
            'sha256',
            provided_password.encode('utf-8'),
            salt,
            iterations
        )
        return hmac.compare_digest(key, new_key)
    except Exception:
        return False

class AuthProvider(ABC):
    @abstractmethod
    def authenticate(self, credentials: dict) -> dict:
        pass

class LocalAuthProvider(AuthProvider):
    def authenticate(self, credentials: dict) -> dict:
        email_or_username = credentials.get("username") or credentials.get("email")
        password = credentials.get("password")
        
        if not email_or_username or not password:
            return None
            
        with get_db() as conn:
            cursor = conn.cursor()
            cursor.execute(
                "SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1",
                (email_or_username.strip(), email_or_username.strip())
            )
            user = cursor.fetchone()
            if not user:
                return None
                
            if verify_password(user["password_hash"], password):
                cursor.execute(
                    "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
                    (user["id"],)
                )
                return dict(user)
        return None

class EntraIdAuthProvider(AuthProvider):
    """
    Modular Auth Adapter for Microsoft Entra ID / Microsoft 365 / Azure AD SSO.
    Ready for OAuth 2.0 / OpenID Connect callback integration via Microsoft Graph.
    """
    def __init__(self, tenant_id=None, client_id=None, client_secret=None):
        self.tenant_id = tenant_id or os.environ.get("ENTRA_TENANT_ID")
        self.client_id = client_id or os.environ.get("ENTRA_CLIENT_ID")
        self.client_secret = client_secret or os.environ.get("ENTRA_CLIENT_SECRET")
        
    def authenticate(self, credentials: dict) -> dict:
        # In prototype/local environment, fallback or delegate when configured
        token = credentials.get("id_token") or credentials.get("code")
        if not token or not self.client_id:
            return None
        # When Microsoft Graph credentials configured, validate JWT and map claims to User
        return None

# Active Auth Provider
_auth_provider = LocalAuthProvider()

def get_auth_provider() -> AuthProvider:
    return _auth_provider

def create_session(user_id: int, ip_address: str = None, user_agent: str = None, duration_hours: int = 24) -> str:
    session_token = secrets.token_hex(32)
    expires_at = (datetime.utcnow() + timedelta(hours=duration_hours)).strftime('%Y-%m-%d %H:%M:%S')
    
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO sessions (session_token, user_id, expires_at, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
            """,
            (session_token, user_id, expires_at, ip_address, user_agent)
        )
    return session_token

def validate_session(session_token: str) -> dict:
    if not session_token:
        return None
        
    now = datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S')
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT s.session_token, s.expires_at, u.id, u.email, u.username, u.full_name, u.role, u.is_active
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.session_token = ? AND s.expires_at > ? AND u.is_active = 1
            """,
            (session_token, now)
        )
        row = cursor.fetchone()
        if row:
            return dict(row)
    return None

def destroy_session(session_token: str):
    if not session_token:
        return
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute("DELETE FROM sessions WHERE session_token = ?", (session_token,))
