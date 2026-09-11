import io
import csv
from app.database import get_db
from app.services.ranking_service import get_ranking_results

def export_ranking_csv(exercise_id: int) -> str:
    results = get_ranking_results(exercise_id)
    if not results:
        return ""
        
    output = io.StringIO()
    writer = csv.writer(output)
    
    # Write Title & Metadata
    ex = results["exercise"]
    writer.writerow(["ACADEMY OF SCIENCES MALAYSIA (ASM)"])
    writer.writerow(["CANDIDATE RANKING ASSESSMENT REPORT"])
    writer.writerow(["Exercise Name", ex["exercise_name"]])
    writer.writerow(["Exercise Status", ex["status"]])
    writer.writerow(["Tie-Breaker Method", ex["tie_breaker_method"]])
    writer.writerow(["Total Voters Submitted", results["total_voters_submitted"]])
    writer.writerow([])
    
    voters = results["voters"]
    voter_headers = [f"{v['full_name']} (Rank)" for v in voters]
    
    for disc in results["disciplines"]:
        writer.writerow(["--- DISCIPLINE ---", disc["discipline_name"], f"({disc['candidate_count']} candidates)"])
        
        headers = ["Position", "Candidate Name", "Title", "Organisation"] + voter_headers + ["Average Rank", "Rank 1 Count", "Tied"]
        writer.writerow(headers)
        
        for cand in disc["candidates"]:
            row = [
                cand["position"],
                cand["candidate_name"],
                cand["candidate_title"],
                cand["organisation"]
            ]
            for v in voters:
                v_rank = cand["voter_rankings"].get(v["id"], "-")
                row.append(v_rank)
                
            row.extend([
                cand["average_rank"] if cand["average_rank"] is not None else "-",
                cand["rank1_count"],
                "YES" if cand["is_tied"] else "NO"
            ])
            writer.writerow(row)
        writer.writerow([])
        
    return output.getvalue()

def export_due_diligence_csv(exercise_id: int = None) -> str:
    output = io.StringIO()
    writer = csv.writer(output)
    
    writer.writerow(["ACADEMY OF SCIENCES MALAYSIA (ASM)"])
    writer.writerow(["CANDIDATE DUE DILIGENCE CONSOLIDATED REPORT"])
    writer.writerow([])
    writer.writerow([
        "Discipline", "Candidate Name", "Organisation", "Evaluator Name", "Evaluator Email",
        "Category", "Assessment / Comment", "Supporting Documents", "Submission Date"
    ])
    
    with get_db() as conn:
        cursor = conn.cursor()
        query = """
            SELECT dd.*, c.candidate_name, c.organisation, d.discipline_name,
                   u.full_name as evaluator_name, u.email as evaluator_email
            FROM due_diligence_submissions dd
            JOIN candidates c ON dd.candidate_id = c.id
            JOIN disciplines d ON c.discipline_id = d.id
            JOIN users u ON dd.user_id = u.id
            ORDER BY d.display_order ASC, c.candidate_name ASC, dd.submitted_at DESC
        """
        cursor.execute(query)
        rows = cursor.fetchall()
        
        for r in rows:
            cursor.execute(
                "SELECT file_name FROM supporting_documents WHERE due_diligence_submission_id = ?",
                (r["id"],)
            )
            files = [f["file_name"] for f in cursor.fetchall()]
            files_str = "; ".join(files) if files else "None"
            
            writer.writerow([
                r["discipline_name"],
                r["candidate_name"],
                r["organisation"],
                r["evaluator_name"],
                r["evaluator_email"],
                r["category"],
                r["comment"],
                files_str,
                r["submitted_at"]
            ])
            
    return output.getvalue()
