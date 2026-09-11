# Academy of Sciences Malaysia (ASM)
## Candidate Ranking & Due Diligence System

A secure, institutional-grade, full-stack web application developed for the **Academy of Sciences Malaysia (ASM)** to conduct candidate evaluations, confidential ordinal rankings across **8 disciplines**, and due diligence assessments for the Fellowship assessment exercise.

---

## 🌟 Key Features

* **Candidate Information Pop-up Modal**:
  * In-page modal dialog accessible from both the **Ranking Exercise** and **Candidate List**.
  * Displays candidate photograph, title, organisation, discipline, **Basis of Recommendation**, **Area of Expertise**, and structured **Qualifications & Professional Memberships**.
  * **"View Full Nomination Form"** opens the candidate's OneDrive form in a new tab without navigating away or losing in-progress rankings.
  * Direct **"Go to Due Diligence"** link.
  * Accessible (`Escape` key, backdrop click, keyboard navigation).

* **Strict Dynamic Ranking & Business Logic**:
  * Dynamic $1 \dots N$ selection numbers based on candidate counts in each discipline (e.g. 4 candidates $\rightarrow$ [1, 2, 3, 4]; 5 candidates $\rightarrow$ [1, 2, 3, 4, 5]; 6 candidates $\rightarrow$ [1, 2, 3, 4, 5, 6]).
  * **Duplicate Prevention**: Immediate visual and banner warnings if a rank is duplicated (*"Rank 1 has already been assigned to another candidate. Each candidate must have a unique ranking."*).
  * **Dual-Layer Validation**: Complete sequence check ($1 \dots N$ without gaps or duplicates) enforced on both client and backend.
  * **One Final Vote Per User**: Enforced via `UNIQUE(user_id, exercise_id)` in SQLite. Ballots become locked and read-only once submitted.
  * **Administrative Reopening**: Authorized administrators can reopen a submission with an audit reason, allowing voters to revise and resubmit.

* **Due Diligence & Secure Document Attachments**:
  * Multi-user commentary stream supporting configurable categories (*General Comment*, *Professional Background*, *Academic / Research Record*, *Leadership*, *Achievement*, *Conflict of Interest*, *Integrity / Reputation*, *Other Relevant Information*).
  * Secure file uploads with MIME checking, path traversal protection, 15 MB limit, and strict blocking of executable files (`.exe`, `.sh`, `.bat`, etc.).
  * Protected download endpoint (`/api/files/:id`).

* **Administration & Reporting**:
  * **Ranking Results Matrix**: Candidate rows vs. voter columns, calculating average rank, Rank 1 tally, and configurable tie-breaking rules.
  * **System Audit Trail**: Detailed immutable activity log of all logins, ballot submissions, reopenings, and file uploads.
  * **CSV Exports**: 1-click downloads for both **Ranking Results CSV** and **Due Diligence CSV** reports.

---

## 🚀 Quick Start (Local Run)

The system is built on **Python 3 Standard Library** and has **zero external package dependencies**.

```bash
# 1. Clone repository
git clone https://github.com/<your-username>/asm-candidate-ranking.git
cd asm-candidate-ranking

# 2. Run the application
python3 run.py
```

Open your browser and visit:
👉 **[http://127.0.0.1:8000/](http://127.0.0.1:8000/)**

---

## 🔑 Pre-Configured Demo Accounts

The login page includes convenient **1-click quick-login buttons**:

| Role | Email / Username | Password | Full Name |
|---|---|---|---|
| **Administrator** | `admin@asm.org.my` / `admin` | `Admin123!` | Dr. Aminah binti Razak (ASM Admin) |
| **Voting User 1** | `voter1@asm.org.my` / `voter1` | `Voter123!` | Academician Tan Sri Dr. Ahmad Ibrahim |
| **Voting User 2** | `voter2@asm.org.my` / `voter2` | `Voter123!` | Professor Emerita Datuk Dr. Mazlan Othman |
| **Voting User 3** | `voter3@asm.org.my` / `voter3` | `Voter123!` | Professor Dr. Wong Chee Kong |
| **Reviewer** | `reviewer@asm.org.my` / `reviewer` | `Reviewer123!` | Dato' Dr. Sharifah Maimunah (Reviewer) |

---

## 📤 How to Upload to GitHub

Follow these steps in your terminal to push this project to GitHub:

### Step 1: Initialize Git and Stage Files
```bash
git init
git add .
git commit -m "feat: complete ASM candidate ranking and due diligence system"
```

### Step 2: Create a New Repository on GitHub
1. Go to [GitHub.com/new](https://github.com/new).
2. Name your repository (e.g. `asm-candidate-ranking`).
3. Set visibility to **Private** (recommended for institutional evaluation systems) or **Public**.
4. Do **not** check "Initialize with README", .gitignore, or license (these are already created).
5. Click **Create repository**.

### Step 3: Link and Push to GitHub
```bash
git branch -M main
git remote add origin https://github.com/<YOUR-GITHUB-USERNAME>/asm-candidate-ranking.git
git push -u origin main
```

---

## 🧪 Running Automated Tests

Run the comprehensive unit test suite:

```bash
python3 -m unittest tests/test_system.py
```

Output:
```text
.......
----------------------------------------------------------------------
Ran 7 tests in 0.354s

OK
```

---

## 🐳 Docker Deployment (Optional)

```bash
# Build Docker image
docker build -t asm-candidate-ranking .

# Run Docker container
docker run -p 8000:8000 asm-candidate-ranking
```

---

## 🏛️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | Python 3 Multi-Threaded HTTP Server (`ThreadingHTTPServer`, `http.server`) |
| **Database** | SQLite 3 Relational Database with strict foreign keys and WAL mode |
| **Security** | PBKDF2-HMAC-SHA256 salted password hashing, high-entropy session tokens, path traversal guards |
| **Frontend** | Modern Vanilla ES6+ SPA, ASM institutional CSS design system |
| **Integrations** | Microsoft OneDrive / SharePoint links, modular Microsoft Entra ID SSO adapter |

---

## 🔒 Confidentiality Notice

All information contained within this application constitutes confidential institutional assessment data under the **Academy of Sciences Malaysia Act 1994**.
