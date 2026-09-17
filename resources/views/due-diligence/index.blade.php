@extends('layouts.app')

@section('title', 'Due Diligence Submissions')

@section('content')
<div x-data="{}" class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-8">
        <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-teal-700 font-semibold">Due Diligence</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Due Diligence</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Submit confidential assessments and research integrity observations for candidates in <strong class="text-teal-700 font-bold">any of the 8 disciplines</strong>.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Strictly Confidential & Anonymized
                </span>
            </div>
        </div>
    </div>

    {{-- Alert / Guidelines Banner --}}
    <div class="bg-gradient-to-r from-teal-900 to-slate-900 rounded-2xl p-6 text-white mb-8 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2.5 bg-teal-500/20 rounded-xl border border-teal-400/30 flex-shrink-0">
                <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-base text-white">Cross-Discipline Due Diligence Rules</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Unlike the candidate ranking phase (which is strictly restricted to your assigned discipline), due diligence observations are open across all 8 disciplines. You may submit qualitative assessments, research integrity notes, conflict of interest disclosures, or professional achievements for any candidate. Multiple additive submissions per candidate are allowed.
                </p>
            </div>
        </div>
    </div>

    {{-- My Past Submissions Section (if any) --}}
    @if($mySubmissions->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
            <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600 mr-2"></span>
                My Recent Due Diligence Entries ({{ $mySubmissions->count() }})
            </h2>
            <div class="divide-y divide-slate-100">
                @foreach($mySubmissions as $sub)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center space-x-2">
                                <button type="button" 
                                        onclick="openCandidateModal({{ $sub->candidate->id }})"
                                        class="text-left group focus:outline-none cursor-pointer">
                                    <span class="text-sm font-bold text-slate-900 group-hover:text-teal-700 underline decoration-teal-500/40 underline-offset-2 transition-colors">
                                        {{ $sub->candidate->candidate_name }}
                                    </span>
                                </button>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-700 border border-teal-200" title="{{ $sub->candidate->discipline->discipline_name ?? '' }}">
                                    {{ $sub->candidate->discipline->code ?? 'Discipline' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $sub->category->name ?? 'Category' }}
                                </span>
                            </div>
                            <span class="text-xs text-slate-400">
                                Submitted {{ $sub->created_at->format('d M Y, h:i A') }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                            {{ $sub->comment ?? $sub->comments }}
                        </p>
                        @if($sub->reference_1_name || $sub->reference_2_name)
                            <div class="mt-2 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-200 space-y-1 text-slate-600">
                                @if($sub->reference_1_name)
                                    <div>
                                        <strong class="text-[#302556] font-semibold">Ref 1:</strong>
                                        {{ $sub->reference_1_name }} ({{ $sub->reference_1_designation }}, {{ $sub->reference_1_organisation }}) &bull; {{ $sub->reference_1_email }} &bull; {{ $sub->reference_1_contact_number }}
                                    </div>
                                @endif
                                @if($sub->reference_2_name)
                                    <div>
                                        <strong class="text-[#302556] font-semibold">Ref 2:</strong>
                                        {{ $sub->reference_2_name }} ({{ $sub->reference_2_designation }}, {{ $sub->reference_2_organisation }}) &bull; {{ $sub->reference_2_email }} &bull; {{ $sub->reference_2_contact_number }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($sub->supportingDocuments->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($sub->supportingDocuments as $doc)
                                    <a href="{{ route('due-diligence.download', $doc) }}" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 transition border border-teal-200">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ $doc->original_filename }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Candidate Select & Search for Due Diligence --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Select Candidate to Submit Due Diligence</h2>
            <p class="text-xs text-slate-500 mt-0.5">Filter by discipline or search candidate name to launch the due diligence form.</p>

            {{-- Filter form --}}
            <form method="GET" action="{{ route('due-diligence.index') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-6">
                    <input type="text" 
                           name="q" 
                           value="{{ $filters['q'] ?? '' }}" 
                           placeholder="Search candidate name or institution..." 
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="sm:col-span-4">
                    <select name="discipline_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All 8 Disciplines</option>
                        @foreach($disciplines as $disc)
                            <option value="{{ $disc->id }}" {{ (string)($filters['discipline_id'] ?? '') === (string)$disc->id ? 'selected' : '' }}>
                                {{ $disc->discipline_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex space-x-2">
                    <button type="submit" class="w-full py-2 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Filter
                    </button>
                    @if(!empty($filters['q']) || !empty($filters['discipline_id']))
                        <a href="{{ route('due-diligence.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Clear">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Candidates Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Candidate</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Discipline</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Organisation</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Past Comments</th>
                        <th scope="col" class="px-6 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($candidates as $candidate)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center space-x-3">
                                    @if($candidate->photo_path)
                                        <img src="{{ $candidate->photo_path }}" 
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($candidate->candidate_name) }}&background=0D9488&color=fff&size=128';"
                                             alt="{{ $candidate->candidate_name }}" 
                                             class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openCandidateModal({{ $candidate->id }})"
                                             title="Click to view candidate info">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-teal-800 text-teal-200 flex items-center justify-center font-bold text-xs cursor-pointer shadow-xs hover:bg-teal-700 transition-colors"
                                             onclick="openCandidateModal({{ $candidate->id }})"
                                             title="Click to view candidate info">
                                            {{ substr($candidate->candidate_name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <button type="button" 
                                                onclick="openCandidateModal({{ $candidate->id }})"
                                                class="text-left group focus:outline-none cursor-pointer">
                                            <span class="font-bold text-slate-900 group-hover:text-teal-700 text-sm block leading-tight underline decoration-teal-500/40 underline-offset-2 transition-colors">
                                                {{ $candidate->candidate_name }}
                                            </span>
                                            <span class="text-slate-500 text-[11px] block mt-0.5">
                                                {{ $candidate->candidate_title ?? $candidate->sub_discipline ?? 'General' }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-800 border border-teal-200" title="{{ $candidate->discipline->discipline_name ?? '' }}">
                                    {{ $candidate->discipline->code ?? 'Discipline' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-800 font-medium">{{ $candidate->organisation }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $candidate->due_diligence_submissions_count ?? $candidate->dueDiligenceSubmissions()->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <button type="button" 
                                            onclick="openCandidateModal({{ $candidate->id }})"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Info
                                    </button>
                                    <a href="{{ route('due-diligence.create', $candidate) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition shadow-xs">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Submit Due Diligence
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No candidates match the specified criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $candidates->links() }}
        </div>
    </div>
</div>
@endsection
