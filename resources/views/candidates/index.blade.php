@extends('layouts.app')

@section('title', 'Candidate Search (All 8 Disciplines)')

@section('content')
<div x-data="{}" class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header & Search Bar --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                    <span class="mx-2">/</span>
                    <span class="text-teal-700 font-semibold">Candidates</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Candidate Search Directory</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Search and explore shortlisted candidate profiles across all 8 ASM disciplines.
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                    <svg class="w-4 h-4 mr-1.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Cross-Discipline Access Active
                </span>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="mt-6 bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
            <form method="GET" action="{{ route('candidates.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                {{-- Search query input --}}
                <div class="sm:col-span-6 relative">
                    <label for="q" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Search Keyword</label>
                    <div class="relative">
                        <input type="text" 
                               name="q" 
                               id="q" 
                               value="{{ $filters['q'] ?? '' }}" 
                               placeholder="Search candidate name, organisation, or specialty..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Discipline dropdown --}}
                <div class="sm:col-span-4">
                    <label for="discipline_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Discipline Filter</label>
                    <select name="discipline_id" 
                            id="discipline_id" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
                        <option value="">All 8 Disciplines</option>
                        @foreach($disciplines as $disc)
                            <option value="{{ $disc->id }}" {{ (string)($filters['discipline_id'] ?? '') === (string)$disc->id ? 'selected' : '' }}>
                                {{ $disc->discipline_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action buttons --}}
                <div class="sm:col-span-2 flex items-end space-x-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                        Filter
                    </button>
                    @if(!empty($filters['q']) || !empty($filters['discipline_id']))
                        <a href="{{ route('candidates.index') }}" class="inline-flex items-center justify-center py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition" title="Clear Filters">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Candidate Count Summary --}}
    <div class="mb-4 flex items-center justify-between text-xs text-slate-500">
        <div>
            Showing <strong class="text-slate-800">{{ $candidates->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $candidates->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ $candidates->total() }}</strong> candidates
            @if(!empty($filters['discipline_id']))
                @php
                    $selectedDiscipline = $disciplines->firstWhere('id', $filters['discipline_id']);
                @endphp
                (Filtered by {{ $selectedDiscipline->discipline_name ?? 'Selected Discipline' }})
            @endif
        </div>
    </div>

    {{-- Candidates Grid --}}
    @if($candidates->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">No Candidates Found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                No candidates match your search parameters. Try adjusting your keyword or clearing the discipline filter.
            </p>
            <a href="{{ route('candidates.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                Reset All Filters
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($candidates as $candidate)
                <x-candidate-card :candidate="$candidate" mode="public" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $candidates->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Candidate Information Modal Component --}}
<x-candidate-modal />
@endsection
