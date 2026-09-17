@extends('layouts.app')

@section('title', 'Admin Ranking Exercises')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Ranking Exercises</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Ranking Exercises & Cycles</h1>
            <p class="text-sm text-slate-600 mt-1">
                Configure evaluation periods, open/close submission windows, and track ballot volume.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.exercises.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Create Exercise Cycle
            </a>
        </div>
    </div>

    {{-- Exercises Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Exercise Title</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Start & End Window</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Total Submissions</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($exercises as $ex)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $ex->exercise_name }}</span>
                                <span class="text-slate-500 text-xs">{{ Str::limit($ex->description, 60) }}</span>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ex->status === 'Open' ? 'bg-emerald-100 text-emerald-800' : ($ex->status === 'Closed' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $ex->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <div><strong class="text-slate-700">Start:</strong> {{ $ex->start_datetime ? $ex->start_datetime->format('d M Y, h:i A') : 'Immediate' }}</div>
                                <div class="mt-0.5"><strong class="text-slate-700">End:</strong> {{ $ex->end_datetime ? $ex->end_datetime->format('d M Y, h:i A') : 'No Deadline' }}</div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-900 text-sm">
                                {{ $ex->ranking_submissions_count ?? $ex->rankingSubmissions()->count() }}
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.ranking-results.index', ['exercise_id' => $ex->id]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                                    Results Matrix
                                </a>
                                <a href="{{ route('admin.exercises.edit', $ex) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No ranking exercises created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $exercises->links() }}
        </div>
    </div>
</div>
@endsection
