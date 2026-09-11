/**
 * Main Application Controller for ASM Candidate Ranking & Due Diligence System
 * Academy of Sciences Malaysia (ASM)
 */

const App = {
  user: null,
  exercise: null,
  disciplines: [],
  candidates: [],
  rankingDraft: {}, // { discipline_id: { candidate_id: rank } }
  submissionStatus: null, // 'draft', 'submitted', 'reopened'
  currentDisciplineId: 1,

  async init() {
    // Listen for unauthorized events
    window.addEventListener('asm:unauthorized', () => {
      this.user = null;
      this.updateHeaderAuth();
      this.navigate('login');
    });

    // Hash change router
    window.addEventListener('hashchange', () => this.handleRoute());

    // Check existing session
    const res = await API.get('/api/auth/me');
    if (res && res.authenticated) {
      this.user = res.user;
    }

    this.updateHeaderAuth();
    this.handleRoute();
  },

  updateHeaderAuth() {
    const userControls = document.getElementById('userControls');
    const adminNavItems = document.querySelectorAll('.admin-nav-item');

    if (!this.user) {
      userControls.innerHTML = `
        <a href="#/login" class="btn btn-gold btn-sm">🔒 Login</a>
      `;
      adminNavItems.forEach(el => el.classList.add('hidden'));
    } else {
      userControls.innerHTML = `
        <div class="user-profile-badge">
          <span class="user-name">👤 ${this.escapeHtml(this.user.full_name || this.user.username)}</span>
          <span class="badge-role ${this.user.role}">${this.user.role.replace('_', ' ')}</span>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="App.logout()">Logout</button>
      `;

      // Role check for admin/reviewer navigation items
      const isAdminOrReviewer = ['admin', 'reviewer'].includes(this.user.role);
      const isAdminOnly = this.user.role === 'admin';

      adminNavItems.forEach(el => {
        const adminOnly = el.getAttribute('data-admin-only') === 'true';
        if (adminOnly) {
          el.classList.toggle('hidden', !isAdminOnly);
        } else {
          el.classList.toggle('hidden', !isAdminOrReviewer);
        }
      });
    }
  },

  navigate(route) {
    window.location.hash = `#/${route}`;
  },

  handleRoute() {
    const hash = window.location.hash.replace('#/', '') || 'home';
    const [path, queryString] = hash.split('?');
    const queryParams = new URLSearchParams(queryString || '');

    // Highlight active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
      const href = link.getAttribute('href').replace('#/', '');
      link.classList.toggle('active', href === path || (path.startsWith('admin') && href.startsWith('admin')));
    });

    // Protected Route Guards
    const protectedRoutes = ['ranking', 'due-diligence', 'dashboard', 'admin'];
    const requiresAuth = protectedRoutes.some(r => path.startsWith(r));

    if (requiresAuth && !this.user) {
      this.renderLogin(path);
      return;
    }

    // Admin Route Guards
    if (path.startsWith('admin') && !['admin', 'reviewer'].includes(this.user?.role)) {
      this.renderForbidden();
      return;
    }

    // Dispatch view
    switch (path) {
      case 'home':
        this.renderHome();
        break;
      case 'login':
        this.renderLogin(queryParams.get('redirect'));
        break;
      case 'dashboard':
        this.renderDashboard();
        break;
      case 'ranking':
        this.renderRanking();
        break;
      case 'candidates':
        this.renderCandidateList(queryParams);
        break;
      case 'due-diligence':
        this.renderDueDiligence(queryParams.get('candidate_id'));
        break;
      case 'information':
        this.renderInformation();
        break;
      case 'admin':
      case 'admin/dashboard':
        this.renderAdminDashboard();
        break;
      case 'admin/results':
        this.renderAdminResults();
        break;
      case 'admin/users':
        this.renderAdminUsers();
        break;
      case 'admin/candidates':
        this.renderAdminCandidates();
        break;
      case 'admin/audit':
        this.renderAdminAudit();
        break;
      case 'admin/reports':
        this.renderAdminReports();
        break;
      default:
        this.renderHome();
    }
  },

  // =========================================================================
  // VIEW: LOGIN
  // =========================================================================
  renderLogin(redirectRoute) {
    const main = document.getElementById('mainContent');
    main.innerHTML = `
      <div style="max-width: 480px; margin: 40px auto;">
        <div class="card" style="box-shadow: var(--shadow-lg);">
          <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-logo-badge" style="margin: 0 auto 12px; width: 56px; height: 56px; font-size: 20px;">ASM</div>
            <h2 style="color: var(--asm-navy); font-size: 20px; font-weight: 700;">Academy of Sciences Malaysia</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Candidate Ranking & Due Diligence System</p>
          </div>

          <div id="loginAlertContainer"></div>

          <form id="loginForm" onsubmit="App.handleLogin(event, '${redirectRoute || 'dashboard'}')">
            <div class="form-group">
              <label class="form-label" for="loginUsername">Email / Username</label>
              <input type="text" id="loginUsername" class="form-control" placeholder="e.g. voter1@asm.org.my or admin" required autofocus autocomplete="username" />
            </div>

            <div class="form-group">
              <label class="form-label" for="loginPassword">Password</label>
              <input type="password" id="loginPassword" class="form-control" placeholder="••••••••" required autocomplete="current-password" />
            </div>

            <button type="submit" class="btn btn-gold btn-lg" style="width: 100%; margin-top: 10px;">
              Sign In to System ➔
            </button>
          </form>

          <!-- Quick Login Helper for Testing -->
          <div style="margin-top: 28px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
              ⚡ Quick Login (Pre-configured Test Accounts):
            </div>
            <div class="quick-login-pills">
              <button type="button" class="quick-login-btn" onclick="App.quickLogin('admin@asm.org.my', 'Admin123!')">
                👑 Admin
              </button>
              <button type="button" class="quick-login-btn" onclick="App.quickLogin('voter1@asm.org.my', 'Voter123!')">
                🗳️ Voter 1 (Tan Sri Dr. Ahmad)
              </button>
              <button type="button" class="quick-login-btn" onclick="App.quickLogin('voter2@asm.org.my', 'Voter123!')">
                🗳️ Voter 2 (Datuk Dr. Mazlan)
              </button>
              <button type="button" class="quick-login-btn" onclick="App.quickLogin('voter3@asm.org.my', 'Voter123!')">
                🗳️ Voter 3 (Prof. Dr. Wong)
              </button>
              <button type="button" class="quick-login-btn" onclick="App.quickLogin('reviewer@asm.org.my', 'Reviewer123!')">
                👁️ Reviewer
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  quickLogin(email, password) {
    document.getElementById('loginUsername').value = email;
    document.getElementById('loginPassword').value = password;
    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
  },

  async handleLogin(e, redirectRoute) {
    e.preventDefault();
    const alertBox = document.getElementById('loginAlertContainer');
    alertBox.innerHTML = '';

    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;

    const res = await API.post('/api/auth/login', { username, password });
    if (!res || !res.success) {
      alertBox.innerHTML = `
        <div class="alert alert-danger">
          ⚠️ ${res?.error || 'Invalid username or password. Please try again.'}
        </div>
      `;
      return;
    }

    this.user = res.user;
    this.updateHeaderAuth();

    // Destination routing
    const target = redirectRoute && redirectRoute !== 'login' ? redirectRoute : 'dashboard';
    this.navigate(target);
  },

  async logout() {
    await API.post('/api/auth/logout', {});
    this.user = null;
    this.rankingDraft = {};
    this.updateHeaderAuth();
    this.navigate('home');
  },

  // =========================================================================
  // VIEW: HOME & DASHBOARD
  // =========================================================================
  async renderHome() {
    const main = document.getElementById('mainContent');
    const authCta = this.user
      ? `<a href="#/ranking" class="btn btn-gold btn-lg">Start Ranking Exercise ➔</a>`
      : `<a href="#/login?redirect=ranking" class="btn btn-gold btn-lg">Login to Start Ranking Exercise ➔</a>`;

    main.innerHTML = `
      <div class="card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border-top: 4px solid var(--asm-navy);">
        <div style="max-width: 900px;">
          <span class="badge-role voting_user" style="margin-bottom: 12px; display: inline-block;">Official Assessment Portal</span>
          <h1 style="font-size: 28px; color: var(--asm-navy); font-weight: 800; margin-bottom: 12px; line-height: 1.2;">
            Candidate Ranking & Due Diligence System
          </h1>
          <p style="font-size: 16px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">
            Welcome to the Candidate Ranking Exercise. This system is provided to facilitate the assessment and confidential ranking of shortlisted candidates across eight disciplines for the Academy of Sciences Malaysia.
          </p>

          <div style="margin-bottom: 30px;">
            ${authCta}
          </div>
        </div>
      </div>

      <!-- Ranking Methodology & Instructions -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">⚖️ Ranking Method</h2>
          </div>
          <div style="background-color: var(--bg-accent); padding: 16px; border-radius: var(--radius-md); margin-bottom: 16px; border-left: 4px solid var(--asm-navy-light);">
            <div style="font-size: 18px; font-weight: 700; color: var(--asm-navy);">Rank 1 = Highest Preference</div>
            <div style="font-size: 15px; font-weight: 600; color: var(--text-muted); margin-top: 4px;">Higher Number = Lower Preference</div>
          </div>
          <p style="color: var(--text-main); font-size: 14px; line-height: 1.6;">
            Users are required to review the candidate nomination dossier and assign a unique preference rank to each candidate within their respective discipline. Each candidate must receive a unique ranking number from 1 to N without duplication.
          </p>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">📜 Critical Instructions</h2>
          </div>
          <ul style="padding-left: 20px; font-size: 14px; color: var(--text-main); line-height: 1.7;">
            <li>Each candidate within a discipline must receive a <strong>unique ranking</strong>.</li>
            <li>A ranking number cannot be assigned to more than one candidate within the same discipline.</li>
            <li><strong>Every candidate must be ranked</strong> across all 8 disciplines before final submission.</li>
            <li>Click candidate names to review the <strong>Nomination Dossier Pop-up</strong> and view OneDrive forms.</li>
            <li>Due diligence comments and supporting documents are strictly confidential.</li>
          </ul>
        </div>
      </div>
    `;
  },

  async renderDashboard() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading assessment dashboard...</p></div>`;

    const [exRes, subRes, discRes] = await Promise.all([
      API.get('/api/exercises/current'),
      API.get('/api/ranking/my-submission'),
      API.get('/api/disciplines')
    ]);

    const ex = exRes?.exercise;
    const sub = subRes?.submission;
    const disciplines = discRes?.disciplines || [];

    const totalCandidates = disciplines.reduce((acc, d) => acc + (d.candidate_count || 0), 0);
    const rankedCount = sub?.rankings ? sub.rankings.length : 0;
    const completionPercent = totalCandidates > 0 ? Math.round((rankedCount / totalCandidates) * 100) : 0;

    const status = sub?.status || 'not_started';
    let statusBadge = `<span class="badge-role reviewer">Not Started</span>`;
    if (status === 'submitted') statusBadge = `<span class="badge-role" style="background: var(--success); color: white;">Submitted ✓</span>`;
    else if (status === 'reopened') statusBadge = `<span class="badge-role" style="background: var(--warning); color: white;">Reopened 🔄</span>`;
    else if (rankedCount > 0) statusBadge = `<span class="badge-role voting_user">In Progress</span>`;

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">Welcome, ${this.escapeHtml(this.user.full_name)}</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Role: ${this.user.role.replace('_', ' ').toUpperCase()} • Active Exercise: ${this.escapeHtml(ex?.exercise_name || 'ASM Ranking Exercise 2026')}</p>
          </div>
          <div>${statusBadge}</div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin: 20px 0;">
          <div style="background: #F8FAFC; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Ranking Progress</div>
            <div style="font-size: 28px; font-weight: 800; color: var(--asm-navy); margin: 6px 0;">
              ${rankedCount} / ${totalCandidates} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">candidates</span>
            </div>
            <div class="progress-bar-container">
              <div class="progress-bar-fill" style="width: ${completionPercent}%"></div>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); text-align: right;">${completionPercent}% completed</div>
          </div>

          <div style="background: #F8FAFC; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Exercise Disciplines</div>
            <div style="font-size: 28px; font-weight: 800; color: var(--asm-navy); margin: 6px 0;">
              8 Disciplines
            </div>
            <p style="font-size: 13px; color: var(--text-muted);">All 8 disciplines must be fully ranked before final submission.</p>
          </div>

          <div style="background: #F8FAFC; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Submission Status</div>
            <div style="font-size: 20px; font-weight: 700; color: var(--asm-navy); margin: 8px 0;">
              ${status === 'submitted' ? 'Locked & Sealed' : 'Open for Voting'}
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
              ${sub?.submitted_at ? 'Submitted on: ' + sub.submitted_at : 'One final submission per voter.'}
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 14px; margin-top: 10px;">
          ${status === 'submitted'
            ? `<a href="#/ranking" class="btn btn-secondary btn-lg">View My Submission ➔</a>`
            : `<a href="#/ranking" class="btn btn-gold btn-lg">Continue Ranking Exercise ➔</a>`
          }
          <a href="#/candidates" class="btn btn-secondary btn-lg">Browse Candidates ➔</a>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // VIEW: RANKING EXERCISE (CORE MODULE)
  // =========================================================================
  async renderRanking() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading ranking exercise interface...</p></div>`;

    const [exRes, discRes, subRes] = await Promise.all([
      API.get('/api/exercises/current'),
      API.get('/api/disciplines'),
      API.get('/api/ranking/my-submission')
    ]);

    this.exercise = exRes?.exercise;
    this.disciplines = discRes?.disciplines || [];
    const sub = subRes?.submission;
    this.submissionStatus = sub?.status || 'draft';

    // Populate draft ranking map from existing database records
    this.rankingDraft = {};
    if (sub && sub.rankings) {
      for (const r of sub.rankings) {
        const dId = String(r.discipline_id);
        const cId = String(r.candidate_id);
        if (!this.rankingDraft[dId]) this.rankingDraft[dId] = {};
        this.rankingDraft[dId][cId] = r.ranking_number;
      }
    }

    this.renderRankingUI();
  },

  renderRankingUI() {
    const main = document.getElementById('mainContent');
    const isLocked = this.submissionStatus === 'submitted';

    // Calculate completion metrics
    let totalCandidates = 0;
    let totalRanked = 0;
    let completedDisciplines = 0;

    for (const d of this.disciplines) {
      totalCandidates += d.candidate_count;
      const dMap = this.rankingDraft[String(d.id)] || {};
      const rankedInD = Object.values(dMap).filter(v => v !== null && v !== '').length;
      totalRanked += rankedInD;
      if (rankedInD === d.candidate_count && d.candidate_count > 0) {
        completedDisciplines++;
      }
    }

    const currentDisc = this.disciplines.find(d => d.id === this.currentDisciplineId) || this.disciplines[0];
    this.currentDisciplineId = currentDisc?.id || 1;

    main.innerHTML = `
      <!-- Header Banner & Guidance -->
      <div class="card" style="margin-bottom: 20px; border-left: 5px solid var(--asm-navy);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px;">
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: var(--asm-navy);">
              Candidate Ranking Exercise
            </h1>
            <div style="margin-top: 6px; font-size: 15px; font-weight: 700; color: #1E293B;">
              👉 Please assign a unique ranking number to every candidate.
            </div>
            <div style="font-size: 13px; font-weight: 600; color: #64748B; margin-top: 2px;">
              <strong>1 = Highest Preference</strong> • <strong>Higher number = Lower Preference</strong>
            </div>
          </div>

          <div style="text-align: right;">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Overall Progress</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--asm-navy);">
              ${totalRanked} / ${totalCandidates} <span style="font-size: 13px; font-weight: 500;">Ranked</span>
            </div>
            <div style="font-size: 13px; font-weight: 600; color: ${completedDisciplines === 8 ? 'var(--success)' : 'var(--warning)'};">
              ${completedDisciplines} / 8 Disciplines Complete
            </div>
          </div>
        </div>

        ${isLocked ? `
          <div class="alert alert-success" style="margin-top: 16px; margin-bottom: 0;">
            🔒 <strong>Ranking Status: SUBMITTED ✓</strong> — Your final ranking has been recorded and locked. Editing is disabled unless reopened by an Administrator.
          </div>
        ` : ''}
      </div>

      <!-- Discipline Navigation Tabs -->
      <div class="discipline-tabs">
        ${this.disciplines.map(d => {
          const dMap = this.rankingDraft[String(d.id)] || {};
          const count = Object.values(dMap).filter(v => v !== null && v !== '').length;
          const isComplete = count === d.candidate_count;
          const isActive = d.id === this.currentDisciplineId;

          return `
            <button type="button" class="disc-tab-btn ${isActive ? 'active' : ''} ${isComplete ? 'completed' : ''}" onclick="App.selectDiscipline(${d.id})">
              ${isComplete ? '✓ ' : ''}Discipline ${d.display_order || d.id}
              <span class="badge-count">${count}/${d.candidate_count}</span>
            </button>
          `;
        }).join('')}
      </div>

      <!-- Active Discipline Ranking Section -->
      <div id="activeDisciplineContainer">
        <!-- Injected via loadActiveDisciplineTable -->
      </div>

      <!-- Submission Actions Bar -->
      <div class="card" style="margin-top: 24px; background: #FFFFFF; border: 2px solid ${completedDisciplines === 8 ? 'var(--asm-gold)' : 'var(--border-color)'};">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--asm-navy);">Ready to Submit Final Ranking?</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
              ${completedDisciplines === 8 
                ? 'All 8 disciplines are completely and uniquely ranked. You can now finalize your vote.' 
                : `Please complete all disciplines before submitting. (${8 - completedDisciplines} disciplines remaining).`}
            </p>
          </div>

          <div style="display: flex; gap: 10px;">
            ${!isLocked ? `
              <button type="button" class="btn btn-secondary" onclick="App.saveCurrentDraft(true)">
                💾 Save Draft Progress
              </button>
              <button type="button" class="btn btn-gold btn-lg" id="btnSubmitRanking" ${completedDisciplines === 8 ? '' : 'disabled'} onclick="App.confirmAndSubmitRanking()">
                🔒 Submit Final Ranking
              </button>
            ` : `
              <span style="font-weight: 700; color: var(--success); font-size: 15px; align-self: center;">
                ✓ Submitted and Sealed
              </span>
            `}
          </div>
        </div>
      </div>
    `;

    this.loadActiveDisciplineTable(this.currentDisciplineId);
  },

  selectDiscipline(discId) {
    this.currentDisciplineId = discId;
    this.renderRankingUI();
  },

  async loadActiveDisciplineTable(discId) {
    const container = document.getElementById('activeDisciplineContainer');
    if (!container) return;

    container.innerHTML = `<div style="padding: 30px; text-align: center;"><p>Loading candidates for discipline...</p></div>`;

    const res = await API.get(`/api/candidates?discipline_id=${discId}`);
    const candidates = res?.candidates || [];
    const disc = this.disciplines.find(d => d.id === discId);
    const isLocked = this.submissionStatus === 'submitted';

    const candCount = candidates.length;
    const dMap = this.rankingDraft[String(discId)] || {};

    // Validate duplicate ranks currently in memory
    const assignedRanks = Object.values(dMap).filter(v => v !== null && v !== '').map(Number);
    const duplicates = assignedRanks.filter((item, index) => assignedRanks.indexOf(item) !== index);

    let alertHtml = '';
    if (duplicates.length > 0) {
      alertHtml = `
        <div class="alert alert-danger" style="margin-bottom: 16px;">
          ⚠️ <strong>Duplicate Ranking:</strong> Rank ${[...new Set(duplicates)].join(', ')} has already been assigned to another candidate. Each candidate must have a unique ranking.
        </div>
      `;
    }

    container.innerHTML = `
      <div class="card" style="padding: 20px;">
        <div class="card-header" style="margin-bottom: 14px;">
          <div>
            <h2 class="card-title" style="font-size: 18px;">${this.escapeHtml(disc?.discipline_name || 'Discipline')}</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">${this.escapeHtml(disc?.description || '')}</p>
          </div>
          <div style="font-weight: 700; font-size: 14px; color: var(--asm-navy); background: #F1F5F9; padding: 6px 14px; border-radius: var(--radius-full);">
            ${candCount} Candidates (Ranks 1 to ${candCount})
          </div>
        </div>

        ${alertHtml}

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 60px;">No.</th>
                <th>Candidate</th>
                <th>Organisation & Title</th>
                <th style="width: 140px; text-align: center;">Rank</th>
              </tr>
            </thead>
            <tbody>
              ${candidates.map((c, idx) => {
                const currentRank = dMap[String(c.id)] || '';
                const isDup = currentRank !== '' && duplicates.includes(Number(currentRank));

                return `
                  <tr>
                    <td style="font-weight: 600; color: var(--text-muted);">${idx + 1}</td>
                    <td>
                      <!-- Clickable Candidate Name -> Opens Candidate Information Pop-up Modal -->
                      <a href="javascript:void(0)" class="candidate-name-link" onclick="CandidateModal.open(${c.id})" title="Click to view nomination dossier">
                        ${this.escapeHtml(c.candidate_name)}
                        <span style="font-size: 12px; color: var(--asm-gold);">ℹ️</span>
                      </a>
                    </td>
                    <td>
                      <div style="font-weight: 600; font-size: 13px;">${this.escapeHtml(c.organisation || 'ASM')}</div>
                      <div style="font-size: 12px; color: var(--text-muted);">${this.escapeHtml(c.candidate_title || '')}</div>
                    </td>
                    <td style="text-align: center;">
                      ${isLocked ? `
                        <span style="font-weight: 800; font-size: 16px; color: var(--asm-navy); background: #EFF6FF; padding: 4px 14px; border-radius: 4px; border: 1px solid #BFDBFE;">
                          ${currentRank || '-'}
                        </span>
                      ` : `
                        <select class="form-select rank-select ${currentRank ? 'ranked' : ''} ${isDup ? 'duplicate-error' : ''}"
                                onchange="App.onRankChange(${discId}, ${c.id}, this.value)">
                          <option value="">Select</option>
                          ${Array.from({ length: candCount }, (_, i) => i + 1).map(r => `
                            <option value="${r}" ${String(currentRank) === String(r) ? 'selected' : ''}>
                              ${r} ${r === 1 ? '★ (Top)' : ''}
                            </option>
                          `).join('')}
                        </select>
                      `}
                    </td>
                  </tr>
                `;
              }).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;
  },

  onRankChange(disciplineId, candidateId, rankValue) {
    const dId = String(disciplineId);
    const cId = String(candidateId);

    if (!this.rankingDraft[dId]) {
      this.rankingDraft[dId] = {};
    }

    this.rankingDraft[dId][cId] = rankValue ? parseInt(rankValue, 10) : '';

    // Re-render UI to update validation messages, progress bar, and submit button state
    this.renderRankingUI();

    // Auto-save draft in background
    this.saveCurrentDraft(false);
  },

  async saveCurrentDraft(showFeedback = true) {
    if (this.submissionStatus === 'submitted') return;

    const res = await API.post('/api/ranking/save-draft', { rankings: this.rankingDraft });
    if (showFeedback) {
      if (res && res.success) {
        alert('Draft rankings saved successfully.');
      } else {
        alert('Failed to save draft: ' + (res?.error || 'Unknown error'));
      }
    }
  },

  async confirmAndSubmitRanking() {
    // 1. Run full backend validation check first
    const valRes = await API.post('/api/ranking/validate', { rankings: this.rankingDraft });
    if (!valRes || !valRes.is_valid) {
      const errMsgs = Object.values(valRes?.errors || {}).join('\n• ');
      alert('Cannot submit ranking exercise. Validation errors exist:\n• ' + errMsgs);
      return;
    }

    // 2. Confirmation prompt as required by specification
    const confirmed = window.confirm(
      "You have completed the ranking exercise. Once submitted, your ranking will be recorded and cannot normally be changed. Do you want to submit your ranking?"
    );

    if (!confirmed) return;

    // 3. Submit
    const res = await API.post('/api/ranking/submit', { rankings: this.rankingDraft });
    if (!res || !res.success) {
      alert('Submission rejected: ' + (res?.error || 'Validation failure.'));
      return;
    }

    alert('Your ranking has been successfully submitted.');
    this.submissionStatus = 'submitted';
    this.renderRanking();
  },

  // =========================================================================
  // VIEW: CANDIDATE LIST (SEARCH & FILTER)
  // =========================================================================
  async renderCandidateList(queryParams) {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading candidate list...</p></div>`;

    const discRes = await API.get('/api/disciplines');
    this.disciplines = discRes?.disciplines || [];

    const selectedDisc = queryParams?.get('discipline_id') || '';
    const searchQuery = queryParams?.get('search') || '';

    const candRes = await API.get(`/api/candidates?discipline_id=${selectedDisc}&search=${encodeURIComponent(searchQuery)}`);
    const candidates = candRes?.candidates || [];

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">Candidate Directory</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              Review shortlisted scientific nominees across all disciplines. Click any candidate name to open their Nomination Dossier.
            </p>
          </div>
          <div style="font-size: 14px; font-weight: 700; color: var(--asm-navy);">
            ${candidates.length} Candidates Found
          </div>
        </div>

        <!-- Search & Filter Controls -->
        <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 24px; background: #F8FAFC; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
          <div style="flex: 2; min-width: 240px;">
            <label class="form-label" style="font-size: 12px;">Search Candidate / Organisation / Expertise</label>
            <input type="text" id="candidateSearchInput" class="form-control" placeholder="Search by name, institution or research field..." value="${this.escapeHtml(searchQuery)}" onkeyup="if(event.key==='Enter') App.applyCandidateFilters()" />
          </div>

          <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 12px;">Filter by Discipline</label>
            <select id="candidateDisciplineFilter" class="form-select" onchange="App.applyCandidateFilters()">
              <option value="">All 8 Disciplines</option>
              ${this.disciplines.map(d => `
                <option value="${d.id}" ${String(selectedDisc) === String(d.id) ? 'selected' : ''}>
                  ${this.escapeHtml(d.discipline_name)}
                </option>
              `).join('')}
            </select>
          </div>

          <div style="display: flex; align-items: flex-end;">
            <button type="button" class="btn btn-primary" onclick="App.applyCandidateFilters()">Filter Results</button>
          </div>
        </div>

        <!-- Candidate Grouping / Table -->
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 50px;">Photo</th>
                <th>Candidate Name</th>
                <th>Organisation & Title</th>
                <th>Discipline</th>
                <th>Area of Expertise</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              ${candidates.length === 0 ? `
                <tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">No candidates matching your search criteria.</td></tr>
              ` : candidates.map(c => `
                <tr>
                  <td>
                    <img src="${c.photo_url || ''}" alt="" style="width: 40px; height: 48px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'40\\' height=\\'48\\' fill=\\'%23CBD5E1\\'><rect width=\\'40\\' height=\\'48\\'/></svg>';" />
                  </td>
                  <td>
                    <!-- Clickable candidate name opens Candidate Information Pop-up Modal -->
                    <a href="javascript:void(0)" class="candidate-name-link" onclick="CandidateModal.open(${c.id})" style="font-size: 15px;">
                      ${this.escapeHtml(c.candidate_name)}
                    </a>
                  </td>
                  <td>
                    <div style="font-weight: 600;">${this.escapeHtml(c.organisation || '')}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">${this.escapeHtml(c.candidate_title || '')}</div>
                  </td>
                  <td>
                    <span class="candidate-meta-discipline" style="font-size: 11px;">
                      ${this.escapeHtml(c.discipline_name || '')}
                    </span>
                  </td>
                  <td style="font-size: 13px; color: var(--text-muted);">
                    ${this.escapeHtml(c.area_of_expertise || '')}
                  </td>
                  <td style="text-align: right; white-space: nowrap;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="CandidateModal.open(${c.id})">
                      Dossier ℹ️
                    </button>
                    <a href="#/due-diligence?candidate_id=${c.id}" class="btn btn-primary btn-sm">
                      Due Diligence 📋
                    </a>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;
  },

  applyCandidateFilters() {
    const search = document.getElementById('candidateSearchInput').value.trim();
    const disc = document.getElementById('candidateDisciplineFilter').value;
    window.location.hash = `#/candidates?discipline_id=${disc}&search=${encodeURIComponent(search)}`;
  },

  // =========================================================================
  // VIEW: DUE DILIGENCE MODULE
  // =========================================================================
  async renderDueDiligence(candidateIdParam) {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading Due Diligence Assessment module...</p></div>`;

    const [candsRes, catsRes] = await Promise.all([
      API.get('/api/candidates'),
      API.get('/api/due-diligence/categories')
    ]);

    const candidates = candsRes?.candidates || [];
    const categories = catsRes?.categories || [];

    const activeCandId = candidateIdParam ? parseInt(candidateIdParam, 10) : (candidates[0]?.id || null);
    const activeCand = candidates.find(c => c.id === activeCandId) || candidates[0];

    // Fetch existing due diligence comments for this candidate
    let submissions = [];
    if (activeCand) {
      const subRes = await API.get(`/api/candidates/${activeCand.id}/due-diligence`);
      submissions = subRes?.submissions || [];
    }

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">Candidate Due Diligence Assessment</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              Provide factual evaluations, observations, and supporting documentation regarding shortlisted nominees.
            </p>
          </div>
          <div style="min-width: 260px;">
            <select class="form-select" onchange="window.location.hash = '#/due-diligence?candidate_id=' + this.value">
              ${candidates.map(c => `
                <option value="${c.id}" ${c.id === activeCand?.id ? 'selected' : ''}>
                  ${this.escapeHtml(c.candidate_name)} (${this.escapeHtml(c.discipline_name?.split(':')[0] || '')})
                </option>
              `).join('')}
            </select>
          </div>
        </div>

        ${activeCand ? `
          <!-- Selected Candidate Banner -->
          <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 16px;">
              <img src="${activeCand.photo_url || ''}" alt="" style="width: 56px; height: 68px; object-fit: cover; border-radius: 6px; box-shadow: var(--shadow-sm);" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'56\\' height=\\'68\\' fill=\\'%23CBD5E1\\'><rect width=\\'56\\' height=\\'68\\'/></svg>';" />
              <div>
                <h2 style="font-size: 18px; font-weight: 700; color: var(--asm-navy); margin-bottom: 2px;">
                  ${this.escapeHtml(activeCand.candidate_name)}
                </h2>
                <div style="font-size: 13px; color: var(--text-muted);">
                  ${this.escapeHtml(activeCand.candidate_title || '')} • ${this.escapeHtml(activeCand.organisation || '')}
                </div>
                <div style="font-size: 12px; font-weight: 600; color: #92400E; margin-top: 4px;">
                  🏷️ ${this.escapeHtml(activeCand.discipline_name || '')}
                </div>
              </div>
            </div>

            <div style="display: flex; gap: 10px;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="CandidateModal.open(${activeCand.id})">
                View Dossier ℹ️
              </button>
              <a href="${activeCand.nomination_form_url || '#'}" target="_blank" rel="noopener noreferrer" class="btn btn-onedrive btn-sm">
                View Nomination Form ↗
              </a>
            </div>
          </div>

          <!-- New Comment / Due Diligence Submission Form -->
          <div class="card" style="border: 2px solid var(--asm-navy-light); margin-bottom: 28px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--asm-navy); margin-bottom: 14px;">
              ✍️ Submit Assessment / Due Diligence Opinion
            </h3>

            <div id="ddAlertContainer"></div>

            <form id="dueDiligenceForm" onsubmit="App.handleDueDiligenceSubmit(event, ${activeCand.id})">
              <div class="form-group">
                <label class="form-label" for="ddCategory">Assessment Category</label>
                <select id="ddCategory" class="form-select" required>
                  ${categories.map(cat => `
                    <option value="${this.escapeHtml(cat.category_name)}">${this.escapeHtml(cat.category_name)}</option>
                  `).join('')}
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="ddComment">User Opinion / Comments</label>
                <textarea id="ddComment" class="form-control" rows="5" required
                          placeholder="Please provide your assessment, comments, observations or other relevant information regarding this candidate."></textarea>
              </div>

              <div class="form-group">
                <label class="form-label" for="ddFile">Attach Supporting Document (Optional)</label>
                <input type="file" id="ddFile" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" />
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                  Supported: PDF, Word (DOC/DOCX), Excel (XLS/XLSX), PPT/PPTX, PNG/JPG (Max 15MB). Executable files are prohibited.
                </div>
              </div>

              <div style="text-align: right;">
                <button type="submit" class="btn btn-gold btn-lg">
                  Submit Due Diligence Information ➔
                </button>
              </div>
            </form>
          </div>

          <!-- Existing Submissions Stream (Separately Identifiable) -->
          <div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--asm-navy); margin-bottom: 16px;">
              📋 Recorded Due Diligence Submissions (${submissions.length})
            </h3>

            ${submissions.length === 0 ? `
              <div style="padding: 24px; text-align: center; color: var(--text-muted); background: #F8FAFC; border-radius: 6px; border: 1px dashed var(--border-color);">
                No due diligence submissions recorded for this candidate yet.
              </div>
            ` : submissions.map(sub => `
              <div class="card" style="margin-bottom: 16px; border-left: 4px solid var(--asm-gold);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                  <div>
                    <span style="font-weight: 700; color: var(--asm-navy); font-size: 14px;">
                      👤 ${this.escapeHtml(sub.author_name || 'Evaluator')}
                    </span>
                    <span class="badge-role ${sub.author_role}" style="margin-left: 8px;">${sub.author_role}</span>
                  </div>
                  <div style="font-size: 12px; color: var(--text-light);">
                    ${sub.submitted_at}
                  </div>
                </div>

                <div style="margin-bottom: 8px;">
                  <span class="badge-role reviewer" style="background: #E2E8F0; color: #1E293B; font-weight: 600;">
                    Category: ${this.escapeHtml(sub.category)}
                  </span>
                </div>

                <div style="font-size: 14px; color: var(--text-main); line-height: 1.6; white-space: pre-line; background: #F8FAFC; padding: 12px; border-radius: 4px;">
                  ${this.escapeHtml(sub.comment)}
                </div>

                ${sub.supporting_documents && sub.supporting_documents.length > 0 ? `
                  <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--border-color);">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
                      📎 Supporting Documents:
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                      ${sub.supporting_documents.map(doc => `
                        <a href="/api/files/${doc.id}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                          📄 ${this.escapeHtml(doc.file_name)} (${Math.round(doc.file_size / 1024)} KB) ⬇️
                        </a>
                      `).join('')}
                    </div>
                  </div>
                ` : ''}
              </div>
            `).join('')}
          </div>
        ` : ''}
      </div>
    `;
  },

  async handleDueDiligenceSubmit(e, candidateId) {
    e.preventDefault();
    const alertBox = document.getElementById('ddAlertContainer');
    alertBox.innerHTML = '';

    const category = document.getElementById('ddCategory').value;
    const comment = document.getElementById('ddComment').value.trim();
    const fileInput = document.getElementById('ddFile');

    const res = await API.post(`/api/candidates/${candidateId}/due-diligence`, { category, comment });
    if (!res || !res.success) {
      alertBox.innerHTML = `<div class="alert alert-danger">⚠️ ${res?.error || 'Failed to submit.'}</div>`;
      return;
    }

    const submissionId = res.submission_id;

    // Check if user attached a supporting document
    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const formData = new FormData();
      formData.append('file', file);

      const uploadRes = await API.upload(`/api/due-diligence/${submissionId}/upload`, formData);
      if (!uploadRes || !uploadRes.success) {
        alert('Due diligence comment was saved, but file upload failed: ' + (uploadRes?.error || 'Unknown error'));
      }
    }

    alert('Your due diligence information has been successfully submitted.');
    this.renderDueDiligence(candidateId);
  },

  // =========================================================================
  // VIEW: ADMINISTRATION PORTAL
  // =========================================================================
  async renderAdminDashboard() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading administration portal...</p></div>`;

    const statsRes = await API.get('/api/admin/dashboard-stats');
    const stats = statsRes?.stats || {};

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">🏛️ Administration Dashboard</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              System overview, voting completion metrics, and management tools.
            </p>
          </div>
          <div>
            <a href="#/admin/results" class="btn btn-gold btn-sm">View Ranking Results ➔</a>
          </div>
        </div>

        <!-- Metric Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 20px 0;">
          <div style="background: #F8FAFC; padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Exercise Status</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--success); margin: 6px 0;">
              ${(stats.exercise_status || 'OPEN').toUpperCase()}
            </div>
          </div>

          <div style="background: #F8FAFC; padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Registered Voters</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--asm-navy); margin: 6px 0;">
              ${stats.registered_voters || 0}
            </div>
          </div>

          <div style="background: #F8FAFC; padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Submitted Rankings</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--asm-navy); margin: 6px 0;">
              ${stats.submitted_rankings || 0} / ${stats.registered_voters || 0}
            </div>
            <div style="font-size: 13px; font-weight: 700; color: var(--success);">${stats.completion_rate || 0}% Completion Rate</div>
          </div>

          <div style="background: #F8FAFC; padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Due Diligence Submissions</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--asm-navy); margin: 6px 0;">
              ${stats.due_diligence_submissions || 0}
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">${stats.supporting_documents || 0} Supporting Files</div>
          </div>
        </div>

        <!-- Quick Administration Links -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 10px;">
          <a href="#/admin/results" class="card" style="text-decoration: none; padding: 16px; margin: 0;">
            <h3 style="color: var(--asm-navy); font-size: 16px; font-weight: 700; margin-bottom: 4px;">📊 Ranking Results Matrix</h3>
            <p style="color: var(--text-muted); font-size: 13px;">View candidate ranks by voter, average rankings, and Rank 1 frequencies.</p>
          </a>

          <a href="#/admin/users" class="card" style="text-decoration: none; padding: 16px; margin: 0;">
            <h3 style="color: var(--asm-navy); font-size: 16px; font-weight: 700; margin-bottom: 4px;">👥 User & Reopening Management</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Manage authorized voters, check submission status, and reopen rankings.</p>
          </a>

          <a href="#/admin/reports" class="card" style="text-decoration: none; padding: 16px; margin: 0;">
            <h3 style="color: var(--asm-navy); font-size: 16px; font-weight: 700; margin-bottom: 4px;">📑 Reports & CSV Export</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Download consolidated CSV assessment and due diligence spreadsheets.</p>
          </a>

          <a href="#/admin/audit" class="card" style="text-decoration: none; padding: 16px; margin: 0;">
            <h3 style="color: var(--asm-navy); font-size: 16px; font-weight: 700; margin-bottom: 4px;">🛡️ Audit Trail Log</h3>
            <p style="color: var(--text-muted); font-size: 13px;">View comprehensive immutable logs of user logins, submissions, and changes.</p>
          </a>
        </div>
      </div>
    `;
  },

  async renderAdminResults() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Calculating ranking matrices...</p></div>`;

    const res = await API.get('/api/admin/ranking-results');
    const results = res?.results;

    if (!results) {
      main.innerHTML = `<div class="alert alert-danger">Unable to load ranking results.</div>`;
      return;
    }

    const voters = results.voters || [];

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">📊 Ranking Results & Statistical Matrix</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              ${this.escapeHtml(results.exercise?.exercise_name || '')} • ${results.total_voters_submitted} Final Voter Ballots Counted
            </p>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="/api/admin/reports/ranking" target="_blank" class="btn btn-secondary btn-sm">
              📥 Export CSV Matrix
            </a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
              🖨️ Print Report
            </button>
          </div>
        </div>

        <div class="alert alert-info" style="font-size: 13px;">
          <strong>Tie-Breaker Rule:</strong> ${this.escapeHtml(results.exercise?.tie_breaker_method || 'Rank 1 Count')}. 
          Lower average rank indicates higher overall consensus preference.
        </div>

        ${results.disciplines.map(disc => `
          <div style="margin-bottom: 32px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <h2 style="font-size: 17px; font-weight: 700; color: var(--asm-navy);">
                ${this.escapeHtml(disc.discipline_name)} (${disc.candidate_count} Candidates)
              </h2>
            </div>

            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th style="width: 70px; text-align: center;">Pos.</th>
                    <th>Candidate Name</th>
                    <th>Organisation</th>
                    ${voters.map(v => `
                      <th style="text-align: center; font-size: 12px;" title="${this.escapeHtml(v.full_name)}">
                        ${this.escapeHtml(v.full_name.split(' ')[0])}
                      </th>
                    `).join('')}
                    <th style="text-align: center; background: #F1F5F9;">Avg Rank</th>
                    <th style="text-align: center; background: #F1F5F9;">Rank 1</th>
                  </tr>
                </thead>
                <tbody>
                  ${disc.candidates.map(cand => `
                    <tr>
                      <td style="text-align: center; font-weight: 800; color: ${cand.position === 1 ? 'var(--asm-gold)' : 'var(--asm-navy)'}; font-size: 16px;">
                        ${cand.position} ${cand.is_tied ? '<span style="font-size: 10px; color: var(--danger);">(Tied)</span>' : ''}
                      </td>
                      <td>
                        <a href="javascript:void(0)" class="candidate-name-link" onclick="CandidateModal.open(${cand.candidate_id})">
                          ${this.escapeHtml(cand.candidate_name)}
                        </a>
                      </td>
                      <td style="font-size: 13px; color: var(--text-muted);">
                        ${this.escapeHtml(cand.organisation || '')}
                      </td>
                      ${voters.map(v => {
                        const r = cand.voter_rankings[v.id];
                        return `
                          <td style="text-align: center; font-weight: 700; color: ${r === 1 ? 'var(--asm-gold)' : 'var(--text-main)'};">
                            ${r !== undefined ? r : '-'}
                          </td>
                        `;
                      }).join('')}
                      <td style="text-align: center; font-weight: 800; font-size: 15px; background: #F8FAFC; color: var(--asm-navy);">
                        ${cand.average_rank !== null ? cand.average_rank : '-'}
                      </td>
                      <td style="text-align: center; font-weight: 700; background: #F8FAFC;">
                        ${cand.rank1_count}
                      </td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  },

  async renderAdminUsers() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading users & submissions...</p></div>`;

    const res = await API.get('/api/admin/users');
    const users = res?.users || [];

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">👥 User & Ranking Submission Management</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              Manage voter accounts and unlock/reopen rankings if authorized.
            </p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Name / Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Ranking Submission</th>
                <th>Submitted On</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              ${users.map(u => `
                <tr>
                  <td>
                    <div style="font-weight: 700; color: var(--asm-navy);">${this.escapeHtml(u.full_name)}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">${this.escapeHtml(u.email)}</div>
                  </td>
                  <td>
                    <span class="badge-role ${u.role}">${u.role.replace('_', ' ')}</span>
                  </td>
                  <td>
                    ${u.is_active ? 'Active' : '<span style="color: red;">Inactive</span>'}
                  </td>
                  <td>
                    ${u.ranking_status === 'submitted' 
                      ? '<span class="badge-role" style="background: var(--success); color: white;">Submitted ✓</span>' 
                      : u.ranking_status === 'reopened'
                      ? '<span class="badge-role" style="background: var(--warning); color: white;">Reopened 🔄</span>'
                      : '<span style="color: var(--text-muted); font-size: 13px;">Not Finalized</span>'
                    }
                  </td>
                  <td style="font-size: 13px; color: var(--text-muted);">
                    ${u.ranking_submitted_at || '-'}
                  </td>
                  <td style="text-align: right;">
                    ${u.ranking_status === 'submitted' ? `
                      <button type="button" class="btn btn-secondary btn-sm" onclick="App.promptReopenRanking(${u.submission_id}, '${this.escapeHtml(u.full_name)}')">
                        🔄 Reopen Ranking
                      </button>
                    ` : ''}
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;
  },

  async promptReopenRanking(submissionId, voterName) {
    const reason = window.prompt(
      `Enter authorization reason to reopen rankings for ${voterName}:`,
      "Voter requested modification to ranking ballot."
    );

    if (reason === null) return; // Cancelled
    if (!reason.trim()) {
      alert("A valid justification reason is required to reopen a submission.");
      return;
    }

    const res = await API.post('/api/admin/reopen-ranking', {
      submission_id: submissionId,
      reason: reason.trim()
    });

    if (!res || !res.success) {
      alert('Failed to reopen ranking: ' + (res?.error || 'Unknown error'));
      return;
    }

    alert(`Ranking for ${voterName} has been reopened and logged in the audit trail.`);
    this.renderAdminUsers();
  },

  async renderAdminAudit() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `<div style="text-align: center; padding: 60px;"><p>Loading audit trail...</p></div>`;

    const res = await API.get('/api/admin/audit-logs');
    const logs = res?.logs || [];

    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div>
            <h1 class="card-title" style="font-size: 22px;">🛡️ System Audit Trail</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
              Immutable chronological record of logins, candidate rankings, submissions, and administration events.
            </p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 150px;">Timestamp</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Description</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              ${logs.map(log => `
                <tr>
                  <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">${log.created_at}</td>
                  <td>
                    <div style="font-weight: 600; font-size: 13px;">${this.escapeHtml(log.user_name || 'System / Anonymous')}</div>
                    <div style="font-size: 11px; color: var(--text-light);">${this.escapeHtml(log.user_role || '')}</div>
                  </td>
                  <td>
                    <span class="badge-role reviewer" style="font-size: 11px; background: #E2E8F0; color: #0F172A;">
                      ${this.escapeHtml(log.action)}
                    </span>
                  </td>
                  <td style="font-size: 13px; color: var(--text-main);">${this.escapeHtml(log.description || '')}</td>
                  <td style="font-size: 12px; color: var(--text-muted);">${this.escapeHtml(log.ip_address || '-')}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;
  },

  async renderAdminReports() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <h1 class="card-title" style="font-size: 22px;">📑 Official Assessment Reports & Exports</h1>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 10px;">
          <div class="card" style="border: 2px solid var(--border-color);">
            <h3 style="color: var(--asm-navy); font-size: 18px; font-weight: 700; margin-bottom: 8px;">
              📊 Consolidated Ranking Report
            </h3>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
              Exports a comprehensive candidate ranking spreadsheet with individual voter rankings, average scores, Rank 1 tallies, and tie positions across all 8 disciplines.
            </p>
            <a href="/api/admin/reports/ranking" target="_blank" class="btn btn-gold btn-lg" style="width: 100%;">
              📥 Download Ranking CSV Report
            </a>
          </div>

          <div class="card" style="border: 2px solid var(--border-color);">
            <h3 style="color: var(--asm-navy); font-size: 18px; font-weight: 700; margin-bottom: 8px;">
              📋 Consolidated Due Diligence Report
            </h3>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
              Exports all candidate assessments, categories, evaluator opinions, and references to supporting documents.
            </p>
            <a href="/api/admin/reports/due-diligence" target="_blank" class="btn btn-primary btn-lg" style="width: 100%;">
              📥 Download Due Diligence CSV Report
            </a>
          </div>
        </div>
      </div>
    `;
  },

  // =========================================================================
  // VIEW: INFORMATION & GUIDELINES
  // =========================================================================
  async renderInformation() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `
      <div class="card">
        <div class="card-header">
          <h1 class="card-title" style="font-size: 22px;">📖 System Guidelines & Statutory Notices</h1>
        </div>

        <div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px; margin-top: 10px;">
          <div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
              <button type="button" class="btn btn-secondary" style="text-align: left; justify-content: flex-start;" onclick="App.loadInfoDoc('guidelines')">Guidelines</button>
              <button type="button" class="btn btn-secondary" style="text-align: left; justify-content: flex-start;" onclick="App.loadInfoDoc('criteria')">Evaluation Criteria</button>
              <button type="button" class="btn btn-secondary" style="text-align: left; justify-content: flex-start;" onclick="App.loadInfoDoc('faq')">FAQ</button>
              <button type="button" class="btn btn-secondary" style="text-align: left; justify-content: flex-start;" onclick="App.loadInfoDoc('confidentiality')">Confidentiality Notice</button>
            </div>
          </div>

          <div id="infoDocContent" style="line-height: 1.7; font-size: 14px; color: var(--text-main); background: #F8FAFC; padding: 24px; border-radius: 8px; border: 1px solid var(--border-color);">
            <!-- Populated via loadInfoDoc -->
          </div>
        </div>
      </div>
    `;

    this.loadInfoDoc('guidelines');
  },

  async loadInfoDoc(slug) {
    const container = document.getElementById('infoDocContent');
    if (!container) return;
    container.innerHTML = '<p>Loading document content...</p>';

    const res = await API.get(`/api/information/${slug}`);
    if (res && res.page) {
      container.innerHTML = `
        <h2 style="color: var(--asm-navy); font-size: 20px; font-weight: 700; margin-bottom: 14px;">${this.escapeHtml(res.page.title)}</h2>
        <div style="white-space: pre-line;">${this.escapeHtml(res.page.content)}</div>
      `;
    }
  },

  renderForbidden() {
    const main = document.getElementById('mainContent');
    main.innerHTML = `
      <div class="card" style="text-align: center; padding: 48px 20px;">
        <h2 style="color: var(--danger); font-size: 22px;">🚫 Access Restricted</h2>
        <p style="margin-top: 10px; color: var(--text-muted);">You do not have the required administrative credentials to access this portal.</p>
        <div style="margin-top: 20px;">
          <a href="#/dashboard" class="btn btn-primary">Return to Dashboard</a>
        </div>
      </div>
    `;
  },

  escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
};

// Start application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  App.init();
});
