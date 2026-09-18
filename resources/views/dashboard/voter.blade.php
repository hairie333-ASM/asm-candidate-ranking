@extends('layouts.app', ['title' => 'My Dashboard - ' . ($dossier['code'] ?? 'Discipline') . ' Landing Page'])

@section('content')
<div class="space-y-6 max-w-5xl mx-auto py-4">

    {{-- Welcome & Institutional Status Header --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Authorized Voting Fellow
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-[#302556] text-white">
                    {{ $dossier['code'] ?? $discipline->code }} Discipline Group
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Welcome, {{ $user->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Academy of Sciences Malaysia &bull; Selection Exercise for New Election Fellow 2026
            </p>
        </div>

        @if($exercise)
            <div class="text-left md:text-right bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Ranking Exercise</span>
                <span class="block text-sm font-extrabold text-slate-800">{{ $exercise->exercise_name }}</span>
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $exercise->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    Status: {{ $exercise->status }}
                </span>
            </div>
        @endif
    </div>

    {{-- Official Discipline Communiqué & Landing Page Card (From Download/Landing pages) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Dossier Header Banner --}}
        <div class="bg-gradient-to-r from-slate-900 via-[#302556] to-teal-950 px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                    Official Announcement &bull; {{ $dossier['code'] ?? $discipline->code }}
                </span>
                <h2 class="text-lg sm:text-xl font-bold mt-1 text-white">
                    {{ $dossier['full_name'] ?? $discipline->discipline_name }}
                </h2>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[11px] text-teal-200 uppercase tracking-wider block font-semibold">Voting Window</span>
                <span class="text-xs sm:text-sm font-bold text-white">18 Mar &ndash; 26 Mar 2026</span>
            </div>
        </div>

        {{-- Official Announcement Body (Clean Plain Text Communiqué) --}}
        <div class="p-6 sm:p-10 space-y-4 text-slate-800 text-sm sm:text-base leading-relaxed">
            {{-- Formal Salutation --}}
            <p class="font-bold text-slate-900">
                {{ $dossier['salutation'] }}
            </p>

            {{-- Paragraph 1: Vetting Committee Convening --}}
            <p class="text-justify">
                {{ $dossier['vetting_paragraph'] }}
            </p>

            {{-- Paragraph 2: Membership Committee Deliberation --}}
            <p class="text-justify">
                {{ $dossier['membership_paragraph'] }}
            </p>

            {{-- Paragraph 3: Next Steps (Ranking Procedure or Single Candidate Notice) --}}
            <p class="text-justify">
                {{ $dossier['next_steps_paragraph'] }}
            </p>

            {{-- Paragraph 4: Confidential Cross-Discipline Due Diligence --}}
            <p class="text-justify">
                {{ $dossier['due_diligence_paragraph'] }}
            </p>

            {{-- Paragraph 5: Nominees Intro --}}
            <p class="text-justify">
                {{ $dossier['nominees_intro'] }}
            </p>

            {{-- Nominees List (Just Text, no boxes, no cards, no photos) --}}
            @if(!empty($dossier['shortlisted_nominees']))
                @php
                    $honorifics = ['dato', 'datuk', 'prof', 'professor', 'profesor', 'puan', 'encik', 'madam', 'tuan', 'raja', 'syed', 'seri', 'tan', 'sri'];
                @endphp
                <ol class="list-decimal list-inside space-y-1 pl-2 text-slate-900 font-medium">
                    @foreach($dossier['shortlisted_nominees'] as $index => $nomineeName)
                        @php
                            if (isset($candidates) && $candidates->count() === count($dossier['shortlisted_nominees']) && isset($candidates[$index])) {
                                $candModel = $candidates[$index];
                            } else {
                                $words = array_filter(
                                    explode(' ', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $nomineeName))),
                                    fn($w) => strlen($w) >= 4 && !in_array($w, $honorifics)
                                );
                                $candModel = isset($candidates) ? $candidates->first(function($c) use ($words) {
                                    $cName = strtolower($c->candidate_name);
                                    foreach ($words as $w) {
                                        if (str_contains($cName, $w)) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }) : null;
                            }
                        @endphp
                        <li>
                            @if($candModel)
                                <button type="button" onclick="openCandidateModal({{ $candModel->id }})" class="hover:text-teal-700 hover:underline cursor-pointer text-left font-medium">
                                    {{ $nomineeName }}
                                </button>
                            @else
                                <span>{{ $nomineeName }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @elseif(isset($candidates) && $candidates->isNotEmpty())
                <ol class="list-decimal list-inside space-y-1 pl-2 text-slate-900 font-medium">
                    @foreach($candidates as $candidate)
                        <li>
                            <button type="button" onclick="openCandidateModal({{ $candidate->id }})" class="hover:text-teal-700 hover:underline cursor-pointer text-left font-medium">
                                {{ $candidate->candidate_name }}
                            </button>
                        </li>
                    @endforeach
                </ol>
            @endif

            {{-- Paragraph 6: Official Voting Window --}}
            <p class="text-justify pt-1">
                {{ $dossier['voting_timeline_paragraph'] }}
            </p>

            {{-- Paragraph 7: Sign-off --}}
            <p class="font-bold text-slate-900 pt-1">
                {{ $dossier['sign_off'] }}
            </p>
        </div>
    </div>

    {{-- Voting Console & Action Area --}}
    <div class="bg-gradient-to-br from-[#302556] via-[#241c42] to-[#008442]/80 rounded-2xl p-6 sm:p-8 text-white shadow-lg border border-white/10 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-white/20 pb-3 gap-2">
            <div>
                <span class="text-xs font-black uppercase tracking-widest text-emerald-300">DISCIPLINE VOTING CONSOLE</span>
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-0.5 text-white">
                    {{ $discipline->discipline_name ?? 'Assigned Discipline' }}
                </h3>
            </div>
            <div>
                @if($isSingleCandidate)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-blue-500 text-white shadow-sm">
                        Sole Nominee Confirmed
                    </span>
                @elseif($isSubmitted)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-[#008442] text-white shadow-sm">
                        Ballot Submitted ✓
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950 shadow-sm">
                        Awaiting Your Ranking
                    </span>
                @endif
            </div>
        </div>

        {{-- Metrics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Shortlisted Nominees</span>
                <span class="block text-2xl font-black text-white">{{ $candidateCount }} {{ $candidateCount === 1 ? 'nominee' : 'nominees' }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Ranking Requirement</span>
                <span class="block text-2xl font-black text-white">
                    @if($isSingleCandidate)
                        Not Required ✓
                    @elseif($isSubmitted)
                        {{ $candidateCount }} / {{ $candidateCount }} ranked ✓
                    @else
                        {{ $rankedCount }} / {{ $candidateCount }} ranked
                    @endif
                </span>
                @if($isSingleCandidate)
                    <span class="block text-[11px] text-blue-200">1 candidate — Ranking not required</span>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Submission Status</span>
                <div>
                    @if($isSingleCandidate)
                        <span class="text-sm font-bold text-emerald-300 block">SATISFIED ✓</span>
                        <span class="block text-[11px] text-slate-300">Single candidate advancement</span>
                    @elseif($isSubmitted)
                        <span class="text-sm font-bold text-emerald-300 block">SUBMITTED ✓</span>
                        <span class="block text-[11px] text-slate-300 font-mono">{{ $submission->submitted_at?->format('d M Y, h:i A') }}</span>
                    @else
                        <span class="text-sm font-bold text-amber-300 block">NOT SUBMITTED</span>
                        <span class="block text-[11px] text-slate-300">Rankings pending submission</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Call To Action Buttons --}}
        <div class="pt-3 flex flex-wrap items-center gap-4">
            @if($isSingleCandidate)
                @if($singleCandidate)
                    <button type="button" onclick="openCandidateModal({{ $singleCandidate->id }})" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-bold text-xs shadow-md transition-all cursor-pointer">
                        View Nominee Profile
                        <svg class="ml-1.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                @endif
                <a href="{{ route('my-submission') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-xs border border-white/20 transition">
                    View Discipline Receipt
                </a>
            @elseif($isSubmitted)
                <a href="{{ route('my-submission') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#008442] hover:bg-[#007038] text-white font-bold text-sm shadow-md transition-all">
                    View My Submitted Rankings
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </a>
            @else
                <a href="{{ route('ranking.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#008442] hover:bg-[#007038] text-white font-bold text-sm shadow-md transition-all">
                    {{ $rankedCount > 0 ? 'Continue Ranking Exercise' : 'Start Ranking Exercise' }}
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif

            @if(Auth::user()->isAdmin())
                <a href="{{ route('candidates.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm transition">
                    Search All Candidates (All 8 Disciplines)
                </a>
            @endif
        </div>
    </div>

    {{-- Quick Reference Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="{{ route('due-diligence.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center group-hover:bg-teal-700 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition">Due Diligence Review</h4>
            <p class="text-xs text-slate-500 leading-relaxed">Confidential feedback & supporting document submissions across all 8 disciplines.</p>
        </a>

        <a href="{{ route('selection-process') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center group-hover:bg-indigo-700 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-700 transition">Selection Process</h4>
            <p class="text-xs text-slate-500 leading-relaxed">Official roadmap from Discipline Vetting to Membership Committee and AGM.</p>
        </a>

        <a href="{{ route('evaluation-rubric') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition space-y-2 group">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-amber-700 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-amber-700 transition">Evaluation Rubric</h4>
            <p class="text-xs text-slate-500 leading-relaxed">Detailed criteria and scoring benchmarks for election as an ASM Fellow.</p>
        </a>
    </div>

</div>

<x-candidate-modal />
@endsection
