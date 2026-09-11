import os
import sys
import unittest
import sqlite3

# Ensure app is on path
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.database import init_db, get_db
from app.seed import seed_database
from app.auth import (
    LocalAuthProvider, create_session, validate_session,
    destroy_session, hash_password, verify_password
)
from app.services.candidate_service import (
    get_all_disciplines, get_candidates, get_candidate_by_id
)
from app.services.ranking_service import (
    get_current_exercise, save_draft_rankings, validate_all_disciplines,
    submit_final_ranking, admin_reopen_submission, get_ranking_results,
    get_user_submission
)
from app.services.due_diligence_service import (
    create_due_diligence_submission, attach_supporting_document,
    get_candidate_due_diligence, get_due_diligence_categories
)
from app.services.storage_service import LocalStorageProvider
from app.services.export_service import export_ranking_csv, export_due_diligence_csv

class TestASMSystem(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        seed_database()
        with get_db() as conn:
            conn.execute("DELETE FROM rankings")
            conn.execute("DELETE FROM ranking_submissions")
            conn.execute("DELETE FROM supporting_documents")
            conn.execute("DELETE FROM due_diligence_submissions")
            conn.execute("DELETE FROM audit_logs")

    def test_01_auth_flow(self):
        auth = LocalAuthProvider()
        
        # Valid login
        user = auth.authenticate({"username": "voter1@asm.org.my", "password": "Voter123!"})
        self.assertIsNotNone(user)
        self.assertEqual(user["role"], "voting_user")
        
        # Invalid login
        bad_user = auth.authenticate({"username": "voter1@asm.org.my", "password": "WrongPassword"})
        self.assertIsNone(bad_user)
        
        # Session token
        token = create_session(user["id"])
        self.assertIsNotNone(token)
        
        # Validate session
        session_user = validate_session(token)
        self.assertIsNotNone(session_user)
        self.assertEqual(session_user["email"], "voter1@asm.org.my")
        
        # Destroy session
        destroy_session(token)
        self.assertIsNone(validate_session(token))

    def test_02_candidate_dossier_detail(self):
        cands = get_candidates()
        self.assertGreaterEqual(len(cands), 36)
        
        first = cands[0]
        detail = get_candidate_by_id(first["id"])
        self.assertIsNotNone(detail)
        
        # Check required fields for Candidate Information Pop-up
        self.assertIn("candidate_name", detail)
        self.assertIn("candidate_title", detail)
        self.assertIn("organisation", detail)
        self.assertIn("discipline_name", detail)
        self.assertIn("photo_url", detail)
        self.assertIn("basis_of_recommendation", detail)
        self.assertIn("area_of_expertise", detail)
        self.assertIn("qualifications", detail)
        self.assertIn("professional_memberships", detail)
        self.assertIn("nomination_form_url", detail)
        self.assertTrue(len(detail["basis_of_recommendation"]) > 20)

    def test_03_ranking_validation(self):
        ex = get_current_exercise()
        self.assertIsNotNone(ex)
        
        disciplines = get_all_disciplines()
        self.assertEqual(len(disciplines), 8)
        
        # Build valid rankings
        valid_rankings = {}
        for d in disciplines:
            c_list = get_candidates(discipline_id=d["id"])
            valid_rankings[str(d["id"])] = {
                str(c["id"]): rank + 1 for rank, c in enumerate(c_list)
            }
            
        is_valid, errors, progress, total_ranked, total_cands = validate_all_disciplines(ex["id"], valid_rankings)
        self.assertTrue(is_valid)
        self.assertEqual(len(errors), 0)
        self.assertEqual(total_ranked, 36)
        self.assertEqual(total_cands, 36)
        
        # Test Duplicate Ranking in Discipline 1
        dup_rankings = dict(valid_rankings)
        d1_id = str(disciplines[0]["id"])
        c_list_d1 = get_candidates(discipline_id=disciplines[0]["id"])
        # Give candidate 1 and 2 the same rank 1
        dup_rankings[d1_id] = {
            str(c_list_d1[0]["id"]): 1,
            str(c_list_d1[1]["id"]): 1,
            str(c_list_d1[2]["id"]): 3,
            str(c_list_d1[3]["id"]): 4
        }
        is_val, errs, _, _, _ = validate_all_disciplines(ex["id"], dup_rankings)
        self.assertFalse(is_val)
        self.assertIn(disciplines[0]["id"], errs)
        self.assertIn("already been assigned", errs[disciplines[0]["id"]])
        
        # Test Missing Ranking in Discipline 1
        missing_rankings = dict(valid_rankings)
        missing_rankings[d1_id] = {
            str(c_list_d1[0]["id"]): 1,
            str(c_list_d1[1]["id"]): 2,
            str(c_list_d1[2]["id"]): 3
            # Candidate 4 omitted
        }
        is_val, errs, _, _, _ = validate_all_disciplines(ex["id"], missing_rankings)
        self.assertFalse(is_val)
        self.assertIn("rank all candidates", errs[disciplines[0]["id"]])
        
        # Test Out-of-Range (Starts at 1, ends at 5 for 4 candidates)
        out_of_range = dict(valid_rankings)
        out_of_range[d1_id] = {
            str(c_list_d1[0]["id"]): 1,
            str(c_list_d1[1]["id"]): 2,
            str(c_list_d1[2]["id"]): 3,
            str(c_list_d1[3]["id"]): 5
        }
        is_val, errs, _, _, _ = validate_all_disciplines(ex["id"], out_of_range)
        self.assertFalse(is_val)
        self.assertIn("must use numbers from 1 to 4", errs[disciplines[0]["id"]])

    def test_04_submission_and_one_vote_restriction(self):
        ex = get_current_exercise()
        disciplines = get_all_disciplines()
        
        # Voter 1 submits valid ranking
        auth = LocalAuthProvider()
        voter1 = auth.authenticate({"username": "voter1@asm.org.my", "password": "Voter123!"})
        
        valid_rankings = {}
        for d in disciplines:
            c_list = get_candidates(discipline_id=d["id"])
            valid_rankings[str(d["id"])] = {
                str(c["id"]): rank + 1 for rank, c in enumerate(c_list)
            }
            
        success, msg = submit_final_ranking(voter1["id"], ex["id"], valid_rankings)
        self.assertTrue(success)
        self.assertIn("successfully submitted", msg)
        
        # Check database submission state
        sub = get_user_submission(voter1["id"], ex["id"])
        self.assertIsNotNone(sub)
        self.assertEqual(sub["status"], "submitted")
        self.assertEqual(len(sub["rankings"]), 36)
        
        # Attempt second submission -> MUST REJECT
        sec_success, sec_msg = submit_final_ranking(voter1["id"], ex["id"], valid_rankings)
        self.assertFalse(sec_success)
        self.assertIn("already submitted", sec_msg)

    def test_05_admin_reopen_submission(self):
        ex = get_current_exercise()
        auth = LocalAuthProvider()
        admin = auth.authenticate({"username": "admin@asm.org.my", "password": "Admin123!"})
        voter1 = auth.authenticate({"username": "voter1@asm.org.my", "password": "Voter123!"})
        
        sub = get_user_submission(voter1["id"], ex["id"])
        self.assertIsNotNone(sub)
        
        # Admin reopens
        reopen_success, reopen_msg = admin_reopen_submission(
            admin_user_id=admin["id"],
            submission_id=sub["id"],
            reason="Voter requested change to Discipline 2 ranking"
        )
        self.assertTrue(reopen_success)
        
        # Verify status is reopened
        updated_sub = get_user_submission(voter1["id"], ex["id"])
        self.assertEqual(updated_sub["status"], "reopened")
        self.assertEqual(updated_sub["reopened_by"], admin["id"])
        
        # Voter can now resubmit
        disciplines = get_all_disciplines()
        valid_rankings = {}
        for d in disciplines:
            c_list = get_candidates(discipline_id=d["id"])
            valid_rankings[str(d["id"])] = {
                str(c["id"]): rank + 1 for rank, c in enumerate(c_list)
            }
        resub_success, resub_msg = submit_final_ranking(voter1["id"], ex["id"], valid_rankings)
        self.assertTrue(resub_success)

    def test_06_due_diligence_and_files(self):
        cands = get_candidates()
        cand = cands[0]
        auth = LocalAuthProvider()
        voter2 = auth.authenticate({"username": "voter2@asm.org.my", "password": "Voter123!"})
        
        # Submit due diligence comment
        success, sub_id = create_due_diligence_submission(
            candidate_id=cand["id"],
            user_id=voter2["id"],
            category="Academic / Research Record",
            comment="Candidate has led multiple international research consortia with outstanding integrity."
        )
        self.assertTrue(success)
        self.assertIsInstance(sub_id, int)
        
        # Attach valid PDF document
        dummy_pdf_content = b"%PDF-1.4 test document content for assessment"
        doc_id, file_info = attach_supporting_document(
            submission_id=sub_id,
            candidate_id=cand["id"],
            user_id=voter2["id"],
            file_content=dummy_pdf_content,
            filename="recommendation_letter.pdf"
        )
        self.assertIsNotNone(doc_id)
        self.assertEqual(file_info["file_type"], "application/pdf")
        
        # Verify due diligence query retrieves submission with attachment
        dd_list = get_candidate_due_diligence(cand["id"], voter2["id"])
        self.assertGreaterEqual(len(dd_list), 1)
        sub_record = next(s for s in dd_list if s["id"] == sub_id)
        self.assertEqual(len(sub_record["supporting_documents"]), 1)
        self.assertEqual(sub_record["supporting_documents"][0]["file_name"], "recommendation_letter.pdf")
        
        # Test security: Disallowed file extension (.exe)
        storage = LocalStorageProvider()
        with self.assertRaises(ValueError) as ctx:
            storage.save_file(b"MZ...", "malware.exe")
        self.assertIn("not permitted", str(ctx.exception))

    def test_07_matrix_calculation_and_export(self):
        ex = get_current_exercise()
        
        # Voter 2 submits inverse rankings to create interesting matrix
        auth = LocalAuthProvider()
        voter2 = auth.authenticate({"username": "voter2@asm.org.my", "password": "Voter123!"})
        disciplines = get_all_disciplines()
        
        voter2_rankings = {}
        for d in disciplines:
            c_list = get_candidates(discipline_id=d["id"])
            n = len(c_list)
            voter2_rankings[str(d["id"])] = {
                str(c["id"]): (n - rank) for rank, c in enumerate(c_list)
            }
        submit_final_ranking(voter2["id"], ex["id"], voter2_rankings)
        
        # Get results
        results = get_ranking_results(ex["id"])
        self.assertIsNotNone(results)
        self.assertGreaterEqual(results["total_voters_submitted"], 2)
        
        # Export CSVs
        ranking_csv = export_ranking_csv(ex["id"])
        self.assertIn("ACADEMY OF SCIENCES MALAYSIA", ranking_csv)
        self.assertIn("Average Rank", ranking_csv)
        
        dd_csv = export_due_diligence_csv()
        self.assertIn("recommendation_letter.pdf", dd_csv)

if __name__ == "__main__":
    unittest.main()
