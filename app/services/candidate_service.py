from app.database import get_db

def get_all_disciplines(active_only: bool = True):
    with get_db() as conn:
        cursor = conn.cursor()
        query = """
            SELECT d.*, COUNT(c.id) as candidate_count
            FROM disciplines d
            LEFT JOIN candidates c ON d.id = c.discipline_id AND c.is_active = 1
            WHERE (? = 0 OR d.is_active = 1)
            GROUP BY d.id
            ORDER BY d.display_order ASC, d.id ASC
        """
        cursor.execute(query, (1 if active_only else 0,))
        rows = cursor.fetchall()
        return [dict(row) for row in rows]

def get_discipline_by_id(discipline_id: int):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM disciplines WHERE id = ?", (discipline_id,))
        row = cursor.fetchone()
        return dict(row) if row else None

def create_discipline(name: str, description: str = None, display_order: int = 0):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO disciplines (discipline_name, description, display_order)
            VALUES (?, ?, ?)
            """,
            (name.strip(), description.strip() if description else "", display_order)
        )
        return cursor.lastrowid

def update_discipline(discipline_id: int, name: str, description: str = None, display_order: int = 0, is_active: bool = True):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            UPDATE disciplines
            SET discipline_name = ?, description = ?, display_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
            """,
            (name.strip(), description.strip() if description else "", display_order, 1 if is_active else 0, discipline_id)
        )
        return cursor.rowcount > 0

def get_candidates(discipline_id: int = None, search: str = None, active_only: bool = True):
    with get_db() as conn:
        cursor = conn.cursor()
        query = """
            SELECT c.*, d.discipline_name
            FROM candidates c
            JOIN disciplines d ON c.discipline_id = d.id
            WHERE (? = 0 OR c.is_active = 1)
        """
        params = [1 if active_only else 0]
        
        if discipline_id:
            query += " AND c.discipline_id = ?"
            params.append(discipline_id)
            
        if search:
            query += " AND (c.candidate_name LIKE ? OR c.organisation LIKE ? OR c.area_of_expertise LIKE ?)"
            s = f"%{search}%"
            params.extend([s, s, s])
            
        query += " ORDER BY d.display_order ASC, c.display_order ASC, c.candidate_name ASC"
        cursor.execute(query, params)
        rows = cursor.fetchall()
        return [dict(row) for row in rows]

def get_candidate_by_id(candidate_id: int):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT c.*, d.discipline_name
            FROM candidates c
            JOIN disciplines d ON c.discipline_id = d.id
            WHERE c.id = ?
            """,
            (candidate_id,)
        )
        row = cursor.fetchone()
        return dict(row) if row else None

def create_candidate(candidate_data: dict):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO candidates (
                discipline_id, candidate_name, candidate_title, organisation, position,
                photo_url, basis_of_recommendation, area_of_expertise, qualifications,
                professional_memberships, nomination_form_url, short_description, display_order, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                candidate_data["discipline_id"],
                candidate_data["candidate_name"].strip(),
                candidate_data.get("candidate_title", "").strip(),
                candidate_data.get("organisation", "").strip(),
                candidate_data.get("position", "").strip(),
                candidate_data.get("photo_url", "").strip(),
                candidate_data.get("basis_of_recommendation", "").strip(),
                candidate_data.get("area_of_expertise", "").strip(),
                candidate_data.get("qualifications", "").strip(),
                candidate_data.get("professional_memberships", "").strip(),
                candidate_data["nomination_form_url"].strip(),
                candidate_data.get("short_description", "").strip(),
                candidate_data.get("display_order", 0),
                1 if candidate_data.get("is_active", True) else 0
            )
        )
        return cursor.lastrowid

def update_candidate(candidate_id: int, candidate_data: dict):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            UPDATE candidates SET
                discipline_id = ?, candidate_name = ?, candidate_title = ?, organisation = ?, position = ?,
                photo_url = ?, basis_of_recommendation = ?, area_of_expertise = ?, qualifications = ?,
                professional_memberships = ?, nomination_form_url = ?, short_description = ?,
                display_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
            """,
            (
                candidate_data["discipline_id"],
                candidate_data["candidate_name"].strip(),
                candidate_data.get("candidate_title", "").strip(),
                candidate_data.get("organisation", "").strip(),
                candidate_data.get("position", "").strip(),
                candidate_data.get("photo_url", "").strip(),
                candidate_data.get("basis_of_recommendation", "").strip(),
                candidate_data.get("area_of_expertise", "").strip(),
                candidate_data.get("qualifications", "").strip(),
                candidate_data.get("professional_memberships", "").strip(),
                candidate_data["nomination_form_url"].strip(),
                candidate_data.get("short_description", "").strip(),
                candidate_data.get("display_order", 0),
                1 if candidate_data.get("is_active", True) else 0,
                candidate_id
            )
        )
        return cursor.rowcount > 0
