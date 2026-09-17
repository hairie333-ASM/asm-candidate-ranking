@extends('layouts.app')

@section('title', $candidate->candidate_name . ' - Candidate Dossier')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb --}}
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('candidates.index') }}" class="hover:text-slate-800">Candidates</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">{{ $candidate->candidate_name }}</span>
    </nav>

    {{-- Main Standardized Profile Card (9 Fields in Order) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
        {{-- Top Header Section: 1. PICTURE, 2. FULL NAME, 3. TITLE / DESIGNATION, 4. NOMINATED DISCIPLINE --}}
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 p-6 sm:p-8 text-white">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                {{-- 1. PICTURE --}}
                @if($candidate->photo_url)
                    <img src="{{ $candidate->photo_url }}" 
                         alt="{{ $candidate->candidate_name }}" 
                         class="w-32 h-40 rounded-2xl object-cover object-top border-4 border-teal-500/40 shadow-xl flex-shrink-0">
                @else
                    <div class="w-32 h-40 rounded-2xl bg-gradient-to-br from-slate-800 to-teal-900 text-slate-300 flex flex-col items-center justify-center border-4 border-teal-500/40 shadow-xl flex-shrink-0">
                        <svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-300 mt-1">No Photo</span>
                    </div>
                @endif

                <div class="flex-1 text-center sm:text-left space-y-2">
                    {{-- 4. NOMINATED DISCIPLINE (Pill) --}}
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-300 border border-teal-400/30">
                            {{ $candidate->discipline->discipline_name ?? 'Discipline' }}
                        </span>
                    </div>

                    {{-- 2. FULL NAME --}}
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ $candidate->candidate_name }}
                    </h1>

                    {{-- 3. TITLE / DESIGNATION --}}
                    <p class="text-teal-300 font-semibold text-sm sm:text-base">
                        {{ $candidate->candidate_title ?: 'Not provided' }}
                    </p>
                    @if($candidate->organisation)
                        <p class="text-xs text-slate-300">
                            {{ $candidate->organisation }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Profile Fields Body: Exact Fields 5 to 9 in Order --}}
        <div class="p-6 sm:p-8 space-y-6">
            {{-- 5. AFFILIATION TO ASM --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Affiliation to ASM</h2>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $candidate->affiliation_to_asm ?: 'Not provided' }}
                    </p>
                </div>
            </div>

            {{-- 6. ONEDRIVE DOSSIER LINK (URL) --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">OneDrive Dossier Link</h2>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    @if($candidate->nomination_form_url)
                        <a href="{{ $candidate->nomination_form_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm group">
                            <svg class="w-4 h-4 mr-2 text-blue-200 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            View OneDrive Dossier
                            <svg class="w-3.5 h-3.5 ml-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <span class="text-xs text-slate-400 font-medium">Not provided</span>
                    @endif
                </div>
            </div>

            {{-- 7. AREAS OF EXPERTISE --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Areas of Expertise</h2>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-sm text-slate-800 whitespace-pre-line leading-relaxed font-medium">
                    {{ $candidate->area_of_expertise ?: 'Not provided' }}
                </div>
            </div>

            {{-- 8. QUALIFICATIONS / PROFESSIONAL MEMBERSHIPS --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Qualifications / Professional Memberships</h2>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                    {{ $candidate->qualifications_professional_memberships ?: 'Not provided' }}
                </div>
            </div>

            {{-- 9. BASIS OF RECOMMENDATION --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Basis of Recommendation</h2>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                    {{ $candidate->basis_of_recommendation ?: 'Not provided' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Due Diligence Submissions Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Due Diligence Observations</h2>
                <p class="text-xs text-slate-500">
                    Confidential evaluations and observations submitted by ASM Fellows and voters.
                </p>
            </div>
            <a href="{{ route('due-diligence.create', $candidate) }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                + Submit Due Diligence
            </a>
        </div>

        @if($candidate->dueDiligenceSubmissions->isEmpty())
            <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <p class="text-xs text-slate-500">No due diligence comments submitted yet for this candidate.</p>
                <a href="{{ route('due-diligence.create', $candidate) }}" class="inline-flex items-center mt-3 text-xs font-semibold text-teal-700 hover:underline">
                    Submit the first due diligence entry &rarr;
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($candidate->dueDiligenceSubmissions as $submission)
                    <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                    {{ $submission->category->name ?? 'General Due Diligence' }}
                                </span>
                                @if(Auth::check() && Auth::user()->isAdmin())
                                    <span class="text-xs text-slate-500 font-medium">
                                        Submitted by {{ $submission->user->name ?? 'Voter' }} ({{ $submission->user->discipline->name ?? 'General' }})
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-400">
                                {{ $submission->created_at->format('d M Y, h:i A') }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $submission->comment ?? $submission->comments }}
                        </p>
                        @if($submission->reference_1_name || $submission->reference_2_name)
                            <div class="mt-3 text-xs bg-white p-3 rounded-xl border border-slate-200 space-y-1.5 text-slate-700">
                                @if($submission->reference_1_name)
                                    <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-[#302556] flex-shrink-0">
                                            Reference 1
                                        </span>
                                        <span>
                                            <strong class="font-bold text-slate-900">{{ $submission->reference_1_name }}</strong> &bull;
                                            {{ $submission->reference_1_designation }}, {{ $submission->reference_1_organisation }} &bull;
                                            <a href="mailto:{{ $submission->reference_1_email }}" class="text-blue-600 hover:underline">{{ $submission->reference_1_email }}</a> &bull;
                                            {{ $submission->reference_1_contact_number }}
                                        </span>
                                    </div>
                                @endif
                                @if($submission->reference_2_name)
                                    <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 pt-1.5 border-t border-slate-100">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 flex-shrink-0">
                                            Reference 2
                                        </span>
                                        <span>
                                            <strong class="font-bold text-slate-900">{{ $submission->reference_2_name }}</strong> &bull;
                                            {{ $submission->reference_2_designation }}, {{ $submission->reference_2_organisation }} &bull;
                                            <a href="mailto:{{ $submission->reference_2_email }}" class="text-blue-600 hover:underline">{{ $submission->reference_2_email }}</a> &bull;
                                            {{ $submission->reference_2_contact_number }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($submission->supportingDocuments && $submission->supportingDocuments->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap gap-2">
                                @foreach($submission->supportingDocuments as $doc)
                                    <a href="{{ route('due-diligence.download', $doc) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ $doc->original_filename }} ({{ round($doc->file_size_bytes / 1024) }} KB)
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
