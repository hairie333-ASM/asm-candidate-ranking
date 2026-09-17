@extends('layouts.app', ['title' => 'Home - Selection Exercise for New Election Fellow'])

@section('content')
<div class="space-y-8 max-w-5xl mx-auto py-4">

    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-[#302556] via-[#241c42] to-[#008442]/80 rounded-2xl p-8 sm:p-10 text-white shadow-xl border border-white/10 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#008442]/40 text-emerald-200 border border-emerald-400/30">
                Official Academy Evaluation Portal
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                ASM Selection Exercise for New Election Fellow
            </h1>
            <p class="text-slate-200 text-base sm:text-lg leading-relaxed">
                A confidential, integrity-protected platform for the Academy of Sciences Malaysia (ASM) to conduct ordinal candidate evaluation and due diligence assessments across 8 scientific disciplines.
            </p>

            <div class="pt-4 flex flex-wrap items-center gap-4">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#008442] hover:bg-[#007038] text-white font-bold text-sm shadow-lg shadow-emerald-900/30 transition-all focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        Login to Start Ranking
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    @if(Auth::user()->isVotingUser())
                        <a href="{{ route('ranking.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#008442] hover:bg-[#007038] text-white font-bold text-sm shadow-lg shadow-emerald-900/30 transition-all">
                            Proceed to My Ranking Board
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-[#008442] hover:bg-[#007038] text-white font-bold text-sm shadow-lg shadow-emerald-900/30 transition-all">
                            Go to Dashboard
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                @endguest
                <a href="{{ route('other-information') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm transition">
                    Ranking Guidelines & FAQs
                </a>
            </div>
        </div>
    </div>

    <!-- Core Methodology & Rules -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-lg">
                1
            </div>
            <h3 class="text-base font-bold text-slate-900">Ordinal Ranking Methodology</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Candidates must be ranked strictly from <strong>Rank 1 (Highest Preference)</strong> to <strong>Rank N (Lowest Preference)</strong>. Every candidate receives exactly one ranking number, with zero skips and zero duplicates allowed.
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-lg">
                2
            </div>
            <h3 class="text-base font-bold text-slate-900">Real-Time Duplicate Prevention</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                The interactive ranking board prevents duplicate numbers at the moment of selection. Choosing Rank 1 immediately locks it for other candidates, guaranteeing a smooth and error-free evaluation experience.
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-lg">
                3
            </div>
            <h3 class="text-base font-bold text-slate-900">Due Diligence & Nomination Dossiers</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Clicking candidate names opens quick information modals and direct links to full OneDrive nomination packages. Authorised reviewers and fellows can submit due diligence observations across all 8 disciplines.
            </p>
        </div>
    </div>

    <!-- 8 Disciplines Overview -->
    <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">The 8 Shortlisted Disciplines</h2>
                <p class="text-xs text-slate-500 mt-0.5">Authorised Voting Users are assigned to evaluate candidates within their designated discipline.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $disciplinesList = [
                    ['code' => 'BAES', 'name' => 'BAES - Biological Agriculture and Environmental Sciences'],
                    ['code' => 'CS', 'name' => 'CS - Chemical Sciences'],
                    ['code' => 'ES', 'name' => 'ES - Engineering Sciences'],
                    ['code' => 'ITCS', 'name' => 'ITCS - Information Technology and Computer Sciences'],
                    ['code' => 'MHS', 'name' => 'MHS - Medical and Health Sciences'],
                    ['code' => 'MPES', 'name' => 'MPES - Mathematical and Physical Sciences'],
                    ['code' => 'STDI', 'name' => 'STDI - Science and Technology Development and Industry'],
                    ['code' => 'SSH', 'name' => 'SSH - Social Sciences and Humanities'],
                ];
            @endphp

            @foreach($disciplinesList as $d)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 hover:border-teal-300 transition-colors">
                    <span class="inline-block text-xs font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                        {{ $d['code'] }}
                    </span>
                    <h4 class="text-sm font-semibold text-slate-900">{{ $d['name'] }}</h4>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Confidentiality Statement -->
    <div class="bg-amber-50 border-l-4 border-amber-500 p-5 rounded-r-xl shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-amber-900">CONFIDENTIAL ACADEMY PROCEEDING</h3>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    All candidate identities, nomination materials, rankings, and due diligence submissions are strictly confidential. System access is monitored and logged in accordance with the ASM Governance Charter and applicable personal data protection protocols.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
