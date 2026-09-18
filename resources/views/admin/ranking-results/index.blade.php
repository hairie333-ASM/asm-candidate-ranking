@extends('layouts.app')

@section('title', 'Candidate Ranking Results Matrix')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Results Matrix</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Candidate Ranking Matrix & Results</h1>
            <p class="text-sm text-slate-600 mt-1">
                Aggregated voter rankings, statistical averages, tie-break resolution, and ballot reopening.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.export-ranking-csv', ['discipline_id' => $selectedDisciplineId, 'exercise_id' => $selectedExerciseId]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Discipline CSV
            </a>
            <a href="{{ route('admin.reports.print-ranking', ['discipline_id' => $selectedDisciplineId, 'exercise_id' => $selectedExerciseId]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Official Sheet
            </a>
        </div>
    </div>

    {{-- Filter Bar (Exercise & Discipline) --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm mb-8">
        <form method="GET" action="{{ route('admin.ranking-results.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-4">
                <label for="exercise_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ranking Cycle</label>
                <select name="exercise_id" id="exercise_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @foreach($exercises as $ex)
                        <option value="{{ $ex->id }}" {{ $selectedExerciseId == $ex->id ? 'selected' : '' }}>
                            {{ $ex->title }} ({{ $ex->status }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-6">
                <label for="discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discipline</label>
                <select name="discipline_id" id="discipline_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ $selectedDisciplineId == $disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    View Matrix
                </button>
            </div>
        </form>
    </div>

    {{-- Results Summary Card --}}
    @php
        $isSingleDiscipline = ($results['is_single_candidate'] ?? false) || count($results['candidates_results']) === 1;
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8" x-data="{ viewMode: 'vertical' }">
        <div class="bg-gradient-to-r from-slate-900 to-teal-950 px-6 py-4 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                        {{ $results['discipline']->discipline_name }}
                    </span>
                    @if($isSingleDiscipline)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-500 text-white shadow-sm">
                            Ranking: Not Required (Single Candidate)
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-bold mt-1.5 text-white">Aggregated Candidate Preference Standings</h2>
                <p class="text-xs text-slate-300 mt-0.5">
                    @if($isSingleDiscipline)
                        Sole Nominee Confirmed &bull; Academic ranking not required for single-candidate discipline.
                    @else
                        Executive composite ranking calculated across all completed ballots.
                    @endif
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="text-left sm:text-right">
                    @if($isSingleDiscipline)
                        <span class="text-xs text-teal-200 block">Status</span>
                        <span class="text-sm font-bold text-white">Sole Nominee Confirmed</span>
                    @else
                        <span class="text-[10px] text-teal-200 uppercase tracking-wider block font-semibold">Submitted Ballots</span>
                        <span class="text-lg font-extrabold text-white">{{ $results['submissions_count'] }} Completed</span>
                    @endif
                </div>

                {{-- View Mode Switcher --}}
                <div class="inline-flex items-center bg-slate-800/90 p-1 rounded-xl border border-slate-700/80 text-xs shadow-inner">
                    <button type="button" 
                            @click="viewMode = 'vertical'" 
                            :class="viewMode === 'vertical' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white'" 
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <span>Vertical View</span>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'horizontal'" 
                            :class="viewMode === 'horizontal' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-300 hover:text-white'" 
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Horizontal Matrix</span>
                    </button>
                </div>
            </div>
        </div>

        @if($isSingleDiscipline)
            {{-- Single Candidate Notice --}}
            <div class="bg-blue-50 px-6 py-3 border-b border-blue-200 text-xs text-blue-900 flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><strong>Status:</strong> Ranking: Not Required (Single Candidate) &bull; This discipline contains exactly one shortlisted candidate. In accordance with Academy election guidelines, ranking is not required and this candidate is automatically recognized as the sole nominee.</span>
            </div>
        @else
            {{-- Tie-break rules note --}}
            <div class="bg-slate-50 px-6 py-2.5 border-b border-slate-200 text-[11px] text-slate-600 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-teal-600 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><strong>Tie-Breaking Standard:</strong> 1st: Lower Average Rank &bull; 2nd: Highest Rank 1 Count &bull; 3rd: Highest Rank 2 Count &bull; 4th: Candidate ID order</span>
                </div>
                <div class="text-[10px] text-slate-500 font-medium">
                    <span x-show="viewMode === 'vertical'">Displaying <strong>Vertical View</strong> (Optimized for voter legibility)</span>
                    <span x-show="viewMode === 'horizontal'">Displaying <strong>Horizontal Matrix</strong> (Spreadsheet layout)</span>
                </div>
            </div>
        @endif

        {{-- Vertical View (Default) --}}
        <div x-show="viewMode === 'vertical'" class="space-y-6 p-6 bg-slate-50/50">
            {{-- Section A: Candidate Standings Leaderboard --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Candidate Standings Leaderboard</h3>
                        <p class="text-[11px] text-slate-500">Overall order of preference determined by voter consensus and tie-breaking criteria.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">
                        {{ count($results['candidates_results']) }} Candidate{{ count($results['candidates_results']) === 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100/90 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center w-16 font-bold">Pos</th>
                                <th scope="col" class="px-6 py-3 font-semibold">Candidate</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Organisation</th>
                                <th scope="col" class="px-4 py-3 text-center font-bold bg-teal-50/70 text-teal-900 border-l border-teal-100">Avg Rank</th>
                                <th scope="col" class="px-4 py-3 text-center font-bold bg-amber-50/70 text-amber-900">Rank 1s</th>
                                <th scope="col" class="px-4 py-3 text-center font-bold bg-slate-50/90 text-slate-800">Rank 2s</th>
                                <th scope="col" class="px-4 py-3 text-center font-semibold">Total Ballots</th>
                                <th scope="col" class="px-4 py-3 text-center font-semibold">Standing Status</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold">Profile</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($results['candidates_results'] as $index => $item)
                                @php 
                                    $candidate = $item['candidate']; 
                                    $position = $item['position'] ?? ($index + 1);
                                    $isTopRanked = $position === 1 && !$isSingleDiscipline && $item['total_submissions'] > 0;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition {{ $isTopRanked ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-4 py-3.5 text-center font-bold">
                                        @if($isSingleDiscipline)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs bg-blue-100 text-blue-800 font-extrabold ring-2 ring-blue-400">
                                                1
                                            </span>
                                        @elseif($position === 1)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs bg-amber-100 text-amber-900 font-black ring-2 ring-amber-400 shadow-2xs" title="1st Preference (Gold)">
                                                1
                                            </span>
                                        @elseif($position === 2)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs bg-slate-200 text-slate-800 font-bold ring-1 ring-slate-300" title="2nd Preference (Silver)">
                                                2
                                            </span>
                                        @elseif($position === 3)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs bg-amber-100/60 text-amber-900 font-bold ring-1 ring-amber-300" title="3rd Preference (Bronze)">
                                                3
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold">
                                                {{ $position }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center space-x-3">
                                            <button type="button" 
                                                    onclick="openCandidateModal({{ $candidate->id }})" 
                                                    class="group relative focus:outline-none flex-shrink-0 cursor-pointer">
                                                @if($candidate->photo_path)
                                                    <img src="{{ $candidate->photo_path }}" alt="{{ $candidate->candidate_name }}" class="w-10 h-10 rounded-full object-cover object-top border-2 border-slate-200 group-hover:border-teal-500 transition shadow-2xs">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-800 group-hover:bg-teal-200 border-2 border-slate-200 group-hover:border-teal-500 flex items-center justify-center font-bold text-xs transition shadow-2xs">
                                                        {{ substr($candidate->candidate_name, 0, 2) }}
                                                    </div>
                                                @endif
                                            </button>
                                            <div class="min-w-0">
                                                <button type="button" 
                                                        onclick="openCandidateModal({{ $candidate->id }})" 
                                                        class="font-bold text-slate-900 hover:text-teal-700 text-xs sm:text-sm block leading-tight text-left transition truncate cursor-pointer">
                                                    {{ $candidate->candidate_name }}
                                                </button>
                                                <span class="text-[11px] text-slate-500 block truncate">{{ $candidate->sub_discipline ?? 'General' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-slate-600">
                                        {{ $candidate->organisation ?: 'Academy of Sciences Malaysia' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center bg-teal-50/70 border-l border-teal-100">
                                        @if($isSingleDiscipline)
                                            <span class="text-xs font-semibold text-slate-400">N/A</span>
                                        @elseif($item['average_rank'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-teal-100 text-teal-900 border border-teal-200 shadow-2xs">
                                                {{ number_format($item['average_rank'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center bg-amber-50/70">
                                        @if($isSingleDiscipline)
                                            <span class="text-slate-400">-</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $item['rank_1_count'] > 0 ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'text-slate-400' }}">
                                                {{ $item['rank_1_count'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center bg-slate-50/90">
                                        @if($isSingleDiscipline)
                                            <span class="text-slate-400">-</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $item['rank_2_count'] > 0 ? 'bg-slate-200 text-slate-800 border border-slate-300' : 'text-slate-400' }}">
                                                {{ $item['rank_2_count'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-medium text-xs text-slate-600">
                                        {{ $isSingleDiscipline ? 1 : $item['total_submissions'] }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($isSingleDiscipline)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                Sole Nominee
                                            </span>
                                        @elseif($item['total_submissions'] === 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                                Awaiting Votes
                                            </span>
                                        @elseif(!empty($item['is_tie']))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                Tied Rank {{ $position }}
                                            </span>
                                        @elseif($position === 1)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                                Top Preference
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                Rank {{ $position }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <button type="button" 
                                                onclick="openCandidateModal({{ $candidate->id }})" 
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 transition shadow-2xs cursor-pointer">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Profile
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-slate-400">
                                        No candidate rankings recorded yet for this discipline and exercise.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Section B: Individual Voter Ballot Breakdown (Transposed Vertical Layout) --}}
            @if(!$isSingleDiscipline && count($results['voters']) > 0)
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Individual Voter Ballot Breakdown</h3>
                            <p class="text-[11px] text-slate-500">Each completed voter ballot transposed into rows with candidate rankings shown in columns.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            {{ count($results['voters']) }} Voter Ballot{{ count($results['voters']) === 1 ? '' : 's' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-100 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-center w-12 font-bold">#</th>
                                    <th scope="col" class="px-6 py-3 font-semibold min-w-[200px]">Voter Name & Affiliation</th>
                                    <th scope="col" class="px-4 py-3 font-semibold w-40">Submitted At</th>
                                    {{-- Candidate Columns --}}
                                    @foreach($results['candidates_results'] as $cItem)
                                        @php $cand = $cItem['candidate']; @endphp
                                        <th scope="col" class="px-4 py-3 text-center font-bold border-l border-slate-200 bg-slate-50 min-w-[140px]">
                                            <div class="flex flex-col items-center">
                                                <button type="button" onclick="openCandidateModal({{ $cand->id }})" class="hover:underline font-bold text-slate-900 text-xs truncate max-w-[130px] cursor-pointer">
                                                    {{ $cand->candidate_name }}
                                                </button>
                                                <span class="text-[9px] text-teal-700 font-semibold">Rank #{{ $cItem['position'] ?? $loop->iteration }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($results['voters'] as $vIndex => $voter)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-4 py-3 text-center text-slate-400 font-semibold text-[11px]">
                                            {{ $vIndex + 1 }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="font-bold text-slate-900 text-xs block leading-tight">{{ $voter['name'] }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $voter['email'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 text-[11px] whitespace-nowrap">
                                            {{ !empty($voter['submitted_at']) ? \Carbon\Carbon::parse($voter['submitted_at'])->format('d M Y, h:i A') : 'Completed' }}
                                        </td>

                                        {{-- Candidate Ranks --}}
                                        @foreach($results['candidates_results'] as $cItem)
                                            @php $rn = $cItem['ranks'][$voter['id']] ?? null; @endphp
                                            <td class="px-4 py-3 text-center font-bold border-l border-slate-100">
                                                @if($rn === 1)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black bg-amber-100 text-amber-900 ring-1 ring-amber-300 shadow-2xs">
                                                        1
                                                    </span>
                                                @elseif($rn === 2)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold bg-slate-200 text-slate-800 ring-1 ring-slate-300">
                                                        2
                                                    </span>
                                                @elseif($rn)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                                        {{ $rn }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-100/90 border-t-2 border-slate-300 text-slate-800 text-xs">
                                <tr class="font-bold">
                                    <td colspan="3" class="px-6 py-3 text-right uppercase tracking-wider text-[11px] text-teal-900">
                                        Average Rank
                                    </td>
                                    @foreach($results['candidates_results'] as $cItem)
                                        <td class="px-4 py-3 text-center border-l border-slate-200 font-black text-sm bg-teal-50 text-teal-900">
                                            {{ $cItem['average_rank'] > 0 ? number_format($cItem['average_rank'], 2) : 'N/A' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="font-semibold text-slate-700">
                                    <td colspan="3" class="px-6 py-2.5 text-right uppercase tracking-wider text-[10px] text-amber-900">
                                        Rank 1 Votes Count
                                    </td>
                                    @foreach($results['candidates_results'] as $cItem)
                                        <td class="px-4 py-2.5 text-center border-l border-slate-200 font-bold bg-amber-50 text-amber-900">
                                            {{ $cItem['rank_1_count'] }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="font-semibold text-slate-700">
                                    <td colspan="3" class="px-6 py-2.5 text-right uppercase tracking-wider text-[10px] text-slate-700">
                                        Rank 2 Votes Count
                                    </td>
                                    @foreach($results['candidates_results'] as $cItem)
                                        <td class="px-4 py-2.5 text-center border-l border-slate-200 font-bold bg-slate-50 text-slate-800">
                                            {{ $cItem['rank_2_count'] }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Horizontal Matrix View (Spreadsheet Mode) --}}
        <div x-show="viewMode === 'horizontal'" class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-center w-16 font-bold">Pos</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Candidate</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Organisation</th>

                        {{-- Individual Voter Columns --}}
                        @foreach($results['voters'] as $voter)
                            <th scope="col" class="px-3 py-3 text-center font-semibold bg-slate-200/50" title="{{ $voter['name'] }} ({{ $voter['email'] }})">
                                <span class="block truncate max-w-[80px]">{{ Str::before($voter['name'], ' ') }}</span>
                                <span class="text-[9px] text-slate-500 font-normal">Voter</span>
                            </th>
                        @endforeach

                        <th scope="col" class="px-4 py-3 text-center font-bold bg-teal-50 text-teal-900 border-l border-teal-200">Avg Rank</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold bg-amber-50 text-amber-900">Rank 1s</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold bg-slate-50 text-slate-900">Rank 2s</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($results['candidates_results'] as $index => $item)
                        @php 
                            $candidate = $item['candidate']; 
                            $position = $index + 1;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ $position === 1 ? 'bg-amber-50/20' : '' }}">
                            <td class="px-4 py-3.5 text-center font-bold">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs {{ $position === 1 ? 'bg-amber-100 text-amber-800 font-black ring-2 ring-amber-400' : ($position === 2 ? 'bg-slate-200 text-slate-800 font-bold' : ($position === 3 ? 'bg-orange-100 text-orange-800 font-bold' : 'bg-slate-100 text-slate-600 font-semibold')) }}">
                                    {{ $position }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center space-x-2.5">
                                    @if($candidate->photo_path)
                                        <img src="{{ $candidate->photo_path }}" alt="{{ $candidate->candidate_name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                            {{ substr($candidate->candidate_name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <button type="button" onclick="openCandidateModal({{ $candidate->id }})" class="font-bold text-slate-900 hover:text-teal-700 text-xs block leading-tight text-left cursor-pointer">
                                            {{ $candidate->candidate_name }}
                                        </button>
                                        <span class="text-[10px] text-slate-500">{{ $candidate->sub_discipline ?? 'General' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-[11px] text-slate-600">
                                {{ Str::limit($candidate->organisation, 25) }}
                            </td>

                            {{-- Individual Voter Marks --}}
                            @foreach($results['voters'] as $voter)
                                @php $rn = $item['ranks'][$voter['id']] ?? null; @endphp
                                <td class="px-3 py-3.5 text-center font-bold border-l border-slate-100">
                                    @if($rn)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs {{ $rn === 1 ? 'bg-amber-200 text-amber-900 font-black' : ($rn === 2 ? 'bg-slate-200 text-slate-800' : 'bg-teal-50 text-teal-800') }}">
                                            {{ $rn }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="px-4 py-3.5 text-center font-black text-sm bg-teal-50 text-teal-900 border-l border-teal-200">
                                @if($isSingleDiscipline)
                                    <span class="text-xs font-semibold text-slate-500">N/A</span>
                                @else
                                    {{ $item['average_rank'] > 0 ? number_format($item['average_rank'], 2) : 'N/A' }}
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-xs bg-amber-50 text-amber-900">
                                {{ $isSingleDiscipline ? '-' : $item['rank_1_count'] }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-xs bg-slate-50 text-slate-800">
                                {{ $isSingleDiscipline ? '-' : $item['rank_2_count'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($results['voters']) }}" class="px-6 py-8 text-center text-slate-400">
                                No candidate rankings recorded yet for this discipline and exercise.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Ballot Submissions & Reopening Management --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ reopenModalOpen: false, selectedSubmissionId: null, voterName: '' }">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Voter Submissions & Ballot Reopening</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Inspect voter participation and unlock ballots to permit resubmission if requested by a voter.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Voter Name & Email</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Submitted At</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($submissions as $sub)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5">
                                <span class="font-bold text-slate-900 text-sm block">{{ $sub->user->name }}</span>
                                <span class="text-slate-500 text-xs">{{ $sub->user->email }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sub->status === 'SUBMITTED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">
                                {{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, h:i A') : 'Pending Final Submission' }}
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                @if($sub->status === 'SUBMITTED')
                                    <button type="button" 
                                            @click="selectedSubmissionId = {{ $sub->id }}; voterName = '{{ addslashes($sub->user->name) }}'; reopenModalOpen = true"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-amber-700 bg-amber-50 border border-amber-300 hover:bg-amber-100 transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                        </svg>
                                        Reopen Ballot
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Ballot Unlocked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                No submissions found for this discipline.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Reopen Modal --}}
        <div x-show="reopenModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="reopenModalOpen = false"></div>

                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                    <form :action="'/admin/ranking-results/reopen/' + selectedSubmissionId" method="POST" class="p-6">
                        @csrf
                        <div class="flex items-start space-x-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Confirm Ballot Reopening</h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Reopening ballot for voter: <strong class="text-slate-800" x-text="voterName"></strong>
                                </p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            This action will switch the submission status back to <strong>DRAFT</strong>. The voter will be able to alter their candidate rankings and resubmit. An audit log entry will be created.
                        </p>

                        <div class="mb-4">
                            <label for="reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Administrative Justification / Reason <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="reason" 
                                      id="reason" 
                                      required 
                                      rows="3" 
                                      placeholder="e.g., Requested by voter due to updated candidate credentials or accidental ranking error..." 
                                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="reopenModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm">
                                Confirm Reopen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<x-candidate-modal />
@endsection
