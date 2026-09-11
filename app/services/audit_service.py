from app.database import get_db

def log_audit_event(user_id: int, action: str, entity_name: str = None, entity_id: str = None, description: str = None, ip_address: str = None, conn=None):
    try:
        if conn is not None:
            cursor = conn.cursor()
            cursor.execute(
                """
                INSERT INTO audit_logs (user_id, action, entity_name, entity_id, description, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
                """,
                (user_id, action, entity_name, str(entity_id) if entity_id is not None else None, description, ip_address)
            )
        else:
            with get_db() as c:
                cursor = c.cursor()
                cursor.execute(
                    """
                    INSERT INTO audit_logs (user_id, action, entity_name, entity_id, description, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?)
                    """,
                    (user_id, action, entity_name, str(entity_id) if entity_id is not None else None, description, ip_address)
                )
    except Exception as e:
        print(f"Error logging audit event: {e}")

def get_audit_logs(limit: int = 100, offset: int = 0, action_filter: str = None, search: str = None):
    with get_db() as conn:
        cursor = conn.cursor()
        query = """
            SELECT a.*, u.full_name as user_name, u.email as user_email, u.role as user_role
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE 1=1
        """
        params = []
        if action_filter:
            query += " AND a.action = ?"
            params.append(action_filter)
        if search:
            query += " AND (a.description LIKE ? OR u.full_name LIKE ? OR a.ip_address LIKE ?)"
            s = f"%{search}%"
            params.extend([s, s, s])
            
        query += " ORDER BY a.created_at DESC LIMIT ? OFFSET ?"
        params.extend([limit, offset])
        
        cursor.execute(query, params)
        rows = cursor.fetchall()
        return [dict(row) for row in rows]
