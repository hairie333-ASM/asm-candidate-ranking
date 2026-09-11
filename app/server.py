import os
import sys
import json
import urllib.parse
import mimetypes
from http.server import HTTPServer, ThreadingHTTPServer, BaseHTTPRequestHandler
from http import cookies

from app.database import init_db, get_db
from app.auth import (
    create_session, validate_session, destroy_session,
    get_auth_provider, hash_password
)
from app.services.ranking_service import (
    get_current_exercise, get_user_submission, save_draft_rankings,
    validate_all_disciplines, submit_final_ranking, admin_reopen_submission,
    get_ranking_results
)
from app.services.candidate_service import (
    get_all_disciplines, get_discipline_by_id, create_discipline, update_discipline,
    get_candidates, get_candidate_by_id, create_candidate, update_candidate
)
from app.services.due_diligence_service import (
    get_due_diligence_categories, get_candidate_due_diligence,
    create_due_diligence_submission, attach_supporting_document,
    get_supporting_document
)
from app.services.audit_service import log_audit_event, get_audit_logs
from app.services.export_service import export_ranking_csv, export_due_diligence_csv

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
STATIC_DIR = os.path.join(BASE_DIR, "static")

class ASMRequestHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        # Keep server log clean and informative
        sys.stderr.write(f"[{self.log_date_time_string()}] {args[0]} {args[1]} -> {args[2]}\n")

    def send_json(self, status_code: int, data: dict, headers: dict = None):
        body = json.dumps(data, default=str).encode('utf-8')
        self.send_response(status_code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("X-Content-Type-Options", "nosniff")
        self.send_header("X-Frame-Options", "SAMEORIGIN")
        self.send_header("Cache-Control", "no-store, no-cache, must-revalidate")
        if headers:
            for k, v in headers.items():
                self.send_header(k, v)
        self.end_headers()
        self.wfile.write(body)

    def send_error_json(self, status_code: int, message: str, errors: dict = None):
        payload = {"success": False, "error": message}
        if errors:
            payload["errors"] = errors
        self.send_json(status_code, payload)

    def parse_query_params(self):
        parsed = urllib.parse.urlparse(self.path)
        return parsed.path, urllib.parse.parse_qs(parsed.query)

    def read_json_body(self):
        try:
            content_length = int(self.headers.get("Content-Length", 0))
            if content_length == 0:
                return {}
            raw_body = self.rfile.read(content_length).decode('utf-8')
            return json.loads(raw_body)
        except Exception:
            return None

    def get_client_ip(self):
        forwarded = self.headers.get("X-Forwarded-For")
        if forwarded:
            return forwarded.split(",")[0].strip()
        return self.client_address[0] if self.client_address else "127.0.0.1"

    def get_authenticated_user(self):
        # 1. Check Cookie
        cookie_header = self.headers.get("Cookie")
        session_token = None
        if cookie_header:
            c = cookies.SimpleCookie()
            try:
                c.load(cookie_header)
                if "asm_session" in c:
                    session_token = c["asm_session"].value
            except Exception:
                pass
                
        # 2. Check Authorization Header (Bearer)
        if not session_token:
            auth_header = self.headers.get("Authorization")
            if auth_header and auth_header.startswith("Bearer "):
                session_token = auth_header[7:].strip()
                
        if not session_token:
            return None, None
            
        user = validate_session(session_token)
        return user, session_token

    def require_auth(self):
        user, token = self.get_authenticated_user()
        if not user:
            self.send_error_json(401, "Authentication required. Please log in.")
            return None, None
        return user, token

    def require_roles(self, allowed_roles: list):
        user, token = self.require_auth()
        if not user:
            return None, None
        if user["role"] not in allowed_roles:
            self.send_error_json(403, "Access denied. Insufficient permissions.")
            return None, None
        return user, token

    # --- ROUTING: GET ---
    def do_GET(self):
        path, query = self.parse_query_params()

        # Static assets and SPA routes
        if not path.startswith("/api/"):
            self.serve_static_file(path)
            return

        # API Routes
        # 1. Auth Me
        if path == "/api/auth/me":
            user, _ = self.get_authenticated_user()
            if not user:
                self.send_json(200, {"authenticated": False})
            else:
                self.send_json(200, {"authenticated": True, "user": user})
            return

        # 2. Current Exercise
        if path == "/api/exercises/current":
            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return
            self.send_json(200, {"success": True, "exercise": ex})
            return

        # 3. Disciplines
        if path == "/api/disciplines":
            active_only = query.get("all", ["0"])[0] != "1"
            disciplines = get_all_disciplines(active_only=active_only)
            self.send_json(200, {"success": True, "disciplines": disciplines})
            return

        if path.startswith("/api/disciplines/"):
            try:
                disc_id = int(path.split("/")[-1])
                disc = get_discipline_by_id(disc_id)
                if not disc:
                    self.send_error_json(404, "Discipline not found.")
                else:
                    self.send_json(200, {"success": True, "discipline": disc})
            except ValueError:
                self.send_error_json(400, "Invalid discipline ID.")
            return

        # 4. Candidates list
        if path == "/api/candidates":
            disc_id = query.get("discipline_id", [None])[0]
            search = query.get("search", [None])[0]
            disc_id_int = int(disc_id) if disc_id else None
            candidates = get_candidates(discipline_id=disc_id_int, search=search)
            self.send_json(200, {"success": True, "candidates": candidates})
            return

        # 5. Candidate Detail (Full Dossier for Candidate Information Pop-up)
        if path.startswith("/api/candidates/") and not path.endswith("/due-diligence"):
            try:
                cand_id = int(path.split("/")[-1])
                user, _ = self.require_auth()
                if not user:
                    return
                cand = get_candidate_by_id(cand_id)
                if not cand:
                    self.send_error_json(404, "Candidate not found.")
                else:
                    self.send_json(200, {"success": True, "candidate": cand})
            except ValueError:
                self.send_error_json(400, "Invalid candidate ID.")
            return

        # 6. Candidate Due Diligence
        if path.startswith("/api/candidates/") and path.endswith("/due-diligence"):
            try:
                parts = path.split("/")
                cand_id = int(parts[3])
                user, _ = self.require_auth()
                if not user:
                    return
                is_admin_or_reviewer = user["role"] in ["admin", "reviewer"]
                submissions = get_candidate_due_diligence(cand_id, user["id"], is_admin_or_reviewer)
                self.send_json(200, {"success": True, "submissions": submissions})
            except ValueError:
                self.send_error_json(400, "Invalid candidate ID.")
            return

        # 7. Due Diligence Categories
        if path == "/api/due-diligence/categories":
            user, _ = self.require_auth()
            if not user:
                return
            cats = get_due_diligence_categories()
            self.send_json(200, {"success": True, "categories": cats})
            return

        # 8. User Ranking Submission (My Submission)
        if path == "/api/ranking/my-submission":
            user, _ = self.require_auth()
            if not user:
                return
            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return
            submission = get_user_submission(user["id"], ex["id"])
            self.send_json(200, {"success": True, "submission": submission, "exercise": ex})
            return

        # 9. Protected File Download
        if path.startswith("/api/files/"):
            try:
                doc_id = int(path.split("/")[-1])
                user, _ = self.require_auth()
                if not user:
                    return
                is_admin_or_reviewer = user["role"] in ["admin", "reviewer"]
                file_data, err = get_supporting_document(doc_id, user["id"], is_admin_or_reviewer)
                if err or not file_data:
                    self.send_error_json(404, err or "File not found.")
                    return
                    
                file_path = file_data["file_path"]
                with open(file_path, 'rb') as f:
                    content = f.read()
                    
                self.send_response(200)
                self.send_header("Content-Type", file_data["file_type"])
                self.send_header("Content-Length", str(len(content)))
                self.send_header("Content-Disposition", f'inline; filename="{file_data["file_name"]}"')
                self.send_header("X-Content-Type-Options", "nosniff")
                self.end_headers()
                self.wfile.write(content)
                return
            except ValueError:
                self.send_error_json(400, "Invalid document ID.")
                return

        # 10. Information Pages
        if path.startswith("/api/information/"):
            slug = path.split("/")[-1]
            with get_db() as conn:
                cursor = conn.cursor()
                cursor.execute("SELECT * FROM system_information_pages WHERE slug = ?", (slug,))
                row = cursor.fetchone()
                if not row:
                    self.send_error_json(404, "Information page not found.")
                else:
                    self.send_json(200, {"success": True, "page": dict(row)})
            return

        # 11. Admin: Dashboard Stats
        if path == "/api/admin/dashboard-stats":
            user, _ = self.require_roles(["admin", "reviewer"])
            if not user:
                return
            with get_db() as conn:
                cursor = conn.cursor()
                ex = get_current_exercise()
                ex_id = ex["id"] if ex else None
                
                # Total registered voters
                cursor.execute("SELECT COUNT(*) as count FROM users WHERE role = 'voting_user' AND is_active = 1")
                total_voters = cursor.fetchone()["count"]
                
                # Submitted rankings
                submitted_count = 0
                if ex_id:
                    cursor.execute(
                        "SELECT COUNT(*) as count FROM ranking_submissions WHERE exercise_id = ? AND status = 'submitted'",
                        (ex_id,)
                    )
                    submitted_count = cursor.fetchone()["count"]
                    
                completion_rate = round((submitted_count / total_voters * 100), 1) if total_voters > 0 else 0
                
                # Due diligence count
                cursor.execute("SELECT COUNT(*) as count FROM due_diligence_submissions")
                due_diligence_count = cursor.fetchone()["count"]
                
                # Supporting documents count
                cursor.execute("SELECT COUNT(*) as count FROM supporting_documents")
                docs_count = cursor.fetchone()["count"]
                
                stats = {
                    "exercise_status": ex["status"] if ex else "closed",
                    "registered_voters": total_voters,
                    "submitted_rankings": submitted_count,
                    "completion_rate": completion_rate,
                    "due_diligence_submissions": due_diligence_count,
                    "supporting_documents": docs_count
                }
                self.send_json(200, {"success": True, "stats": stats})
            return

        # 12. Admin: Ranking Results Matrix
        if path == "/api/admin/ranking-results":
            user, _ = self.require_roles(["admin", "reviewer"])
            if not user:
                return
            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return
            results = get_ranking_results(ex["id"])
            self.send_json(200, {"success": True, "results": results})
            return

        # 13. Admin: Export Reports
        if path == "/api/admin/reports/ranking":
            user, _ = self.require_roles(["admin", "reviewer"])
            if not user:
                return
            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return
            csv_data = export_ranking_csv(ex["id"])
            self.send_response(200)
            self.send_header("Content-Type", "text/csv; charset=utf-8")
            self.send_header("Content-Disposition", f'attachment; filename="ASM_Ranking_Report_Exercise_{ex["id"]}.csv"')
            self.send_header("Cache-Control", "no-store")
            self.end_headers()
            self.wfile.write(csv_data.encode('utf-8'))
            return

        if path == "/api/admin/reports/due-diligence":
            user, _ = self.require_roles(["admin", "reviewer"])
            if not user:
                return
            csv_data = export_due_diligence_csv()
            self.send_response(200)
            self.send_header("Content-Type", "text/csv; charset=utf-8")
            self.send_header("Content-Disposition", 'attachment; filename="ASM_Due_Diligence_Report.csv"')
            self.send_header("Cache-Control", "no-store")
            self.end_headers()
            self.wfile.write(csv_data.encode('utf-8'))
            return

        # 14. Admin: Audit Logs
        if path == "/api/admin/audit-logs":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            action_filter = query.get("action", [None])[0]
            search = query.get("search", [None])[0]
            limit = int(query.get("limit", [100])[0])
            logs = get_audit_logs(limit=limit, action_filter=action_filter, search=search)
            self.send_json(200, {"success": True, "logs": logs})
            return

        # 15. Admin: Users List
        if path == "/api/admin/users":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            with get_db() as conn:
                cursor = conn.cursor()
                cursor.execute(
                    """
                    SELECT u.id, u.email, u.username, u.full_name, u.role, u.is_active,
                           u.created_at, u.last_login,
                           s.status as ranking_status, s.submitted_at as ranking_submitted_at, s.id as submission_id
                    FROM users u
                    LEFT JOIN ranking_submissions s ON u.id = s.user_id
                    ORDER BY u.role ASC, u.full_name ASC
                    """
                )
                rows = cursor.fetchall()
                self.send_json(200, {"success": True, "users": [dict(r) for r in rows]})
            return

        self.send_error_json(404, "Endpoint not found.")

    # --- ROUTING: POST ---
    def do_POST(self):
        path, query = self.parse_query_params()

        # 1. Login
        if path == "/api/auth/login":
            body = self.read_json_body()
            if not body:
                self.send_error_json(400, "Invalid JSON payload.")
                return

            auth_provider = get_auth_provider()
            user = auth_provider.authenticate(body)
            if not user:
                log_audit_event(
                    user_id=None,
                    action="LOGIN_FAILED",
                    description=f"Failed login attempt for identifier: {body.get('username') or body.get('email')}",
                    ip_address=self.get_client_ip()
                )
                self.send_error_json(401, "Invalid username or password. Please try again.")
                return

            session_token = create_session(
                user["id"],
                ip_address=self.get_client_ip(),
                user_agent=self.headers.get("User-Agent")
            )

            log_audit_event(
                user_id=user["id"],
                action="LOGIN_SUCCESS",
                description=f"User {user['full_name']} logged in successfully.",
                ip_address=self.get_client_ip()
            )

            # Set HttpOnly Cookie
            cookie = cookies.SimpleCookie()
            cookie["asm_session"] = session_token
            cookie["asm_session"]["path"] = "/"
            cookie["asm_session"]["httponly"] = True
            cookie["asm_session"]["samesite"] = "Lax"
            cookie["asm_session"]["max-age"] = 86400  # 24 hours

            safe_user = {
                "id": user["id"],
                "email": user["email"],
                "username": user["username"],
                "full_name": user["full_name"],
                "role": user["role"]
            }

            self.send_json(200, {
                "success": True,
                "token": session_token,
                "user": safe_user,
                "message": "Login successful."
            }, headers={"Set-Cookie": cookie["asm_session"].OutputString()})
            return

        # 2. Logout
        if path == "/api/auth/logout":
            user, token = self.get_authenticated_user()
            if token:
                destroy_session(token)
                if user:
                    log_audit_event(
                        user_id=user["id"],
                        action="LOGOUT",
                        description=f"User {user['full_name']} logged out.",
                        ip_address=self.get_client_ip()
                    )
            cookie = cookies.SimpleCookie()
            cookie["asm_session"] = ""
            cookie["asm_session"]["path"] = "/"
            cookie["asm_session"]["max-age"] = 0
            self.send_json(200, {"success": True, "message": "Logged out successfully."},
                           headers={"Set-Cookie": cookie["asm_session"].OutputString()})
            return

        # 3. Save Draft Rankings
        if path == "/api/ranking/save-draft":
            user, _ = self.require_roles(["voting_user", "admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or "rankings" not in body:
                self.send_error_json(400, "Missing rankings payload.")
                return

            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return

            success, msg = save_draft_rankings(user["id"], ex["id"], body["rankings"])
            if not success:
                self.send_error_json(400, msg)
            else:
                self.send_json(200, {"success": True, "message": msg})
            return

        # 4. Validate Rankings (Preview without submission)
        if path == "/api/ranking/validate":
            user, _ = self.require_roles(["voting_user", "admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or "rankings" not in body:
                self.send_error_json(400, "Missing rankings payload.")
                return

            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return

            is_valid, errors, progress, total_ranked, total_candidates = validate_all_disciplines(
                ex["id"], body["rankings"]
            )
            self.send_json(200, {
                "success": True,
                "is_valid": is_valid,
                "errors": errors,
                "discipline_progress": progress,
                "total_ranked": total_ranked,
                "total_candidates": total_candidates
            })
            return

        # 5. Final Ranking Submission (Strict Backend Validation)
        if path == "/api/ranking/submit":
            user, _ = self.require_roles(["voting_user", "admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or "rankings" not in body:
                self.send_error_json(400, "Missing rankings payload.")
                return

            ex = get_current_exercise()
            if not ex:
                self.send_error_json(404, "No active exercise found.")
                return

            success, msg = submit_final_ranking(
                user_id=user["id"],
                exercise_id=ex["id"],
                rankings_by_discipline=body["rankings"],
                ip_address=self.get_client_ip()
            )
            if not success:
                self.send_error_json(400, msg)
            else:
                self.send_json(200, {"success": True, "message": msg})
            return

        # 6. Candidate Due Diligence Submission
        if path.startswith("/api/candidates/") and path.endswith("/due-diligence"):
            try:
                parts = path.split("/")
                cand_id = int(parts[3])
                user, _ = self.require_roles(["voting_user", "admin", "reviewer"])
                if not user:
                    return
                body = self.read_json_body()
                if not body or "comment" not in body:
                    self.send_error_json(400, "Comment is required.")
                    return

                category = body.get("category", "General Comment")
                comment = body.get("comment", "")
                success, result = create_due_diligence_submission(
                    candidate_id=cand_id,
                    user_id=user["id"],
                    category=category,
                    comment=comment,
                    ip_address=self.get_client_ip()
                )
                if not success:
                    self.send_error_json(400, result)
                else:
                    self.send_json(201, {
                        "success": True,
                        "submission_id": result,
                        "message": "Your due diligence information has been successfully submitted."
                    })
            except ValueError:
                self.send_error_json(400, "Invalid candidate ID.")
            return

        # 7. Upload Supporting Document (Multipart Form Data)
        if path.startswith("/api/due-diligence/") and path.endswith("/upload"):
            try:
                submission_id = int(path.split("/")[3])
                user, _ = self.require_roles(["voting_user", "admin", "reviewer"])
                if not user:
                    return

                # Verify submission exists and user is authorized
                with get_db() as conn:
                    cursor = conn.cursor()
                    cursor.execute("SELECT * FROM due_diligence_submissions WHERE id = ?", (submission_id,))
                    sub = cursor.fetchone()
                    if not sub:
                        self.send_error_json(404, "Due diligence submission not found.")
                        return
                    if user["role"] not in ["admin"] and sub["user_id"] != user["id"]:
                        self.send_error_json(403, "Not authorized to attach files to this submission.")
                        return
                    cand_id = sub["candidate_id"]

                # Parse multipart body
                content_type = self.headers.get("Content-Type", "")
                if "multipart/form-data" not in content_type:
                    self.send_error_json(400, "Content-Type must be multipart/form-data.")
                    return

                boundary = content_type.split("boundary=")[-1].encode('utf-8')
                content_length = int(self.headers.get("Content-Length", 0))
                raw_body = self.rfile.read(content_length)

                file_content, filename = self.parse_multipart_file(raw_body, boundary)
                if not file_content or not filename:
                    self.send_error_json(400, "The file could not be uploaded. Please check the file type and file size.")
                    return

                doc_id, file_info = attach_supporting_document(
                    submission_id=submission_id,
                    candidate_id=cand_id,
                    user_id=user["id"],
                    file_content=file_content,
                    filename=filename,
                    ip_address=self.get_client_ip()
                )

                self.send_json(201, {
                    "success": True,
                    "document_id": doc_id,
                    "file_info": file_info,
                    "message": "Supporting document uploaded successfully."
                })
            except Exception as e:
                self.send_error_json(400, f"The file could not be uploaded. Please check the file type and file size. ({str(e)})")
            return

        # 8. Admin: Reopen Ranking Submission
        if path == "/api/admin/reopen-ranking":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or "submission_id" not in body:
                self.send_error_json(400, "Missing submission_id.")
                return

            sub_id = int(body["submission_id"])
            reason = body.get("reason", "Administrative review requested.")
            success, msg = admin_reopen_submission(
                admin_user_id=user["id"],
                submission_id=sub_id,
                reason=reason,
                ip_address=self.get_client_ip()
            )
            if not success:
                self.send_error_json(400, msg)
            else:
                self.send_json(200, {"success": True, "message": msg})
            return

        # 9. Admin: Create Candidate
        if path == "/api/admin/candidates":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body:
                self.send_error_json(400, "Invalid payload.")
                return
            cand_id = create_candidate(body)
            log_audit_event(
                user_id=user["id"],
                action="CANDIDATE_CREATED",
                entity_name="candidates",
                entity_id=cand_id,
                description=f"Created candidate '{body.get('candidate_name')}'",
                ip_address=self.get_client_ip()
            )
            self.send_json(201, {"success": True, "candidate_id": cand_id})
            return

        # 10. Admin: Create Discipline
        if path == "/api/admin/disciplines":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or not body.get("discipline_name"):
                self.send_error_json(400, "Discipline name required.")
                return
            disc_id = create_discipline(
                name=body["discipline_name"],
                description=body.get("description"),
                display_order=body.get("display_order", 0)
            )
            log_audit_event(
                user_id=user["id"],
                action="DISCIPLINE_CREATED",
                entity_name="disciplines",
                entity_id=disc_id,
                description=f"Created discipline '{body.get('discipline_name')}'",
                ip_address=self.get_client_ip()
            )
            self.send_json(201, {"success": True, "discipline_id": disc_id})
            return

        # 11. Admin: Create User
        if path == "/api/admin/users":
            user, _ = self.require_roles(["admin"])
            if not user:
                return
            body = self.read_json_body()
            if not body or not body.get("email") or not body.get("password"):
                self.send_error_json(400, "Email and password are required.")
                return
            with get_db() as conn:
                cursor = conn.cursor()
                cursor.execute(
                    """
                    INSERT INTO users (email, username, password_hash, full_name, role, is_active)
                    VALUES (?, ?, ?, ?, ?, ?)
                    """,
                    (
                        body["email"].strip(),
                        body.get("username", body["email"].split("@")[0]).strip(),
                        hash_password(body["password"]),
                        body.get("full_name", "").strip(),
                        body.get("role", "voting_user"),
                        1 if body.get("is_active", True) else 0
                    )
                )
                new_user_id = cursor.lastrowid
                log_audit_event(
                    user_id=user["id"],
                    action="USER_CREATED",
                    entity_name="users",
                    entity_id=new_user_id,
                    description=f"Admin created user {body['email']} with role {body.get('role', 'voting_user')}",
                    ip_address=self.get_client_ip()
                )
                self.send_json(201, {"success": True, "user_id": new_user_id})
            return

        self.send_error_json(404, "Endpoint not found.")

    # --- ROUTING: PUT ---
    def do_PUT(self):
        path, query = self.parse_query_params()
        
        # 1. Admin: Update Candidate
        if path.startswith("/api/admin/candidates/"):
            try:
                cand_id = int(path.split("/")[-1])
                user, _ = self.require_roles(["admin"])
                if not user:
                    return
                body = self.read_json_body()
                if not body:
                    self.send_error_json(400, "Invalid payload.")
                    return
                success = update_candidate(cand_id, body)
                log_audit_event(
                    user_id=user["id"],
                    action="CANDIDATE_UPDATED",
                    entity_name="candidates",
                    entity_id=cand_id,
                    description=f"Updated candidate #{cand_id}",
                    ip_address=self.get_client_ip()
                )
                self.send_json(200, {"success": success})
            except ValueError:
                self.send_error_json(400, "Invalid candidate ID.")
            return

        # 2. Admin: Update Discipline
        if path.startswith("/api/admin/disciplines/"):
            try:
                disc_id = int(path.split("/")[-1])
                user, _ = self.require_roles(["admin"])
                if not user:
                    return
                body = self.read_json_body()
                if not body:
                    self.send_error_json(400, "Invalid payload.")
                    return
                success = update_discipline(
                    disc_id,
                    name=body.get("discipline_name", ""),
                    description=body.get("description"),
                    display_order=body.get("display_order", 0),
                    is_active=body.get("is_active", True)
                )
                log_audit_event(
                    user_id=user["id"],
                    action="DISCIPLINE_UPDATED",
                    entity_name="disciplines",
                    entity_id=disc_id,
                    description=f"Updated discipline #{disc_id}",
                    ip_address=self.get_client_ip()
                )
                self.send_json(200, {"success": success})
            except ValueError:
                self.send_error_json(400, "Invalid discipline ID.")
            return

        # 3. Admin: Update User
        if path.startswith("/api/admin/users/"):
            try:
                target_user_id = int(path.split("/")[-1])
                user, _ = self.require_roles(["admin"])
                if not user:
                    return
                body = self.read_json_body()
                with get_db() as conn:
                    cursor = conn.cursor()
                    if body.get("password"):
                        cursor.execute(
                            "UPDATE users SET password_hash = ? WHERE id = ?",
                            (hash_password(body["password"]), target_user_id)
                        )
                    cursor.execute(
                        """
                        UPDATE users SET
                            email = ?, username = ?, full_name = ?, role = ?, is_active = ?
                        WHERE id = ?
                        """,
                        (
                            body["email"].strip(),
                            body["username"].strip(),
                            body["full_name"].strip(),
                            body["role"],
                            1 if body.get("is_active", True) else 0,
                            target_user_id
                        )
                    )
                    log_audit_event(
                        user_id=user["id"],
                        action="USER_UPDATED",
                        entity_name="users",
                        entity_id=target_user_id,
                        description=f"Admin updated user #{target_user_id}",
                        ip_address=self.get_client_ip()
                    )
                    self.send_json(200, {"success": True})
            except ValueError:
                self.send_error_json(400, "Invalid user ID.")
            return

        # 4. Admin: Update Exercise
        if path.startswith("/api/admin/exercises/"):
            try:
                ex_id = int(path.split("/")[-1])
                user, _ = self.require_roles(["admin"])
                if not user:
                    return
                body = self.read_json_body()
                with get_db() as conn:
                    cursor = conn.cursor()
                    cursor.execute(
                        """
                        UPDATE ranking_exercises SET
                            exercise_name = ?, description = ?, instructions = ?,
                            status = ?, allow_resubmission = ?, tie_breaker_method = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                        """,
                        (
                            body.get("exercise_name"),
                            body.get("description"),
                            body.get("instructions"),
                            body.get("status", "open"),
                            1 if body.get("allow_resubmission") else 0,
                            body.get("tie_breaker_method", "rank1_count"),
                            ex_id
                        )
                    )
                    log_audit_event(
                        user_id=user["id"],
                        action="EXERCISE_UPDATED",
                        entity_name="ranking_exercises",
                        entity_id=ex_id,
                        description=f"Admin updated exercise #{ex_id} status to '{body.get('status')}'",
                        ip_address=self.get_client_ip()
                    )
                    self.send_json(200, {"success": True})
            except ValueError:
                self.send_error_json(400, "Invalid exercise ID.")
            return

        self.send_error_json(404, "Endpoint not found.")

    def parse_multipart_file(self, body: bytes, boundary: bytes):
        """
        Parses binary multipart body to extract uploaded file without external packages.
        """
        delimiter = b"--" + boundary
        parts = body.split(delimiter)
        for part in parts:
            if b"Content-Disposition" in part and b'filename="' in part:
                header_part, file_data = part.split(b"\r\n\r\n", 1)
                # Remove trailing CRLF
                if file_data.endswith(b"\r\n"):
                    file_data = file_data[:-2]
                
                # Extract filename
                headers = header_part.decode('latin1', errors='replace')
                for line in headers.split("\r\n"):
                    if "Content-Disposition" in line and 'filename="' in line:
                        fn = line.split('filename="')[1].split('"')[0]
                        return file_data, os.path.basename(fn)
        return None, None

    def serve_static_file(self, req_path: str):
        if req_path == "/" or req_path == "":
            file_path = os.path.join(STATIC_DIR, "index.html")
        else:
            clean_path = req_path.lstrip("/")
            file_path = os.path.join(STATIC_DIR, clean_path)

        # Fallback to index.html for SPA client-side routing (e.g. /ranking, /candidates, /admin)
        if not os.path.exists(file_path) or os.path.isdir(file_path):
            file_path = os.path.join(STATIC_DIR, "index.html")

        # Path traversal check
        if not os.path.abspath(file_path).startswith(os.path.abspath(STATIC_DIR)):
            self.send_error(403, "Access Forbidden")
            return

        mime_type, _ = mimetypes.guess_type(file_path)
        if not mime_type:
            mime_type = "text/html"

        try:
            with open(file_path, "rb") as f:
                content = f.read()
            self.send_response(200)
            self.send_header("Content-Type", f"{mime_type}; charset=utf-8")
            self.send_header("Content-Length", str(len(content)))
            self.send_header("X-Content-Type-Options", "nosniff")
            self.send_header("X-Frame-Options", "SAMEORIGIN")
            self.end_headers()
            self.wfile.write(content)
        except Exception:
            self.send_error(500, "Internal Server Error")

def run_server(port: int = 8000):
    init_db()
    server_address = ('127.0.0.1', port)
    httpd = ThreadingHTTPServer(server_address, ASMRequestHandler)
    print(f"ASM Candidate Ranking & Due Diligence Server running on http://127.0.0.1:{port}")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nShutting down server...")
        httpd.server_close()

if __name__ == "__main__":
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 8000
    run_server(port)
