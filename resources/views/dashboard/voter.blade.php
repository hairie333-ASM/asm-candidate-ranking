@extends('layouts.app', ['title' => 'My Dashboard - Voter'])

@section('content')
<div class="space-y-6 max-w-5xl mx-auto py-4">

    <!-- Welcome Header -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 mb-2">
                Authorized Voting User
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Welcome, {{ $user->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Candidate Evaluation & Ranking Portal &bull; Academic Year {{ date('Y') }}
            </p>
        </div>

        @if($exercise)
            <div class="text-left md:text-right bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Exercise</span>
                <span class="block text-sm font-extrabold text-slate-800">{{ $exercise->exercise_name }}</span>
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $exercise->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    Status: {{ $exercise->status }}
                </span>
            </div>
        @endif
    </div>

    <!-- YOUR DISCIPLINE Section (Strict Requirement #15) -->
    <div class="bg-gradient-to-br from-[#302556] via-[#241c42] to-[#008442]/80 rounded-2xl p-6 sm:p-8 text-white shadow-lg border border-white/10 space-y-4">
        <div class="border-b border-white/20 pb-3">
            <span class="text-xs font-black uppercase tracking-widest text-emerald-300">YOUR ASSIGNED DISCIPLINE</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1 text-white">
                {{ $discipline->discipline_name ?? 'Assigned Discipline' }}
            </h2>
        </div>

        <div class="pt-1">
            @if($isSingleCandidate)
                <h3 class="text-base font-bold text-emerald-200">Discipline Evaluation Status</h3>
                <p class="text-xs sm:text-sm text-slate-200 mt-0.5 leading-relaxed">
                    This discipline contains a single shortlisted candidate. In accordance with Academy election guidelines, <strong>no ranking exercise is required</strong>. The candidate is automatically recognized as the sole nominee.
                </p>
            @else
                <h3 class="text-base font-bold text-emerald-200">Candidate Ranking</h3>
                <p class="text-xs sm:text-sm text-slate-200 mt-0.5 leading-relaxed">
                    You are authorised to rank shortlisted candidates from <strong>this discipline only</strong>. Ranking candidates in other disciplines is restricted by Academy election policy.
                </p>
            @endif
        </div>

        <!-- Discipline Status & Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <!-- Candidate Count -->
            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Candidates in Discipline</span>
                <span class="block text-2xl font-black text-white">{{ $candidateCount }} {{ $candidateCount === 1 ? 'candidate' : 'candidates' }}</span>
            </div>

            <!-- Ranking Progress -->
            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Ranking Progress</span>
                <span class="block text-2xl font-black text-white">
                    @if($isSingleCandidate)
                        Not Required ✓
                    @elseif($isSubmitted)
                        {{ $candidateCount }} / {{ $candidateCount }} ranked ✓
                    @else
                        {{ $rankedCount }} / {{ $candidateCount }} ranked
                    @endif
                </span>
            </div>

            <!-- Submission Status -->
            <div class="p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 space-y-1">
                <span class="block text-xs font-semibold text-slate-200">Submission Status</span>
                <div>
                    @if($isSingleCandidate)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-[#008442] text-white shadow-sm">
                            SATISFIED ✓
                        </span>
                        <span class="block text-[11px] text-emerald-300 mt-1">
                            1 candidate — Ranking not required
                        </span>
                    @elseif($isSubmitted)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-[#008442] text-white shadow-sm">
                            SUBMITTED ✓
                        </span>
                        <span class="block text-[11px] text-emerald-300 mt-1 font-mono">
                            {{ $submission->submitted_at?->format('d M Y, H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950 shadow-sm">
                            NOT SUBMITTED
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Call to Action Area -->
        <div class="pt-4 flex flex-wrap items-center gap-4">
            @if($isSingleCandidate)
                {{-- Single Candidate Discipline: Hide "Start Ranking Exercise", "Proceed My Ranking Board", "My Ranking" --}}
                <div class="w-full bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center border border-emerald-400/30 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white block">1 candidate — Ranking not required</span>
                            <span class="text-xs text-slate-200">This discipline has only one candidate. Ranking exercise is not required.</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($singleCandidate)
                            <a href="{{ route('candidates.show', $singleCandidate->id) }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-bold text-xs shadow-md transition-all">
                                View Candidate Profile
                                <svg class="ml-1.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endif
                        <a href="{{ route('my-submission') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-xs border border-white/20 transition">
                            View Discipline Receipt
                        </a>
                    </div>
                </div>
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

    @if($isSingleCandidate && $singleCandidate)
        <!-- Sole Candidate Spotlight Card -->
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        Sole Nominee for {{ $discipline->code ?? 'Discipline' }}
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">1 candidate — Ranking not required</span>
                </div>
                <a href="{{ route('candidates.show', $singleCandidate->id) }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">
                    Full Dossier & Profile &rarr;
                </a>
            </div>
            <x-candidate-card :candidate="$singleCandidate" layout="full" />
        </div>
    @endif

    <!-- Quick Navigation & Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Due Diligence Feature Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Due Diligence Review (All Disciplines)</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                While your ranking rights are restricted to {{ $discipline->discipline_name ?? 'your assigned discipline' }}, you can search candidates and submit confidential due diligence assessments across <strong>all 8 disciplines</strong>.
            </p>
            <div class="pt-2">
                <a href="{{ route('due-diligence.index') }}" class="text-xs font-bold text-teal-700 hover:text-teal-800 inline-flex items-center">
                    Submit Due Diligence Assessment &rarr;
                </a>
            </div>
        </div>

        <!-- Exercise Details Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Important Timelines & Deadlines</h3>
            <div class="space-y-1.5 text-xs text-slate-600">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Exercise Start:</span>
                    <span class="font-semibold text-slate-800">{{ $exercise?->start_datetime?->format('d M Y, H:i') ?? 'Open' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Submission Deadline:</span>
                    <span class="font-semibold text-rose-700">{{ $exercise?->end_datetime?->format('d M Y, H:i') ?? 'Open' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Resubmission Policy:</span>
                    <span class="font-semibold text-slate-800">{{ $exercise?->allow_resubmission ? 'Allowed' : 'One Final Submission (Locked)' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
