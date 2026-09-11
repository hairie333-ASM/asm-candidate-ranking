/**
 * Reusable Candidate Information Pop-up Modal Component
 * Academy of Sciences Malaysia (ASM)
 */

const CandidateModal = {
  activeCandidateId: null,
  previousActiveElement: null,

  init() {
    // Listen for Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen()) {
        this.close();
      }
    });

    // Close when clicking outside dialog
    document.addEventListener('click', (e) => {
      const backdrop = document.getElementById('candidateModalBackdrop');
      if (backdrop && e.target === backdrop) {
        this.close();
      }
    });
  },

  isOpen() {
    const backdrop = document.getElementById('candidateModalBackdrop');
    return backdrop && !backdrop.classList.contains('hidden');
  },

  /**
   * Opens the modal for candidateId.
   * Performs an authenticated on-demand fetch to keep initial pages lightweight.
   * Preserves current parent page state (ranking selections remain untouched).
   */
  async open(candidateId) {
    this.activeCandidateId = candidateId;
    this.previousActiveElement = document.activeElement;

    const backdrop = document.getElementById('candidateModalBackdrop');
    if (!backdrop) return;

    // Show modal with loading state
    backdrop.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    const modalBody = document.getElementById('candidateModalBody');
    const modalFooter = document.getElementById('candidateModalFooter');

    modalBody.innerHTML = `
      <div style="text-align: center; padding: 48px 20px;">
        <div style="display: inline-block; width: 36px; height: 36px; border: 3px solid #E2E8F0; border-top-color: #0B2545; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
        <p style="margin-top: 14px; color: #64748B; font-weight: 500;">Retrieving candidate nomination dossier...</p>
      </div>
      <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
    `;

    modalFooter.innerHTML = `
      <button type="button" class="btn btn-secondary" onclick="CandidateModal.close()">Close</button>
    `;

    // Fetch candidate details
    const res = await API.get(`/api/candidates/${candidateId}`);
    if (!res || !res.success || !res.candidate) {
      modalBody.innerHTML = `
        <div class="alert alert-danger">
          <strong>Error:</strong> Unable to load candidate information. ${res?.error || 'Please try again.'}
        </div>
      `;
      return;
    }

    const c = res.candidate;
    this.render(c);
  },

  render(c) {
    const modalBody = document.getElementById('candidateModalBody');
    const modalFooter = document.getElementById('candidateModalFooter');

    const photoHtml = c.photo_url
      ? `<img src="${c.photo_url}" alt="Photograph of ${this.escapeHtml(c.candidate_name)}" class="candidate-photo-img" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\' viewBox=\\'0 0 24 24\\' fill=\\'%2394A3B8\\'><path d=\\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\\'/></svg>';">`
      : `<div class="candidate-photo-placeholder">👤</div>`;

    modalBody.innerHTML = `
      <!-- Candidate Profile Top -->
      <div class="candidate-profile-top">
        <div class="candidate-photo-wrapper">
          ${photoHtml}
        </div>
        <div class="candidate-meta-details">
          <h3 id="modal-candidate-name">${this.escapeHtml(c.candidate_name)}</h3>
          <div class="candidate-meta-title">${this.escapeHtml(c.candidate_title || '')}${c.position ? ' • ' + this.escapeHtml(c.position) : ''}</div>
          <div class="candidate-meta-org">🏛️ ${this.escapeHtml(c.organisation || 'Academy of Sciences Malaysia')}</div>
          <span class="candidate-meta-discipline">🏷️ ${this.escapeHtml(c.discipline_name || '')}</span>
        </div>
      </div>

      <!-- Basis of Recommendation -->
      <div class="dossier-section">
        <div class="dossier-section-title">Basis of Recommendation</div>
        <div class="dossier-section-content" style="background: #F8FAFC; padding: 14px 16px; border-radius: 6px; border-left: 4px solid var(--asm-gold);">
          ${this.escapeHtml(c.basis_of_recommendation || 'No recommendation text recorded.')}
        </div>
      </div>

      <!-- Area of Expertise -->
      <div class="dossier-section">
        <div class="dossier-section-title">Area of Expertise</div>
        <div class="dossier-section-content" style="font-weight: 500;">
          🔬 ${this.escapeHtml(c.area_of_expertise || 'General Scientific Research')}
        </div>
      </div>

      <!-- Qualification / Professional Membership -->
      <div class="dossier-section">
        <div class="dossier-section-title">Qualification / Professional Membership</div>
        <div class="dossier-section-content" style="background: #FFFFFF; border: 1px solid var(--border-color); padding: 14px 16px; border-radius: 6px;">
          <div style="margin-bottom: 10px;">
            <strong style="color: var(--asm-navy); display: block; margin-bottom: 4px;">🎓 Academic Qualifications:</strong>
            ${this.formatList(c.qualifications)}
          </div>
          <div>
            <strong style="color: var(--asm-navy); display: block; margin-bottom: 4px;">🎖️ Professional Memberships & Honors:</strong>
            ${this.formatList(c.professional_memberships)}
          </div>
        </div>
      </div>
    `;

    // Action buttons in modal footer:
    // 1. Close
    // 2. View Full Nomination Form (OneDrive URL in new tab)
    // 3. Go to Due Diligence
    const oneDriveUrl = c.nomination_form_url || '#';
    modalFooter.innerHTML = `
      <button type="button" class="btn btn-secondary" onclick="CandidateModal.close()">
        ✕ Close
      </button>

      <a href="${oneDriveUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-onedrive" title="Open complete nomination dossier in OneDrive">
        📄 View Full Nomination Form ↗
      </a>

      <button type="button" class="btn btn-primary" onclick="CandidateModal.goToDueDiligence(${c.id})">
        📋 Go to Due Diligence ➔
      </button>
    `;

    // Focus close button for accessibility
    const closeBtn = document.querySelector('.modal-close-btn');
    if (closeBtn) closeBtn.focus();
  },

  close() {
    const backdrop = document.getElementById('candidateModalBackdrop');
    if (backdrop) {
      backdrop.classList.add('hidden');
    }
    document.body.style.overflow = ''; // Restore background scrolling
    this.activeCandidateId = null;

    if (this.previousActiveElement && typeof this.previousActiveElement.focus === 'function') {
      this.previousActiveElement.focus();
    }
  },

  goToDueDiligence(candidateId) {
    this.close();
    window.location.hash = `#/due-diligence?candidate_id=${candidateId}`;
  },

  formatList(text) {
    if (!text || !text.trim()) return '<span style="color: #94A3B8;">None recorded</span>';
    const lines = text.split('\n').filter(l => l.trim().length > 0);
    return lines.map(line => `<div style="margin: 2px 0;">${this.escapeHtml(line)}</div>`).join('');
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

// Initialize modal listeners on DOM load
document.addEventListener('DOMContentLoaded', () => {
  CandidateModal.init();
});
