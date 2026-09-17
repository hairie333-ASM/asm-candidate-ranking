@extends('layouts.app', ['title' => 'Ranking Exercise - ' . ($discipline->discipline_name ?? 'Discipline')])

@section('content')
<div x-data="rankingBoard()" class="space-y-6 max-w-5xl mx-auto py-4">

    <!-- Header Banner -->
    <div class="bg-gradient-to-br from-[#302556] via-[#241c42] to-[#008442]/80 rounded-2xl p-6 sm:p-8 text-white shadow-lg border border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#008442]/40 text-emerald-200 border border-emerald-400/30">
                    Confidential Ranking Exercise
                </span>
                <span class="text-xs text-slate-300 font-mono">{{ $discipline->code }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">
                {{ $discipline->discipline_name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-200 mt-1">
                Assign a unique ordinal preference from <strong>1 (Highest)</strong> to <strong>{{ $totalCandidates }} (Lowest)</strong> for every candidate.
            </p>
        </div>

        <!-- Real-Time Progress Widget -->
        <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl border border-white/10 text-right min-w-[200px]">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-teal-300">Live Ranking Progress</span>
            <div class="flex items-baseline justify-end space-x-1.5 mt-0.5">
                <span class="text-2xl font-black text-white" x-text="rankedCount + ' / ' + total">{{ count(array_filter($existingRanks)) }} / {{ $totalCandidates }}</span>
                <span class="text-xs font-bold text-teal-300" x-show="isCompleteAndValid" x-cloak>✓ Complete</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-700 h-2 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-teal-400 transition-all duration-300 rounded-full" 
                     :style="'width: ' + (total > 0 ? (rankedCount / total * 100) : 0) + '%'"></div>
            </div>
        </div>
    </div>

    <!-- Instructions Notice -->
    <div class="bg-teal-50/70 border border-teal-200 p-4 rounded-xl flex items-start gap-3 text-xs text-teal-950">
        <svg class="w-5 h-5 text-teal-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="space-y-1">
            <p class="font-bold">Real-Time Duplicate Prevention Active:</p>
            <p>
                Selecting a ranking number instantly disables that number across all other candidates. Click on any candidate's name to view their profile dossier or open the complete Microsoft OneDrive nomination form. Current selections are preserved safely in memory.
            </p>
        </div>
    </div>

    <!-- Real-Time Validation Alert Banner -->
    <div x-show="!isCompleteAndValid && rankedCount > 0" x-cloak class="rounded-xl bg-amber-50 p-4 border border-amber-200 text-amber-900 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span x-text="validationStatusMessage"></span>
        </div>
        <p class="text-[11px] text-amber-800" x-show="missingRanksText">
            Missing ranking numbers: <span class="font-bold font-mono" x-text="missingRanksText"></span>
        </p>
    </div>

    <!-- Form for Backend Submission -->
    <form id="ranking-form" action="{{ route('ranking.submit') }}" method="POST" @submit.prevent="openConfirmModal()">
        @csrf

        <!-- Candidates Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Candidates in {{ $discipline->discipline_name }}</h2>
                    <p class="text-xs text-slate-500">Total {{ $totalCandidates }} candidate(s) to be ranked.</p>
                </div>
                <button type="button" 
                        @click="resetRankings()" 
                        class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition-colors">
                    Clear All Selections
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">No.</th>
                            <th class="py-3 px-4 w-16">Photo</th>
                            <th class="py-3 px-6">Candidate Information</th>
                            <th class="py-3 px-4">Area of Expertise</th>
                            <th class="py-3 px-6 w-48 text-right">Assigned Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($candidates as $index => $candidate)
                            <tr class="hover:bg-teal-50/30 transition-colors"
                                :class="{'bg-teal-50/20': selections[{{ $candidate->id }}]}">
                                
                                {{-- Index --}}
                                <td class="py-4 px-4 text-center font-mono font-medium text-slate-400">
                                    {{ $index + 1 }}
                                </td>

                                {{-- Photo Thumbnail --}}
                                <td class="py-4 px-4">
                                    @if($candidate->photo_url)
                                        <img src="{{ $candidate->photo_url }}" 
                                             alt="{{ $candidate->candidate_name }}" 
                                             class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm bg-slate-100 cursor-pointer hover:opacity-90"
                                             onclick="openCandidateModal({{ $candidate->id }})"
                                             @click="openCandidateModal({{ $candidate->id }})">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-teal-800 text-teal-200 flex items-center justify-center font-bold text-sm cursor-pointer shadow-xs hover:bg-teal-700 transition-colors"
                                             onclick="openCandidateModal({{ $candidate->id }})"
                                             @click="openCandidateModal({{ $candidate->id }})">
                                            {{ substr($candidate->candidate_name, 0, 2) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Name & Organization (Clickable) --}}
                                <td class="py-4 px-6">
                                    <button type="button" 
                                            onclick="openCandidateModal({{ $candidate->id }})"
                                            @click="openCandidateModal({{ $candidate->id }})"
                                            class="text-left group focus:outline-none cursor-pointer">
                                        <span class="block font-bold text-slate-900 group-hover:text-teal-700 transition-colors underline decoration-teal-500/40 underline-offset-2">
                                            {{ $candidate->candidate_name }}
                                        </span>
                                        <span class="block text-xs font-medium text-teal-800">
                                            {{ $candidate->candidate_title ?? 'Candidate' }}
                                        </span>
                                        <span class="block text-xs text-slate-500">
                                            {{ $candidate->organisation }}
                                        </span>
                                    </button>
                                </td>

                                {{-- Area of Expertise Tag --}}
                                <td class="py-4 px-4">
                                    <span class="inline-block text-xs text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200 line-clamp-2">
                                        {{ $candidate->area_of_expertise ?? $candidate->sub_discipline ?? 'General' }}
                                    </span>
                                </td>

                                {{-- Rank Dropdown with Real-Time Duplicate Prevention --}}
                                <td class="py-4 px-6 text-right">
                                    <div class="inline-block w-40 text-left">
                                        {{-- Hidden input for form submission --}}
                                        <input type="hidden" 
                                               name="rankings[{{ $candidate->id }}]" 
                                               :value="selections[{{ $candidate->id }}] || ''">

                                        {{-- Reactive Dropdown --}}
                                        <select x-model="selections[{{ $candidate->id }}]" 
                                                @change="onRankChange({{ $candidate->id }}, $event.target.value)"
                                                class="w-full text-xs font-bold py-2 px-3 border rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2"
                                                :class="selections[{{ $candidate->id }}] 
                                                    ? 'border-teal-500 bg-teal-50 text-teal-900 focus:ring-teal-400' 
                                                    : 'border-slate-300 bg-white text-slate-700 focus:ring-teal-500'">
                                            <option value="">Select Rank</option>
                                            @for($r = 1; $r <= $totalCandidates; $r++)
                                                <option value="{{ $r }}" 
                                                        :disabled="!isRankAvailable({{ $candidate->id }}, {{ $r }})"
                                                        x-text="getOptionLabel({{ $candidate->id }}, {{ $r }})">
                                                    Rank {{ $r }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    No active candidates found for this discipline.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Bar -->
            <div class="bg-slate-50 px-6 py-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-600">
                    <span class="font-bold text-slate-800" x-text="rankedCount + ' of ' + total + ' candidates ranked.'">
                        {{ count(array_filter($existingRanks)) }} of {{ $totalCandidates }} candidates ranked.
                    </span>
                    <span x-show="!isCompleteAndValid" class="text-amber-700 block sm:inline sm:ml-2">
                        Complete all ordinal rankings to enable submission.
                    </span>
                    <span x-show="isCompleteAndValid" x-cloak class="text-emerald-700 font-bold block sm:inline sm:ml-2">
                        ✓ All rankings verified. Ready for final submission.
                    </span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg shadow-sm text-center">
                        Back to Dashboard
                    </a>

                    <button type="submit" 
                            :disabled="!isCompleteAndValid" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-lg text-xs font-bold shadow-md transition-all"
                            :class="isCompleteAndValid 
                                ? 'bg-[#302556] hover:bg-[#241c42] text-white cursor-pointer shadow-md' 
                                : 'bg-slate-300 text-slate-500 cursor-not-allowed'">
                        Submit Ranking
                        <svg class="ml-2 w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Submission Confirmation Modal (Strict Requirement #32) -->
    <div x-show="confirmModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="confirmModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-slate-200 p-6 space-y-4" @click.stop>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Confirm Ranking Submission</h3>
                        <p class="text-xs text-slate-500">{{ $discipline->discipline_name }}</p>
                    </div>
                </div>

                <!-- Prompt #32 Exact Text -->
                <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                    You have completed the ranking exercise for <strong>{{ $discipline->discipline_name }}</strong>. Once submitted, your ranking will be recorded and cannot normally be changed. Do you want to submit your ranking?
                </p>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" 
                            @click="confirmModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg shadow-sm">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="executeFinalSubmit()" 
                            class="px-5 py-2 text-xs font-bold text-white bg-[#302556] hover:bg-[#241c42] rounded-lg shadow-md transition-colors">
                        Submit Ranking
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Real-Time Duplicate Prevention Engine -->
<script>
    const initialRankingState = {
        total: {{ (int) $totalCandidates }},
        candidateIds: @json($candidates->pluck('id')),
        initialRanks: @json((object) ($existingRanks ?? [])),
        disciplineName: @json($discipline->discipline_name ?? 'Discipline')
    };

    function rankingBoard() {
        return {
            total: initialRankingState.total,
            candidateIds: initialRankingState.candidateIds,
            disciplineName: initialRankingState.disciplineName,
            allRanks: Array.from({ length: initialRankingState.total }, (_, i) => i + 1),
            selections: {},
            confirmModalOpen: false,

            init() {
                // Initialize reactive selection for each candidate
                this.candidateIds.forEach(id => {
                    const initVal = initialRankingState.initialRanks[id];
                    this.selections[id] = (initVal !== undefined && initVal !== null && initVal !== '') ? Number(initVal) : '';
                });
            },

            // Check if rank r is available for candidateId
            // A rank is available if NO OTHER candidate currently holds it
            isRankAvailable(candidateId, rank) {
                const targetRank = Number(rank);
                return !Object.entries(this.selections).some(([cid, r]) => {
                    return String(cid) !== String(candidateId) && Number(r) === targetRank;
                });
            },

            // Generate label for select option
            getOptionLabel(candidateId, rank) {
                const targetRank = Number(rank);
                const isSelectedByOther = Object.entries(this.selections).some(([cid, r]) => {
                    return String(cid) !== String(candidateId) && Number(r) === targetRank;
                });

                if (isSelectedByOther) {
                    return 'Rank ' + rank + ' (Selected)';
                }
                return 'Rank ' + rank;
            },

            // Handle rank change dynamically
            onRankChange(candidateId, newRankValue) {
                if (newRankValue === '' || newRankValue === null) {
                    this.selections[candidateId] = '';
                } else {
                    this.selections[candidateId] = Number(newRankValue);
                }
            },

            // Reset all selections
            resetRankings() {
                if (confirm('Clear all ranking selections?')) {
                    this.candidateIds.forEach(id => {
                        this.selections[id] = '';
                    });
                }
            },

            // Count of ranked candidates
            get rankedCount() {
                return Object.values(this.selections).filter(v => v !== '' && v !== null && !isNaN(v) && Number(v) > 0).length;
            },

            // Sequence completeness and validity: must equal [1..total] exactly
            get isCompleteAndValid() {
                const values = Object.values(this.selections)
                    .map(Number)
                    .filter(v => !isNaN(v) && v >= 1 && v <= this.total);

                if (values.length !== this.total) {
                    return false;
                }

                // Check uniqueness
                const unique = new Set(values);
                return unique.size === this.total;
            },

            get validationStatusMessage() {
                if (this.rankedCount < this.total) {
                    return 'Please rank all candidates before submitting (' + this.rankedCount + ' of ' + this.total + ' ranked).';
                }
                if (!this.isCompleteAndValid) {
                    return 'Please ensure that all ranking numbers from 1 to ' + this.total + ' are assigned exactly once.';
                }
                return '';
            },

            get missingRanksText() {
                const assigned = new Set(
                    Object.values(this.selections)
                        .map(Number)
                        .filter(v => !isNaN(v) && v >= 1 && v <= this.total)
                );
                const missing = this.allRanks.filter(r => !assigned.has(r));
                return missing.join(', ');
            },

            openConfirmModal() {
                if (!this.isCompleteAndValid) return;
                this.confirmModalOpen = true;
            },

            executeFinalSubmit() {
                this.confirmModalOpen = false;
                document.getElementById('ranking-form').submit();
            }
        };
    }
</script>
@endsection
