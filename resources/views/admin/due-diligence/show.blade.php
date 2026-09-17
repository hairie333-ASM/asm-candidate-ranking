@extends('layouts.app')

@section('title', 'Due Diligence Details #' . $submission->id)

@section('content')
<div x-data="{}" class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header & Breadcrumbs --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <a href="{{ route('admin.due-diligence.index') }}" class="hover:text-slate-800">Due Diligence Review</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Submission #{{ $submission->id }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                Due Diligence Review &bull; #{{ $submission->id }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                Submitted on {{ $submission->created_at->format('l, d F Y \a\t h:i A') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.due-diligence.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to All Submissions
            </a>
            <a href="{{ route('candidates.show', $submission->candidate) }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                View Full Candidate Profile
            </a>

            <form method="POST" 
                  action="{{ route('admin.due-diligence.destroy', $submission) }}" 
                  onsubmit="return confirm('Are you sure you want to permanently delete this due diligence submission (#{{ $submission->id }}) for candidate {{ addslashes($submission->candidate->candidate_name) }}? All attached documents and feedback notes will be deleted. This action cannot be undone.');" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Submission
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Left Column: Candidate & Submitter Metadata --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Candidate Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-3">Target Nominee</span>
                <div class="flex flex-col items-center text-center pb-4 border-b border-slate-100">
                    <div class="w-28 h-36 rounded-2xl overflow-hidden border-2 border-white shadow-md bg-slate-100 mb-3 flex-shrink-0">
                        @if($submission->candidate->photo_url)
                            <img src="{{ $submission->candidate->photo_url }}" 
                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($submission->candidate->candidate_name) }}&background=0D9488&color=fff&size=256';"
                                 alt="{{ $submission->candidate->candidate_name }}" 
                                 class="w-full h-full object-cover object-top">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-200 text-slate-400">
                                <svg class="w-10 h-10 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <span class="text-[9px] font-semibold uppercase mt-1">No Photo</span>
                            </div>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">
                        {{ $submission->candidate->candidate_name }}
                    </h3>
                    <p class="text-xs font-semibold text-teal-700 mt-0.5">
                        {{ $submission->candidate->candidate_title ?: 'Not provided' }}
                    </p>
                    @if($submission->candidate->organisation)
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            {{ $submission->candidate->organisation }}
                        </p>
                    @endif
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                            {{ $submission->candidate->discipline->discipline_name ?? 'Discipline' }}
                        </span>
                    </div>
                </div>

                <div class="pt-4 text-xs space-y-2.5">
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Affiliation to ASM</span>
                        <p class="font-medium text-slate-800 mt-0.5 whitespace-pre-line">{{ $submission->candidate->affiliation_to_asm ?: 'Not provided' }}</p>
                    </div>
                    @if($submission->candidate->nomination_form_url)
                        <div class="pt-2 border-t border-slate-100">
                            <a href="{{ $submission->candidate->nomination_form_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                                <span>Open OneDrive Nomination Dossier</span>
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Submitter Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-3">Submitter Information</span>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Name</span>
                        <span class="font-bold text-slate-900 text-sm block">{{ $submission->user->name ?? 'Deleted User' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Email</span>
                        <a href="mailto:{{ $submission->user->email ?? '' }}" class="font-medium text-blue-600 hover:underline block">{{ $submission->user->email ?? 'N/A' }}</a>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Discipline Assignment</span>
                        <span class="font-semibold text-slate-800 block">
                            {{ $submission->userDiscipline->discipline_name ?? ($submission->user->discipline->discipline_name ?? 'Cross-Discipline / Admin') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">User Role</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 mt-0.5">
                            {{ ucfirst(str_replace('_', ' ', $submission->user->role ?? 'User')) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Observations, References & Supporting Documents --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Category & Observations Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Due Diligence Category</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-300 mt-1">
                            {{ $submission->category->name ?? 'General Assessment' }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Confidential Record</span>
                        <span class="text-xs text-slate-500 font-mono">DD-REC-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">Observations & Feedback Details</span>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 text-sm leading-relaxed whitespace-pre-line font-medium">
                        {{ $submission->comment }}
                    </div>
                </div>
            </div>

            {{-- Professional References Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Contactable References
                </h3>

                @if($submission->reference_1_name || $submission->reference_2_name)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Reference 1 --}}
                        @if($submission->reference_1_name)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Reference 1
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $submission->reference_1_name }}</h4>
                                    <p class="text-xs text-slate-600 font-medium">{{ $submission->reference_1_designation }}</p>
                                    <p class="text-xs text-slate-500">{{ $submission->reference_1_organisation }}</p>
                                </div>
                                <div class="pt-2 border-t border-slate-200/80 text-xs space-y-1">
                                    @if($submission->reference_1_email)
                                        <div class="flex items-center text-slate-700">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <a href="mailto:{{ $submission->reference_1_email }}" class="text-blue-600 hover:underline">{{ $submission->reference_1_email }}</a>
                                        </div>
                                    @endif
                                    @if($submission->reference_1_contact_number)
                                        <div class="flex items-center text-slate-700">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <a href="tel:{{ $submission->reference_1_contact_number }}" class="hover:underline">{{ $submission->reference_1_contact_number }}</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Reference 2 --}}
                        @if($submission->reference_2_name)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">
                                        Reference 2
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $submission->reference_2_name }}</h4>
                                    <p class="text-xs text-slate-600 font-medium">{{ $submission->reference_2_designation }}</p>
                                    <p class="text-xs text-slate-500">{{ $submission->reference_2_organisation }}</p>
                                </div>
                                <div class="pt-2 border-t border-slate-200/80 text-xs space-y-1">
                                    @if($submission->reference_2_email)
                                        <div class="flex items-center text-slate-700">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <a href="mailto:{{ $submission->reference_2_email }}" class="text-blue-600 hover:underline">{{ $submission->reference_2_email }}</a>
                                        </div>
                                    @endif
                                    @if($submission->reference_2_contact_number)
                                        <div class="flex items-center text-slate-700">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <a href="tel:{{ $submission->reference_2_contact_number }}" class="hover:underline">{{ $submission->reference_2_contact_number }}</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs italic">
                        No contactable references were supplied for this submission.
                    </div>
                @endif
            </div>

            {{-- Supporting Documents Hub --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Supporting Documents & Evidence ({{ $submission->supportingDocuments->count() }})
                    </h3>

                    @if($submission->supportingDocuments->isNotEmpty())
                        <a href="{{ route('admin.due-diligence.download-zip', $submission) }}" 
                           class="inline-flex items-center px-3.5 py-1.5 bg-teal-50 hover:bg-teal-100 border border-teal-300 text-teal-800 rounded-xl text-xs font-bold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Download All ({{ $submission->supportingDocuments->count() }} Files .ZIP)</span>
                        </a>
                    @endif
                </div>

                @if($submission->supportingDocuments->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($submission->supportingDocuments as $doc)
                            @php
                                $ext = strtolower(pathinfo($doc->original_filename, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isPdf = $ext === 'pdf';
                                $sizeKb = round(($doc->file_size ?: 0) / 1024);
                            @endphp
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 hover:border-teal-300 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs uppercase flex-shrink-0 {{ $isPdf ? 'bg-rose-100 text-rose-700 border border-rose-200' : ($isImage ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-slate-200 text-slate-700 border border-slate-300') }}">
                                            {{ $ext ?: 'DOC' }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 text-sm truncate" title="{{ $doc->original_filename }}">
                                                {{ $doc->original_filename }}
                                            </h4>
                                            <div class="flex items-center text-slate-500 text-xs space-x-2 mt-0.5">
                                                <span>{{ $sizeKb }} KB</span>
                                                <span>&bull;</span>
                                                <span>{{ $doc->mime_type }}</span>
                                                <span>&bull;</span>
                                                <span>Uploaded {{ $doc->created_at->format('d M Y, h:i A') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200">
                                        <a href="{{ route('admin.due-diligence.document.preview', $doc) }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View / Open in Tab
                                        </a>
                                        <a href="{{ route('admin.due-diligence.document.download', $doc) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>

                                {{-- Inline Image Preview (for quick instant visibility) --}}
                                @if($isImage)
                                    <div class="mt-3 pt-3 border-t border-slate-200">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Image Preview</span>
                                        <div class="max-w-md rounded-xl overflow-hidden border border-slate-200 shadow-2xs bg-white">
                                            <img src="{{ route('admin.due-diligence.document.preview', $doc) }}" 
                                                 alt="{{ $doc->original_filename }}" 
                                                 class="w-full h-auto max-h-72 object-contain bg-slate-100">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                        <div class="w-12 h-12 rounded-xl bg-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-slate-700 text-sm">No Supporting Documents Attached</h4>
                        <p class="text-xs text-slate-500 mt-0.5">The submitter did not attach any additional files or evidence for this entry.</p>
                    </div>
                @endif
            </div>

            {{-- Danger Zone: Delete Submission --}}
            <div class="bg-rose-50/50 rounded-2xl border border-rose-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-rose-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Permanently Delete Submission
                        </h4>
                        <p class="text-xs text-rose-700 mt-1 max-w-lg">
                            Permanently removes this confidential due diligence record (#{{ $submission->id }}), references, and all associated evidence files from server storage. This action cannot be reversed.
                        </p>
                    </div>

                    <form method="POST" 
                          action="{{ route('admin.due-diligence.destroy', $submission) }}" 
                          onsubmit="return confirm('Are you sure you want to permanently delete this due diligence submission (#{{ $submission->id }}) for candidate {{ addslashes($submission->candidate->candidate_name) }}? All attached documents and feedback notes will be deleted. This action cannot be undone.');" 
                          class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
