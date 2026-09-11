import sqlite3
import os
from contextlib import contextmanager

DB_DIR = os.path.dirname(os.path.abspath(__file__))
BASE_DIR = os.path.dirname(DB_DIR)
DB_PATH = os.path.join(BASE_DIR, "asm_ranking.db")

def get_db_connection(db_path=None):
    if db_path is None:
        db_path = DB_PATH
    conn = sqlite3.connect(db_path, check_same_thread=False, timeout=30.0)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON;")
    try:
        conn.execute("PRAGMA journal_mode = WAL;")
    except Exception:
        pass
    return conn

@contextmanager
def get_db(db_path=None):
    conn = get_db_connection(db_path)
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()

def init_db(db_path=None):
    with get_db(db_path) as conn:
        cursor = conn.cursor()
        
        # 1. Users
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL COLLATE NOCASE,
            username TEXT UNIQUE NOT NULL COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'voting_user', 'reviewer')),
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        );
        """)

        # 2. Sessions
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS sessions (
            session_token TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_address TEXT,
            user_agent TEXT
        );
        """)

        # 3. Ranking Exercises
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS ranking_exercises (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            exercise_name TEXT NOT NULL,
            description TEXT,
            instructions TEXT,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            status TEXT NOT NULL DEFAULT 'open' CHECK(status IN ('draft', 'open', 'closed')),
            allow_resubmission INTEGER NOT NULL DEFAULT 0,
            tie_breaker_method TEXT NOT NULL DEFAULT 'rank1_count' CHECK(tie_breaker_method IN ('rank1_count', 'rank2_count', 'admin_review')),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # 4. Disciplines
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS disciplines (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            discipline_name TEXT NOT NULL,
            description TEXT,
            display_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # 5. Candidates (with full dossier fields)
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS candidates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            discipline_id INTEGER NOT NULL REFERENCES disciplines(id) ON DELETE RESTRICT,
            candidate_name TEXT NOT NULL,
            candidate_title TEXT,
            organisation TEXT,
            position TEXT,
            photo_url TEXT,
            basis_of_recommendation TEXT,
            area_of_expertise TEXT,
            qualifications TEXT,
            professional_memberships TEXT,
            nomination_form_url TEXT NOT NULL,
            short_description TEXT,
            display_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT uq_discipline_candidate UNIQUE(discipline_id, candidate_name)
        );
        """)

        # 6. Ranking Submissions
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS ranking_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
            exercise_id INTEGER NOT NULL REFERENCES ranking_exercises(id) ON DELETE RESTRICT,
            status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft', 'submitted', 'reopened')),
            submitted_at DATETIME,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reopened_by INTEGER REFERENCES users(id),
            reopened_at DATETIME,
            reopen_reason TEXT,
            CONSTRAINT uq_user_exercise_submission UNIQUE(user_id, exercise_id)
        );
        """)

        # 7. Individual Rankings
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS rankings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            submission_id INTEGER NOT NULL REFERENCES ranking_submissions(id) ON DELETE CASCADE,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
            exercise_id INTEGER NOT NULL REFERENCES ranking_exercises(id) ON DELETE RESTRICT,
            discipline_id INTEGER NOT NULL REFERENCES disciplines(id) ON DELETE RESTRICT,
            candidate_id INTEGER NOT NULL REFERENCES candidates(id) ON DELETE RESTRICT,
            ranking_number INTEGER NOT NULL CHECK(ranking_number > 0),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT uq_user_discipline_candidate UNIQUE(user_id, exercise_id, discipline_id, candidate_id),
            CONSTRAINT uq_user_discipline_rank UNIQUE(user_id, exercise_id, discipline_id, ranking_number)
        );
        """)

        # 8. Due Diligence Submissions
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS due_diligence_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            candidate_id INTEGER NOT NULL REFERENCES candidates(id) ON DELETE RESTRICT,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
            category TEXT NOT NULL,
            comment TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'submitted' CHECK(status IN ('draft', 'submitted', 'archived')),
            submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # 9. Supporting Documents
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS supporting_documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            due_diligence_submission_id INTEGER REFERENCES due_diligence_submissions(id) ON DELETE CASCADE,
            candidate_id INTEGER NOT NULL REFERENCES candidates(id) ON DELETE RESTRICT,
            uploaded_by INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
            file_name TEXT NOT NULL,
            storage_path TEXT NOT NULL,
            file_type TEXT NOT NULL,
            file_size INTEGER NOT NULL,
            uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # 10. Due Diligence Categories Configuration
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS due_diligence_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_name TEXT UNIQUE NOT NULL,
            display_order INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1
        );
        """)

        # 11. System Information Pages
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS system_information_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            display_order INTEGER NOT NULL DEFAULT 0,
            updated_by INTEGER REFERENCES users(id),
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # 12. Audit Logs
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            action TEXT NOT NULL,
            entity_name TEXT,
            entity_id TEXT,
            description TEXT,
            ip_address TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)

        # Indexes for high performance
        cursor.execute("CREATE INDEX IF NOT EXISTS idx_candidates_discipline ON candidates(discipline_id);")
        cursor.execute("CREATE INDEX IF NOT EXISTS idx_rankings_user_exercise ON rankings(user_id, exercise_id);")
        cursor.execute("CREATE INDEX IF NOT EXISTS idx_rankings_discipline ON rankings(discipline_id);")
        cursor.execute("CREATE INDEX IF NOT EXISTS idx_due_diligence_candidate ON due_diligence_submissions(candidate_id);")
        cursor.execute("CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action);")
