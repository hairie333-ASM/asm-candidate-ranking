@extends('layouts.app')

@section('title', 'Admin Candidate Management')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ viewMode: 'grid' }">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Candidate Management</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Candidate Directory Management</h1>
            <p class="text-sm text-slate-600 mt-1">
                Maintain shortlisted nominee credentials, OneDrive dossier links, photos, and discipline rosters.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            {{-- Grid / Table View Switcher --}}
            <div class="inline-flex bg-slate-100 p-1 rounded-xl border border-slate-200">
                <button type="button" 
                        @click="viewMode = 'grid'" 
                        :class="viewMode === 'grid' ? 'bg-white text-teal-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Standard Cards
                </button>
                <button type="button" 
                        @click="viewMode = 'table'" 
                        :class="viewMode === 'table' ? 'bg-white text-teal-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Table List
                </button>
            </div>

            <a href="{{ route('admin.candidates.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Add Shortlisted Candidate
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.candidates.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-6">
                <label for="q" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Search Candidate / Organisation</label>
                <input type="text" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search candidate name, organisation, or specialty..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="sm:col-span-4">
                <label for="discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discipline</label>
                <select name="discipline_id" id="discipline_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="all">All 8 Disciplines</option>
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ (string)($filters['discipline_id'] ?? '') === (string)$disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex space-x-2">
                <button type="submit" class="w-full py-2.5 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer">
                    Filter
                </button>
                @if(!empty($filters['q']) || !empty($filters['discipline_id']))
                    <a href="{{ route('admin.candidates.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Results Summary --}}
    <div class="mb-4 flex items-center justify-between text-xs text-slate-500">
        <span>Showing <strong class="text-slate-800">{{ $candidates->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $candidates->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ $candidates->total() }}</strong> candidates</span>
    </div>

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
            <a href="{{ route('admin.candidates.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                Reset All Filters
            </a>
        </div>
    @else
        {{-- Standard Candidate Card Grid View --}}
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($candidates as $candidate)
                <x-candidate-card :candidate="$candidate" mode="admin" />
            @endforeach
        </div>

        {{-- Compact Table View --}}
        <div x-show="viewMode === 'table'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-semibold">Candidate Profile</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Discipline</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Affiliation to ASM & Specialty</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($candidates as $c)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center space-x-3">
                                        @if($c->photo_url)
                                            <img src="{{ $c->photo_url }}" alt="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                {{ substr($c->candidate_name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm block leading-tight">{{ $c->candidate_name }}</span>
                                            <span class="text-[11px] text-teal-700 font-medium">{{ $c->candidate_title ?: 'Not provided' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-800 border border-teal-200" title="{{ $c->discipline->discipline_name ?? '' }}">
                                        {{ $c->discipline->code ?? 'Discipline' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="font-semibold text-slate-800 block">{{ $c->affiliation_to_asm ?: 'Not provided' }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $c->area_of_expertise ?: 'Not provided' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.candidates.toggle-active', $c) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $c->active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}" title="Click to toggle status">
                                            {{ $c->active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2">
                                    <button type="button" 
                                            onclick="openCandidateModal({{ $c->id }})" 
                                            class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 rounded border border-slate-200 hover:bg-slate-200 cursor-pointer" title="Quick View">
                                        View
                                    </button>
                                    @if($c->nomination_form_url)
                                        <a href="{{ $c->nomination_form_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-blue-700 bg-blue-50 rounded border border-blue-200 hover:bg-blue-100" title="Open OneDrive Dossier">
                                            Dossier
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.candidates.edit', $c) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $candidates->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Candidate Modal Component --}}
<x-candidate-modal />
@endsection
