@extends('layouts.app')

@section('title', 'Admin Management Suite')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <span class="text-amber-600 font-semibold">System Administration</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">ASM Management Suite</h1>
            <p class="text-sm text-slate-600 mt-1">
                Executive monitoring, voter submission tracking, and candidate data management.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.ranking-results.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Ranking Results Matrix
            </a>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports & Export
            </a>
        </div>
    </div>

    {{-- System KPI Metrics --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Total Voters</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_voters'] }}</span>
            <span class="text-[10px] text-slate-500">Across 8 disciplines</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Candidates</span>
            <span class="text-2xl font-black text-teal-700 mt-1 block">{{ $stats['total_candidates'] }}</span>
            <span class="text-[10px] text-slate-500">Nominees in roster</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Disciplines</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_disciplines'] }}</span>
            <span class="text-[10px] text-slate-500">Academic domains</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Submissions</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $stats['total_submissions'] }}</span>
            <span class="text-[10px] text-slate-500">Completed ballots</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Due Diligence</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $stats['total_due_diligence'] }}</span>
            <span class="text-[10px] text-slate-500">Confidential entries</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase block">Total Users</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_users'] }}</span>
            <span class="text-[10px] text-slate-500">All registered roles</span>
        </div>
    </div>

    {{-- Discipline Progress Matrix --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Discipline Submission Progress</h2>
                <p class="text-xs text-slate-500 mt-0.5">Real-time participation rates across the 8 disciplines.</p>
            </div>
            @if($exercise)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                    Exercise: {{ $exercise->title }} ({{ $exercise->status }})
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Discipline</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Shortlisted Candidates</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Registered Voters</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Completed Ballots</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Participation %</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($disciplines as $disc)
                        @php
                            $submittedCount = $disc->rankingSubmissions->count();
                            $voterCount = $disc->users_count;
                            $pct = $voterCount > 0 ? round(($submittedCount / $voterCount) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $disc->discipline_name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $disc->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-900">{{ $disc->candidates_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-900">{{ $voterCount }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($disc->candidates_count === 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">
                                        Ranking: Not Required (Single Candidate)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $submittedCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $submittedCount }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($disc->candidates_count === 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                        Satisfied (100%)
                                    </span>
                                @else
                                    <div class="flex items-center justify-center space-x-2">
                                        <div class="w-16 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-teal-600 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700">{{ $pct }}%</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.ranking-results.index', ['discipline_id' => $disc->id]) }}" class="inline-flex items-center text-xs font-bold text-teal-700 hover:text-teal-900">
                                    View Matrix &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Admin Navigation Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.users.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200 hover:border-teal-500 shadow-xs hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="font-bold text-sm text-slate-900">User Management</h3>
            <p class="text-xs text-slate-500 mt-1">Manage voter accounts, roles, and discipline assignments.</p>
        </a>

        <a href="{{ route('admin.candidates.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200 hover:border-teal-500 shadow-xs hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h3 class="font-bold text-sm text-slate-900">Candidate Management</h3>
            <p class="text-xs text-slate-500 mt-1">Create, edit, and link OneDrive candidate dossiers.</p>
        </a>

        <a href="{{ route('admin.exercises.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200 hover:border-teal-500 shadow-xs hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="font-bold text-sm text-slate-900">Ranking Exercises</h3>
            <p class="text-xs text-slate-500 mt-1">Schedule, open, close, and manage ranking cycles.</p>
        </a>

        <a href="{{ route('admin.audit-logs.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200 hover:border-teal-500 shadow-xs hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h3 class="font-bold text-sm text-slate-900">Audit Trail & Logs</h3>
            <p class="text-xs text-slate-500 mt-1">Track ballot submissions, reopens, and user logins.</p>
        </a>
    </div>

    {{-- Recent Audit Activity --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center">
                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>
                Recent Security & System Activity
            </h2>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900">
                View All Activity &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px]">
                    <tr>
                        <th class="px-4 py-2">Timestamp</th>
                        <th class="px-4 py-2">User</th>
                        <th class="px-4 py-2">Action</th>
                        <th class="px-4 py-2">Target</th>
                        <th class="px-4 py-2">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                            <td class="px-4 py-2.5 font-bold text-slate-800">{{ $log->user ? $log->user->name : 'System/Guest' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $log->record_type }} #{{ $log->record_id }}</td>
                            <td class="px-4 py-2.5 font-mono text-[10px] text-slate-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
