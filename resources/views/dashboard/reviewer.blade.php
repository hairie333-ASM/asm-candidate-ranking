@extends('layouts.app')

@section('title', 'Reviewer Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8">
        <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
            <span class="text-teal-700 font-semibold">Reviewer Portal</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Welcome, {{ $user->name }}</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Due Diligence Reviewer Portal &bull; Multi-discipline Candidate Review
                </p>
            </div>
            <div>
                <a href="{{ route('due-diligence.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Submit Due Diligence
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase font-semibold">Disciplines</span>
                <span class="block text-2xl font-bold text-slate-900">{{ $disciplines->count() }} Disciplines</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase font-semibold">Total Candidates</span>
                <span class="block text-2xl font-bold text-slate-900">{{ $totalCandidates }} Candidates</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 uppercase font-semibold">Due Diligence Entries</span>
                <span class="block text-2xl font-bold text-slate-900">{{ $totalDueDiligence }} Submissions</span>
            </div>
        </div>
    </div>

    {{-- Disciplines List --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <h2 class="text-lg font-bold text-slate-900 mb-2">The 8 Disciplines Directory</h2>
        <p class="text-xs text-slate-500 mb-6">Review candidate portfolios and submit observations by discipline category.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($disciplines as $discipline)
                <div class="border border-slate-200 rounded-xl p-4 hover:border-teal-400 hover:shadow-xs transition bg-slate-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-teal-100 text-teal-800">
                                {{ $discipline->code }}
                            </span>
                            @if($discipline->candidates_count === 1)
                                <span class="text-xs text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded">1 Candidate (No ranking required)</span>
                            @else
                                <span class="text-xs text-slate-500 font-semibold">{{ $discipline->candidates_count }} Candidates</span>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $discipline->discipline_name }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $discipline->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <a href="{{ route('candidates.index', ['discipline_id' => $discipline->id]) }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">
                            View Candidates &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
