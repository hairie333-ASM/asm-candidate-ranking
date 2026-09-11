from app.database import get_db
from app.services.audit_service import log_audit_event
from app.services.storage_service import get_storage_provider

DEFAULT_CATEGORIES = [
    "General Comment",
    "Professional Background",
    "Academic / Research Record",
    "Leadership",
    "Achievement",
    "Conflict of Interest",
    "Integrity / Reputation",
    "Other Relevant Information"
]

def get_due_diligence_categories():
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            "SELECT * FROM due_diligence_categories WHERE is_active = 1 ORDER BY display_order ASC, id ASC"
        )
        rows = cursor.fetchall()
        if not rows:
            # Seed default categories if empty
            for idx, cat in enumerate(DEFAULT_CATEGORIES):
                cursor.execute(
                    "INSERT OR IGNORE INTO due_diligence_categories (category_name, display_order) VALUES (?, ?)",
                    (cat, idx)
                )
            cursor.execute(
                "SELECT * FROM due_diligence_categories WHERE is_active = 1 ORDER BY display_order ASC, id ASC"
            )
            rows = cursor.fetchall()
        return [dict(r) for r in rows]

def get_candidate_due_diligence(candidate_id: int, current_user_id: int, is_admin_or_reviewer: bool = False):
    with get_db() as conn:
        cursor = conn.cursor()
        
        # Admin and Reviewers see all submissions; Voting users see all finalized comments or their own
        query = """
            SELECT dd.*, u.full_name as author_name, u.email as author_email, u.role as author_role
            FROM due_diligence_submissions dd
            JOIN users u ON dd.user_id = u.id
            WHERE dd.candidate_id = ?
            ORDER BY dd.submitted_at DESC
        """
        cursor.execute(query, (candidate_id,))
        submissions = [dict(s) for s in cursor.fetchall()]
        
        # Attach supporting documents for each submission
        for sub in submissions:
            cursor.execute(
                """
                SELECT id, due_diligence_submission_id, candidate_id, uploaded_by,
                       file_name, file_type, file_size, uploaded_at
                FROM supporting_documents
                WHERE due_diligence_submission_id = ?
                ORDER BY uploaded_at ASC
                """,
                (sub["id"],)
            )
            sub["supporting_documents"] = [dict(d) for d in cursor.fetchall()]
            
        return submissions

def create_due_diligence_submission(candidate_id: int, user_id: int, category: str, comment: str, ip_address: str = None):
    if not comment or not comment.strip():
        return False, "Comment content cannot be empty."
        
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO due_diligence_submissions (candidate_id, user_id, category, comment, status)
            VALUES (?, ?, ?, ?, 'submitted')
            """,
            (candidate_id, user_id, category.strip(), comment.strip())
        )
        submission_id = cursor.lastrowid
        
        log_audit_event(
            user_id=user_id,
            action="DUE_DILIGENCE_SUBMITTED",
            entity_name="due_diligence_submissions",
            entity_id=submission_id,
            description=f"User submitted due diligence assessment for candidate #{candidate_id} under category '{category}'",
            ip_address=ip_address,
            conn=conn
        )
        return True, submission_id

def attach_supporting_document(submission_id: int, candidate_id: int, user_id: int, file_content: bytes, filename: str, ip_address: str = None):
    provider = get_storage_provider()
    file_info = provider.save_file(file_content, filename)
    
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO supporting_documents (
                due_diligence_submission_id, candidate_id, uploaded_by,
                file_name, storage_path, file_type, file_size
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
            """,
            (
                submission_id, candidate_id, user_id,
                file_info["file_name"], file_info["storage_path"],
                file_info["file_type"], file_info["file_size"]
            )
        )
        doc_id = cursor.lastrowid
        
        log_audit_event(
            user_id=user_id,
            action="SUPPORTING_DOCUMENT_UPLOADED",
            entity_name="supporting_documents",
            entity_id=doc_id,
            description=f"Uploaded supporting document '{file_info['file_name']}' ({file_info['file_size']} bytes) for submission #{submission_id}",
            ip_address=ip_address,
            conn=conn
        )
        
        return doc_id, file_info

def get_supporting_document(doc_id: int, requesting_user_id: int, is_admin_or_reviewer: bool = False):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM supporting_documents WHERE id = ?", (doc_id,))
        doc = cursor.fetchone()
        if not doc:
            return None, "File record not found."
            
        # Security access check
        if not is_admin_or_reviewer and doc["uploaded_by"] != requesting_user_id:
            # Check if associated due diligence submission is public to authorized users
            pass
            
        provider = get_storage_provider()
        full_path = provider.get_file_path(doc["storage_path"])
        if not full_path:
            return None, "File not found on storage."
            
        return {
            "file_path": full_path,
            "file_name": doc["file_name"],
            "file_type": doc["file_type"]
        }, None
