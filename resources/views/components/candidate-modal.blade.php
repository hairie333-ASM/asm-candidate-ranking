{{-- Candidate Modal Component --}}
<div x-data="candidateModal()" 
     x-show="isOpen" 
     x-cloak 
     @open-candidate-modal.window="openModal($event.detail)"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 overflow-y-auto" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="candidate-modal-title">

    {{-- Backdrop --}}
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
         @click="closeModal()"></div>

    {{-- Modal Dialog --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200"
             @click.stop>
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 px-6 py-4 flex items-center justify-between text-white">
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-200 border border-teal-400/30" 
                          x-text="candidate.discipline_name || (loading ? 'Loading...' : 'Candidate Details')"></span>
                    <h3 class="mt-1 text-lg font-bold text-white tracking-wide" 
                        id="candidate-modal-title" 
                        x-text="candidate.candidate_name || (loading ? 'Loading Candidate...' : 'Candidate Profile')"></h3>
                </div>
                <button type="button" 
                        @click="closeModal()" 
                        class="rounded-lg p-1.5 text-slate-300 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-400 cursor-pointer"
                        aria-label="Close modal">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="py-16 text-center space-y-3">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-teal-600 border-t-transparent"></div>
                <p class="text-sm font-semibold text-slate-700">Loading candidate information...</p>
                <p class="text-xs text-slate-400">Fetching candidate credentials & dossier</p>
            </div>

            {{-- Error State --}}
            <div x-show="!loading && errorMessage" class="py-12 px-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800" x-text="errorMessage"></p>
                <button type="button" @click="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                    Close
                </button>
            </div>

            {{-- Body Content: Exact 9 fields in order --}}
            <div x-show="!loading && !errorMessage" class="px-6 py-6 max-h-[75vh] overflow-y-auto space-y-5">
                {{-- Top Block: 1. PICTURE, 2. FULL NAME, 3. TITLE / DESIGNATION, 4. NOMINATED DISCIPLINE --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 pb-5 border-b border-slate-100">
                    {{-- 1. PICTURE --}}
                    <template x-if="candidate.photo_url">
                        <img :src="candidate.photo_url" 
                             :alt="candidate.candidate_name || 'Candidate'" 
                             class="h-28 w-28 rounded-2xl object-cover shadow-sm border-2 border-teal-600 bg-slate-100 flex-shrink-0">
                    </template>
                    <template x-if="!candidate.photo_url">
                        <div class="h-28 w-28 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 flex flex-col items-center justify-center border-2 border-slate-300 shadow-sm flex-shrink-0">
                            <svg class="w-10 h-10 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">No Photo</span>
                        </div>
                    </template>

                    <div class="text-center sm:text-left space-y-1.5 flex-1 min-w-0">
                        {{-- 2. FULL NAME --}}
                        <h2 class="text-xl font-bold text-slate-900 leading-snug" x-text="candidate.candidate_name || 'Not provided'"></h2>

                        {{-- 3. TITLE / DESIGNATION --}}
                        <p class="text-xs font-semibold text-teal-700" x-text="candidate.candidate_title || 'Not provided'"></p>
                        <p class="text-xs text-slate-500 font-medium" x-show="candidate.organisation" x-text="candidate.organisation"></p>

                        {{-- 4. NOMINATED DISCIPLINE --}}
                        <div class="pt-1">
                            <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 border border-teal-200 font-semibold">
                                <svg class="w-3.5 h-3.5 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span x-text="candidate.discipline_name || 'Not provided'"></span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 5. AFFILIATION TO ASM --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Affiliation to ASM</h4>
                    <p class="text-sm font-semibold text-slate-800 bg-slate-50 p-3 rounded-xl border border-slate-100" 
                       x-text="candidate.affiliation_to_asm || 'Not provided'"></p>
                </div>

                {{-- 6. ONEDRIVE DOSSIER LINK (URL) --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">OneDrive Dossier Link</h4>
                    <template x-if="candidate.nomination_form_url">
                        <a :href="candidate.nomination_form_url" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center px-4 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors group">
                            <svg class="w-4 h-4 mr-2 text-blue-200 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            View OneDrive Dossier
                            <svg class="w-3.5 h-3.5 ml-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </template>
                    <template x-if="!candidate.nomination_form_url">
                        <span class="inline-flex items-center px-3 py-2 text-xs font-medium text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                            Not provided
                        </span>
                    </template>
                </div>

                {{-- 7. AREAS OF EXPERTISE --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Areas of Expertise</h4>
                    <div class="text-sm text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed font-medium" 
                         x-text="candidate.area_of_expertise || 'Not provided'"></div>
                </div>

                {{-- 8. QUALIFICATIONS / PROFESSIONAL MEMBERSHIPS --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Qualifications / Professional Memberships</h4>
                    <div class="text-sm text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed" 
                         x-text="candidate.qualifications_professional_memberships || 'Not provided'"></div>
                </div>

                {{-- 9. BASIS OF RECOMMENDATION --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Basis of Recommendation</h4>
                    <div class="text-sm text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed" 
                         x-text="candidate.basis_of_recommendation || 'Not provided'"></div>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100">
                <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-2">
                    {{-- OneDrive External Link --}}
                    <template x-if="!loading && candidate.nomination_form_url">
                        <a :href="candidate.nomination_form_url" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            View OneDrive Dossier
                        </a>
                    </template>

                    {{-- Due Diligence Action --}}
                    <template x-if="!loading && candidate.id">
                        <a :href="'/due-diligence/candidate/' + candidate.id" 
                           class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-300 rounded-xl transition-colors">
                            <svg class="w-4 h-4 mr-1.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Submit Due Diligence
                        </a>
                    </template>
                </div>

                <button type="button" 
                        @click="closeModal()" 
                        class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl shadow-2xs transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var candidateModalInstance = null;
    window._pendingCandidateModalId = null;

    function candidateModal() {
        return {
            isOpen: false,
            loading: false,
            errorMessage: '',
            candidate: {},
            init() {
                candidateModalInstance = this;
                if (window._pendingCandidateModalId) {
                    var pendingId = window._pendingCandidateModalId;
                    window._pendingCandidateModalId = null;
                    this.openModal({ candidateId: pendingId });
                }
            },
            openModal(data) {
                var self = this;
                self.isOpen = true;
                self.errorMessage = '';

                if (data && data.candidate) {
                    self.candidate = data.candidate;
                    self.loading = false;
                    return;
                }

                var candidateId = (data && data.candidateId) ? data.candidateId : data;
                if (!candidateId) return;

                self.loading = true;
                self.candidate = {};

                fetch('/api/candidates/' + candidateId, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': Failed to load candidate details');
                    }
                    return response.json();
                })
                .then(function(result) {
                    self.candidate = result;
                    self.loading = false;
                    if (candidateModalInstance) {
                        candidateModalInstance.candidate = result;
                        candidateModalInstance.loading = false;
                    }
                })
                .catch(function(error) {
                    console.error('Candidate modal fetch error:', error);
                    self.errorMessage = 'Unable to load candidate details. Please try again.';
                    self.loading = false;
                    if (candidateModalInstance) {
                        candidateModalInstance.errorMessage = 'Unable to load candidate details. Please try again.';
                        candidateModalInstance.loading = false;
                    }
                });
            },
            closeModal() {
                this.isOpen = false;
                this.loading = false;
            }
        };
    }

    // Universal global helper callable from onclick="..." anywhere
    window.openCandidateModal = function(candidateId) {
        if (!candidateId) return;
        if (candidateModalInstance) {
            candidateModalInstance.openModal({ candidateId: candidateId });
        } else {
            window._pendingCandidateModalId = candidateId;
            window.dispatchEvent(new CustomEvent('open-candidate-modal', {
                detail: { candidateId: candidateId },
                bubbles: true,
                composed: true
            }));
        }
    };

    if (window.Alpine) {
        Alpine.data('candidateModal', candidateModal);
    } else {
        document.addEventListener('alpine:init', function() {
            Alpine.data('candidateModal', candidateModal);
        });
    }
</script>
