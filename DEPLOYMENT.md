# ASM Candidate Ranking & Due Diligence System
## Production Deployment & Operational Manual

**Target Environment:** Conventional Streamline Hosting (cPanel / DirectAdmin / Linux Apache/Nginx)  
**Database:** PostgreSQL (Supabase) or MySQL/SQLite  
**Framework:** Laravel 12 (PHP 8.2+)  
**Frontend Assets:** Zero-build architecture using CDN-delivered Tailwind CSS and Alpine.js (no Node.js or npm runtime required on the production server).

---

## 1. System Architecture & Zero-Build Design

This application was engineered specifically to adhere to high security standards, institutional confidentiality, and zero-build deployment requirements:

- **No Node.js Dependency:** Tailwind CSS and Alpine.js are served directly via high-availability CDNs. The deployment target does not require Node.js, npm, Vite build steps, or front-end bundlers.
- **No Redis / Daemon Requirement:** All queue, cache, and session drivers default to standard database/file storage (`CACHE_STORE=file`, `SESSION_DRIVER=database` or `file`, `QUEUE_CONNECTION=sync`), making it 100% compatible with shared or managed Streamline hosting environments.
- **Strict Server-Derived Security:** Voter discipline boundaries are enforced at the controller, service, middleware, and database layers.

---

## 2. Server Prerequisites

Ensure your Streamline hosting server meets the following minimum requirements:

- **PHP Version:** PHP 8.2 or higher
- **Required PHP Extensions:**
  - `pdo`
  - `pdo_pgsql` (if connecting to Supabase PostgreSQL) or `pdo_sqlite` / `pdo_mysql`
  - `openssl`
  - `mbstring`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `bcmath`
  - `fileinfo`
  - `curl`
- **Web Server:** Apache 2.4+ (with `mod_rewrite` enabled) or Nginx 1.20+
- **Composer:** Composer 2.x

---

## 3. Step-by-Step Deployment Guide

### Step 3.1: Upload Application Files
Extract or clone the application directory to your web root or document root. On standard cPanel/Streamline setups:
- Place the core application files in a directory **outside** `public_html` (e.g., `/home/username/asm-system/`).
- Point your domain's document root directly to `/home/username/asm-system/public`.

### Step 3.2: Environment Configuration (.env)
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Generate your unique application encryption key:
```bash
php artisan key:generate
```

Configure your application URL and environment:
```ini
APP_NAME="ASM Candidate Ranking System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.edu.my
```

### Step 3.3: Configure Supabase PostgreSQL Connection
In `.env`, configure the PostgreSQL database parameters provided in your Supabase project dashboard:

```ini
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.your-project-ref
DB_PASSWORD=YourSuperSecureSupabasePassword
DB_SCHEMA=public
DB_SSLMODE=require
```

> **Note on Connection Pooling (Port 6543 vs 5432):**  
> Supabase provides a Session / Transaction Pooler on port 6543 (using PgBouncer). When deploying on serverless or traditional PHP hosting where each request creates a new connection, using the pooled connection on port `6543` with `DB_SSLMODE=require` is strongly recommended.

*(Alternatively, for local development or SQLite hosting, use `DB_CONNECTION=sqlite` and ensure `database/database.sqlite` exists and is writable).*

### Step 3.4: Install Dependencies & Run Database Migrations
Run Composer in production mode (without dev dependencies):
```bash
composer install --no-dev --optimize-autoloader
```

Run database migrations and seed default disciplines, categories, and test datasets:
```bash
php artisan migrate --force --seed
```

### Step 3.5: Configure Storage & Permissions
Create the storage symbolic link for public candidate photos:
```bash
php artisan storage:link
```

Ensure private due diligence documents directory exists:
```bash
mkdir -p storage/app/private/due_diligence
```

Set proper write permissions on `storage` and `bootstrap/cache`:
```bash
chmod -R 775 storage bootstrap/cache
```

### Step 3.6: Optimize Laravel for Production
Execute Laravel's production caching commands to maximize performance:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. User Accounts & Management

The system user base is populated from official ASM records using the user import command:

```bash
php artisan users:import-excel
```

- **Voters (Fellows):** 819 verified Fellows mapped across the 8 disciplines with the `voting_user` role. Fellows can authenticate using either their official email address or their assigned username with their individual secure password.
- **System Administrators:** 10 ASM officers with the `administrator` role for overall exercise supervision, user management, and results auditing.
- **Dummy Accounts:** All placeholder/dummy development accounts (`@example.test`) have been purged from the system.

---

## 5. Security & Verification Checklist

1. **Discipline Isolation Verification:**
   - Voting users cannot view or rank candidates outside their assigned discipline.
   - Any crafted or tampered POST request with foreign candidate IDs is blocked with an immediate **HTTP 403 Forbidden** and recorded in the audit log.
2. **Confidential File Storage:**
   - Supporting documents submitted during due diligence are stored under `storage/app/private/due_diligence/` with randomized UUID filenames.
   - Files are never directly accessible via web URL. They are served exclusively through `DueDiligenceController::downloadDocument()` which verifies authenticated user authorization and logs downloads in `audit_logs`.
3. **Ballot Finalization & Reopening:**
   - Once a voter submits their complete 1..N ranking sequence, the ballot is locked.
   - Only an Administrator can reopen a locked ballot, requiring a mandatory documented reason which is written to the immutable audit log.
4. **Automated Test Suite:**
   - All tests can be executed at any time via:
     ```bash
     php artisan test
     ```

---

## 6. Maintenance Commands

- **Clear caches after code updates:**
  ```bash
  php artisan optimize:clear
  php artisan optimize
  ```
- **Export Audit Logs / Backup Database:**
  - Automated backups can be configured using standard PostgreSQL tools (`pg_dump`) or Supabase point-in-time recovery (PITR).
