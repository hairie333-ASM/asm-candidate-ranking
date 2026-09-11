from app.database import get_db
from app.services.audit_service import log_audit_event
from datetime import datetime

def get_current_exercise():
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT * FROM ranking_exercises 
            WHERE status = 'open' 
            ORDER BY id DESC LIMIT 1
            """
        )
        row = cursor.fetchone()
        if row:
            return dict(row)
        # Fallback to any latest exercise if none is explicitly open
        cursor.execute("SELECT * FROM ranking_exercises ORDER BY id DESC LIMIT 1")
        row = cursor.fetchone()
        return dict(row) if row else None

def get_user_submission(user_id: int, exercise_id: int):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT s.*, u.full_name as user_name, u.email as user_email,
                   rb.full_name as reopened_by_name
            FROM ranking_submissions s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN users rb ON s.reopened_by = rb.id
            WHERE s.user_id = ? AND s.exercise_id = ?
            """,
            (user_id, exercise_id)
        )
        row = cursor.fetchone()
        if not row:
            return None
            
        submission = dict(row)
        cursor.execute(
            """
            SELECT r.*, c.candidate_name, c.candidate_title, c.organisation, c.discipline_id,
                   d.discipline_name
            FROM rankings r
            JOIN candidates c ON r.candidate_id = c.id
            JOIN disciplines d ON r.discipline_id = d.id
            WHERE r.submission_id = ?
            ORDER BY r.discipline_id ASC, r.ranking_number ASC
            """,
            (submission["id"],)
        )
        submission["rankings"] = [dict(r) for r in cursor.fetchall()]
        return submission

def save_draft_rankings(user_id: int, exercise_id: int, rankings_by_discipline: dict):
    """
    Saves in-progress rankings for the user.
    rankings_by_discipline: dict of {str(discipline_id): {str(candidate_id): int(rank)}}
    """
    with get_db() as conn:
        cursor = conn.cursor()
        
        # Verify exercise is open
        cursor.execute("SELECT status FROM ranking_exercises WHERE id = ?", (exercise_id,))
        ex = cursor.fetchone()
        if not ex or ex["status"] != "open":
            return False, "The ranking exercise is not open."
            
        # Check existing submission
        cursor.execute(
            "SELECT id, status FROM ranking_submissions WHERE user_id = ? AND exercise_id = ?",
            (user_id, exercise_id)
        )
        sub = cursor.fetchone()
        if sub and sub["status"] == "submitted":
            return False, "You have already submitted your ranking for this exercise."
            
        if not sub:
            cursor.execute(
                """
                INSERT INTO ranking_submissions (user_id, exercise_id, status)
                VALUES (?, ?, 'draft')
                """,
                (user_id, exercise_id)
            )
            submission_id = cursor.lastrowid
        else:
            submission_id = sub["id"]
            
        # Update rankings per discipline provided
        for disc_id_str, cand_ranks in rankings_by_discipline.items():
            discipline_id = int(disc_id_str)
            # Remove previous draft rankings for this discipline to avoid unique constraint collisions during re-ranking
            cursor.execute(
                """
                DELETE FROM rankings 
                WHERE submission_id = ? AND discipline_id = ?
                """,
                (submission_id, discipline_id)
            )
            for cand_id_str, rank_val in cand_ranks.items():
                if rank_val is not None and str(rank_val).strip() != "":
                    try:
                        rank_int = int(rank_val)
                        if rank_int > 0:
                            cursor.execute(
                                """
                                INSERT INTO rankings (
                                    submission_id, user_id, exercise_id, discipline_id, candidate_id, ranking_number
                                ) VALUES (?, ?, ?, ?, ?, ?)
                                """,
                                (submission_id, user_id, exercise_id, discipline_id, int(cand_id_str), rank_int)
                            )
                    except Exception:
                        pass # Ignore malformed draft values
                        
        cursor.execute(
            "UPDATE ranking_submissions SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            (submission_id,)
        )
        return True, "Draft saved successfully."

def validate_all_disciplines(exercise_id: int, rankings_by_discipline: dict):
    """
    Validates that:
    1. Every active discipline is ranked.
    2. Within each discipline, every active candidate has a rank.
    3. Ranks start at 1 and end at N (candidate count) with no duplicates or gaps.
    """
    with get_db() as conn:
        cursor = conn.cursor()
        
        cursor.execute(
            "SELECT id, discipline_name FROM disciplines WHERE is_active = 1 ORDER BY display_order ASC"
        )
        disciplines = cursor.fetchall()
        
        errors = {}
        discipline_progress = {}
        total_candidates = 0
        total_ranked = 0
        
        for disc in disciplines:
            disc_id = disc["id"]
            disc_name = disc["discipline_name"]
            
            cursor.execute(
                "SELECT id, candidate_name FROM candidates WHERE discipline_id = ? AND is_active = 1 ORDER BY display_order ASC",
                (disc_id,)
            )
            active_cands = cursor.fetchall()
            cand_count = len(active_cands)
            total_candidates += cand_count
            
            cand_map = {c["id"]: c["candidate_name"] for c in active_cands}
            expected_ranks = set(range(1, cand_count + 1))
            
            disc_submission = rankings_by_discipline.get(str(disc_id)) or rankings_by_discipline.get(disc_id) or {}
            
            # Count ranked
            ranked_count = sum(1 for cid in cand_map if disc_submission.get(str(cid)) is not None and disc_submission.get(str(cid)) != "")
            total_ranked += ranked_count
            
            discipline_progress[disc_id] = {
                "discipline_name": disc_name,
                "candidate_count": cand_count,
                "ranked_count": ranked_count,
                "is_complete": ranked_count == cand_count
            }
            
            # Validate candidate coverage
            submitted_cand_ids = set()
            rank_values = []
            
            for cid in cand_map:
                val = disc_submission.get(str(cid))
                if val is None or str(val).strip() == "":
                    continue
                try:
                    r = int(val)
                    submitted_cand_ids.add(cid)
                    rank_values.append(r)
                except ValueError:
                    pass
                    
            if len(submitted_cand_ids) < cand_count:
                missing = [cand_map[cid] for cid in cand_map if cid not in submitted_cand_ids]
                errors[disc_id] = f"Please rank all candidates before submitting. Missing: {', '.join(missing[:3])}{'...' if len(missing) > 3 else ''}"
                continue
                
            # Check duplicates
            if len(rank_values) != len(set(rank_values)):
                dupes = [str(r) for r in set(rank_values) if rank_values.count(r) > 1]
                errors[disc_id] = f"Rank {', '.join(dupes)} has already been assigned to another candidate. Each candidate must have a unique ranking."
                continue
                
            # Check exact sequence [1..N]
            if set(rank_values) != expected_ranks:
                errors[disc_id] = f"The ranking must use numbers from 1 to {cand_count} without duplication."
                continue
                
        is_valid = len(errors) == 0 and total_ranked == total_candidates and total_candidates > 0
        return is_valid, errors, discipline_progress, total_ranked, total_candidates

def submit_final_ranking(user_id: int, exercise_id: int, rankings_by_discipline: dict, ip_address: str = None):
    """
    Atomic submission of final rankings.
    """
    with get_db() as conn:
        cursor = conn.cursor()
        
        # 1. Exercise status check
        cursor.execute("SELECT * FROM ranking_exercises WHERE id = ?", (exercise_id,))
        ex = cursor.fetchone()
        if not ex:
            return False, "Exercise not found."
        if ex["status"] != "open":
            return False, "The ranking exercise is now closed. Voting is no longer available."
            
        # 2. Check if already submitted
        cursor.execute(
            "SELECT id, status FROM ranking_submissions WHERE user_id = ? AND exercise_id = ?",
            (user_id, exercise_id)
        )
        existing_sub = cursor.fetchone()
        if existing_sub and existing_sub["status"] == "submitted":
            return False, "You have already submitted your ranking for this exercise."
            
        # 3. Perform complete backend validation
        is_valid, errors, progress, total_ranked, total_candidates = validate_all_disciplines(
            exercise_id, rankings_by_discipline
        )
        if not is_valid:
            first_err = next(iter(errors.values())) if errors else "Incomplete rankings."
            return False, f"Ranking validation failed: {first_err}"
            
        # 4. Atomic record creation
        if not existing_sub:
            cursor.execute(
                """
                INSERT INTO ranking_submissions (user_id, exercise_id, status, submitted_at)
                VALUES (?, ?, 'submitted', CURRENT_TIMESTAMP)
                """,
                (user_id, exercise_id)
            )
            submission_id = cursor.lastrowid
        else:
            submission_id = existing_sub["id"]
            cursor.execute(
                """
                UPDATE ranking_submissions 
                SET status = 'submitted', submitted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
                """,
                (submission_id,)
            )
            # Clear any previous rankings to re-insert cleanly
            cursor.execute("DELETE FROM rankings WHERE submission_id = ?", (submission_id,))
            
        # Insert all rankings
        for disc_id_str, cand_ranks in rankings_by_discipline.items():
            discipline_id = int(disc_id_str)
            for cand_id_str, rank_val in cand_ranks.items():
                cursor.execute(
                    """
                    INSERT INTO rankings (
                        submission_id, user_id, exercise_id, discipline_id, candidate_id, ranking_number
                    ) VALUES (?, ?, ?, ?, ?, ?)
                    """,
                    (submission_id, user_id, exercise_id, discipline_id, int(cand_id_str), int(rank_val))
                )
                
        # 5. Audit trail
        log_audit_event(
            user_id=user_id,
            action="RANKING_SUBMITTED",
            entity_name="ranking_submissions",
            entity_id=submission_id,
            description=f"User finalized and submitted rankings for exercise #{exercise_id} ({total_ranked} candidates ranked)",
            ip_address=ip_address,
            conn=conn
        )
        
        return True, "Your ranking has been successfully submitted."

def admin_reopen_submission(admin_user_id: int, submission_id: int, reason: str = "", ip_address: str = None):
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(
            """
            SELECT s.*, u.full_name as voter_name, u.email as voter_email
            FROM ranking_submissions s
            JOIN users u ON s.user_id = u.id
            WHERE s.id = ?
            """,
            (submission_id,)
        )
        sub = cursor.fetchone()
        if not sub:
            return False, "Submission not found."
            
        if sub["status"] != "submitted":
            return False, f"Cannot reopen submission with status '{sub['status']}'."
            
        cursor.execute(
            """
            UPDATE ranking_submissions
            SET status = 'reopened', reopened_by = ?, reopened_at = CURRENT_TIMESTAMP,
                reopen_reason = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
            """,
            (admin_user_id, reason.strip(), submission_id)
        )
        
        log_audit_event(
            user_id=admin_user_id,
            action="RANKING_REOPENED",
            entity_name="ranking_submissions",
            entity_id=submission_id,
            description=f"Admin reopened ranking submission #{submission_id} for voter {sub['voter_name']}. Reason: {reason}",
            ip_address=ip_address,
            conn=conn
        )
        return True, f"Ranking for {sub['voter_name']} has been reopened."

def get_ranking_results(exercise_id: int):
    """
    Computes candidate matrices, average rankings, Rank 1 counts, and position for each discipline.
    """
    with get_db() as conn:
        cursor = conn.cursor()
        
        cursor.execute("SELECT * FROM ranking_exercises WHERE id = ?", (exercise_id,))
        ex = cursor.fetchone()
        if not ex:
            return None
        tie_breaker_method = ex["tie_breaker_method"]
        
        # Get all submitted voters for this exercise
        cursor.execute(
            """
            SELECT u.id, u.full_name, u.email, s.submitted_at
            FROM ranking_submissions s
            JOIN users u ON s.user_id = u.id
            WHERE s.exercise_id = ? AND s.status = 'submitted'
            ORDER BY u.full_name ASC
            """,
            (exercise_id,)
        )
        voters = [dict(v) for v in cursor.fetchall()]
        voter_count = len(voters)
        
        # Get all disciplines
        cursor.execute(
            "SELECT * FROM disciplines WHERE is_active = 1 ORDER BY display_order ASC, id ASC"
        )
        disciplines = [dict(d) for d in cursor.fetchall()]
        
        results_by_discipline = []
        
        for disc in disciplines:
            disc_id = disc["id"]
            cursor.execute(
                """
                SELECT c.* 
                FROM candidates c 
                WHERE c.discipline_id = ? AND c.is_active = 1 
                ORDER BY c.display_order ASC, c.candidate_name ASC
                """,
                (disc_id,)
            )
            cands = [dict(c) for c in cursor.fetchall()]
            
            # Fetch all submitted rankings for this discipline
            cursor.execute(
                """
                SELECT r.candidate_id, r.user_id, r.ranking_number
                FROM rankings r
                JOIN ranking_submissions s ON r.submission_id = s.id
                WHERE r.exercise_id = ? AND r.discipline_id = ? AND s.status = 'submitted'
                """,
                (exercise_id, disc_id)
            )
            ranks_raw = cursor.fetchall()
            
            ranks_by_cand = {c["id"]: {} for c in cands}
            for row in ranks_raw:
                c_id = row["candidate_id"]
                u_id = row["user_id"]
                r_num = row["ranking_number"]
                if c_id in ranks_by_cand:
                    ranks_by_cand[c_id][u_id] = r_num
                    
            candidate_stats = []
            for c in cands:
                c_id = c["id"]
                user_ranks = ranks_by_cand[c_id]
                assigned_ranks = list(user_ranks.values())
                submission_count = len(assigned_ranks)
                
                avg_rank = round(sum(assigned_ranks) / submission_count, 2) if submission_count > 0 else None
                rank1_count = sum(1 for r in assigned_ranks if r == 1)
                rank2_count = sum(1 for r in assigned_ranks if r == 2)
                
                # Distribution map
                distribution = {i: assigned_ranks.count(i) for i in range(1, len(cands) + 1)}
                
                candidate_stats.append({
                    "candidate_id": c_id,
                    "candidate_name": c["candidate_name"],
                    "candidate_title": c["candidate_title"],
                    "organisation": c["organisation"],
                    "nomination_form_url": c["nomination_form_url"],
                    "voter_rankings": user_ranks,
                    "submission_count": submission_count,
                    "average_rank": avg_rank,
                    "rank1_count": rank1_count,
                    "rank2_count": rank2_count,
                    "distribution": distribution
                })
                
            # Sorting logic:
            # Primary: average_rank ASC (None at the end)
            # Secondary: based on tie_breaker_method
            def sort_key(item):
                avg = item["average_rank"] if item["average_rank"] is not None else 999999
                if tie_breaker_method == "rank1_count":
                    return (avg, -item["rank1_count"], -item["rank2_count"])
                elif tie_breaker_method == "rank2_count":
                    return (avg, -item["rank2_count"], -item["rank1_count"])
                else: # admin_review
                    return (avg, item["candidate_name"])
                    
            candidate_stats.sort(key=sort_key)
            
            # Detect ties and assign position label
            for idx, item in enumerate(candidate_stats):
                if item["average_rank"] is None:
                    item["position"] = "-"
                    item["is_tied"] = False
                    continue
                    
                tied = False
                # Check neighbors
                if idx > 0 and candidate_stats[idx - 1]["average_rank"] == item["average_rank"]:
                    tied = True
                if idx < len(candidate_stats) - 1 and candidate_stats[idx + 1]["average_rank"] == item["average_rank"]:
                    tied = True
                    
                item["position"] = idx + 1
                item["is_tied"] = tied
                
            results_by_discipline.append({
                "discipline_id": disc_id,
                "discipline_name": disc["discipline_name"],
                "candidate_count": len(cands),
                "candidates": candidate_stats
            })
            
        return {
            "exercise": dict(ex),
            "voters": voters,
            "total_voters_submitted": voter_count,
            "disciplines": results_by_discipline
        }
