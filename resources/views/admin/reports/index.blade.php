@extends('layouts.app')

@section('title', 'Administrative Reports & Data Exports')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8">
        <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
            <span class="mx-2">/</span>
            <span class="text-teal-700 font-semibold">Reports & Exports</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Reports & Data Exports</h1>
        <p class="text-sm text-slate-600 mt-1">
            Download institutional ballots, due diligence transcripts, and printable audit sheets.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Section 1: Ranking CSV Export --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col justify-between">
            <div class="p-6">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900 mb-1">Discipline Ranking CSV Export</h2>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Generates a standardized CSV spreadsheet containing composite standings, each voter's ordinal ranking for each candidate, statistical average rank, and total Rank 1 &amp; Rank 2 counts with tie-breakers applied.
                </p>

                <form method="GET" action="{{ route('admin.reports.export-ranking-csv') }}" class="space-y-4">
                    <div>
                        <label for="ranking_exercise_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ranking Cycle</label>
                        <select name="exercise_id" id="ranking_exercise_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($exercises as $ex)
                                <option value="{{ $ex->id }}">{{ $ex->title }} ({{ $ex->status }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ranking_discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discipline</label>
                        <select name="discipline_id" id="ranking_discipline_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($disciplines as $disc)
                                <option value="{{ $disc->id }}">{{ $disc->discipline_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Ranking CSV
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-[11px] text-slate-500">
                Action is logged in audit trail with timestamp.
            </div>
        </div>

        {{-- Section 2: Due Diligence Export --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col justify-between">
            <div class="p-6">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900 mb-1">Due Diligence CSV Export</h2>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Generates a consolidated CSV export of confidential due diligence submissions across disciplines, detailing submitter role, candidate name, evaluation category, full comments, and number of attached files.
                </p>

                <form method="GET" action="{{ route('admin.reports.export-due-diligence-csv') }}" class="space-y-4">
                    <div>
                        <label for="dd_discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Filter Discipline</label>
                        <select name="discipline_id" id="dd_discipline_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="all">All 8 Disciplines Combined</option>
                            @foreach($disciplines as $disc)
                                <option value="{{ $disc->id }}">{{ $disc->discipline_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-8">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Due Diligence CSV
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-[11px] text-slate-500">
                Confidential export &bull; Restricted strictly to Administrators.
            </div>
        </div>
    </div>

    {{-- Section 3: Printable Ranking Dossier Sheets --}}
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <h2 class="text-base font-bold text-slate-900 mb-1">Printable Official Ranking Dossier</h2>
        <p class="text-xs text-slate-500 mb-6">
            Generate clean, printer-optimized executive sheets for Council meetings and record retention.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($disciplines as $disc)
                <a href="{{ route('admin.reports.print-ranking', ['discipline_id' => $disc->id]) }}" target="_blank" class="p-4 rounded-xl border border-slate-200 hover:border-teal-500 hover:bg-teal-50/20 transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">{{ $disc->code }}</span>
                        <span class="text-[11px] text-slate-500 block truncate max-w-[180px]">{{ $disc->discipline_name }}</span>
                    </div>
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
