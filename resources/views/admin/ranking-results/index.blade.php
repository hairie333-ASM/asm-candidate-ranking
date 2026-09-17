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
        <div class="bg-gradient-to-r from-slate-900 to-teal-950 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                    {{ $results['discipline']->discipline_name }}
                </span>
                <h2 class="text-lg font-bold mt-1">Aggregated Candidate Preference Standings</h2>
            </div>
            <div class="text-right">
                @if($isSingleDiscipline)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-blue-500 text-white shadow-sm">
                        Ranking: Not Required (Single Candidate)
                    </span>
                    <span class="text-xs text-teal-200 block mt-1">Sole Nominee Confirmed</span>
                @else
                    <span class="text-xs text-teal-200 uppercase tracking-wider block">Submitted Ballots</span>
                    <span class="text-xl font-extrabold text-white">{{ $results['submissions_count'] }} Completed</span>
                @endif
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
            <div class="bg-slate-50 px-6 py-2.5 border-b border-slate-200 text-[11px] text-slate-600 flex items-center">
                <svg class="w-3.5 h-3.5 text-teal-600 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><strong>Tie-Breaking Standard:</strong> 1st: Lower Average Rank &bull; 2nd: Highest Rank 1 Count &bull; 3rd: Highest Rank 2 Count &bull; 4th: Candidate ID order</span>
            </div>
        @endif

        <div class="overflow-x-auto">
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
                                        <span class="font-bold text-slate-900 text-xs block leading-tight">{{ $candidate->candidate_name }}</span>
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
@endsection
