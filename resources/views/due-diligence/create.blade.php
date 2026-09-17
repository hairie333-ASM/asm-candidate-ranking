@extends('layouts.app')

@section('title', 'Submit Due Diligence - ' . $candidate->candidate_name)

@section('content')
<div x-data="{}" class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb --}}
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('due-diligence.index') }}" class="hover:text-slate-800">Due Diligence</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">Due Diligence Submission</span>
    </nav>

    {{-- Candidate Profile Summary Banner --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            @if($candidate->photo_path)
                <img src="{{ $candidate->photo_path }}" alt="{{ $candidate->candidate_name }}" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($candidate->candidate_name) }}&background=0D9488&color=fff&size=128';"
                     class="w-16 h-16 rounded-xl object-cover border border-slate-200 shadow-xs flex-shrink-0 cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="openCandidateModal({{ $candidate->id }})"
                     title="Click to view candidate info">
            @else
                <div class="w-16 h-16 rounded-xl bg-teal-800 text-teal-200 flex items-center justify-center font-bold text-xl flex-shrink-0 cursor-pointer hover:bg-teal-700 transition-colors"
                     onclick="openCandidateModal({{ $candidate->id }})"
                     title="Click to view candidate info">
                    {{ substr($candidate->candidate_name, 0, 2) }}
                </div>
            @endif
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                        {{ $candidate->discipline->discipline_name ?? 'Discipline' }}
                    </span>
                    <span class="text-xs text-slate-400">Candidate ID #{{ $candidate->id }}</span>
                </div>
                <button type="button" 
                        onclick="openCandidateModal({{ $candidate->id }})"
                        class="text-left group focus:outline-none block cursor-pointer">
                    <h1 class="text-xl font-bold text-slate-900 group-hover:text-teal-700 underline decoration-teal-500/40 underline-offset-2 transition-colors">
                        {{ $candidate->candidate_name }}
                    </h1>
                </button>
                <p class="text-xs text-slate-600 mt-0.5">
                    {{ $candidate->current_position ?? $candidate->candidate_title }} &bull; {{ $candidate->organisation }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-end flex-shrink-0">
                <button type="button" 
                        onclick="openCandidateModal({{ $candidate->id }})"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Candidate Info
                </button>
                @if($candidate->onedrive_link)
                    <a href="{{ $candidate->onedrive_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View OneDrive Dossier
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Submission Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-[#302556] px-6 py-5 text-white border-b border-[#008442]/30">
            <h2 class="text-lg font-bold">Confidential Due Diligence Form</h2>
            <p class="text-xs text-emerald-300 mt-0.5">
                All comments and references submitted are securely archived and accessible only to authorized ASM Council & Administrators.
            </p>
        </div>

        <form method="POST" action="{{ route('due-diligence.store') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">

            {{-- Category Select --}}
            <div>
                <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Due Diligence Category <span class="text-[#E31F21] font-bold">*</span>
                </label>
                <select name="category_id" 
                        id="category_id" 
                        required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('category_id') border-[#E31F21] @enderror">
                    <option value="">-- Select Due Diligence Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} {{ $cat->description ? '- ' . $cat->description : '' }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Comments Textarea --}}
            <div>
                <label for="comment" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Due Diligence Comments & Observations <span class="text-[#E31F21] font-bold">*</span>
                </label>
                <textarea name="comment" 
                          id="comment" 
                          rows="5" 
                          required
                          placeholder="Provide detailed, objective commentary regarding this candidate's scientific standing, academic integrity, leadership contributions, conflicts of interest, or other relevant observations..."
                          class="w-full p-4 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] leading-relaxed @error('comment') border-[#E31F21] @enderror">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-slate-400 mt-1.5">
                    Minimum 5 characters. Be specific and factual. Opinions should be grounded in verifiable academic or professional merits.
                </p>
            </div>

            {{-- Professional References Section (Desktop 2-Col / Mobile Stacked) --}}
            <div class="border-t border-slate-200 pt-6">
                <div class="mb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#302556] mr-2"></span>
                        Professional References
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Please provide professional references who can substantiate or verify the candidate's standing and achievements.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Column 1: REFERENCE 1 (Required) --}}
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#302556] flex items-center">
                                <span class="w-2 h-2 rounded-full bg-[#E31F21] mr-1.5"></span>
                                Reference 1
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-[#E31F21] border border-rose-200 uppercase">
                                Mandatory
                            </span>
                        </div>

                        {{-- Ref 1 Name --}}
                        <div>
                            <label for="reference_1_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Full Name <span class="text-[#E31F21] font-bold">*</span>
                            </label>
                            <input type="text" 
                                   name="reference_1_name" 
                                   id="reference_1_name" 
                                   required 
                                   value="{{ old('reference_1_name') }}"
                                   placeholder="e.g. Prof. Dr. Ahmad bin Abdullah"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_1_name') border-[#E31F21] @enderror">
                            @error('reference_1_name')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 1 Designation --}}
                        <div>
                            <label for="reference_1_designation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Designation / Title <span class="text-[#E31F21] font-bold">*</span>
                            </label>
                            <input type="text" 
                                   name="reference_1_designation" 
                                   id="reference_1_designation" 
                                   required 
                                   value="{{ old('reference_1_designation') }}"
                                   placeholder="e.g. Dean / Senior Principal Scientist"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_1_designation') border-[#E31F21] @enderror">
                            @error('reference_1_designation')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 1 Organisation --}}
                        <div>
                            <label for="reference_1_organisation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Organisation / Institution <span class="text-[#E31F21] font-bold">*</span>
                            </label>
                            <input type="text" 
                                   name="reference_1_organisation" 
                                   id="reference_1_organisation" 
                                   required 
                                   value="{{ old('reference_1_organisation') }}"
                                   placeholder="e.g. Universiti Malaya / SIRIM"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_1_organisation') border-[#E31F21] @enderror">
                            @error('reference_1_organisation')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 1 Contact Number --}}
                        <div>
                            <label for="reference_1_contact_number" class="block text-xs font-semibold text-slate-700 mb-1">
                                Contact Number <span class="text-[#E31F21] font-bold">*</span>
                            </label>
                            <input type="text" 
                                   name="reference_1_contact_number" 
                                   id="reference_1_contact_number" 
                                   required 
                                   value="{{ old('reference_1_contact_number') }}"
                                   placeholder="e.g. +60 12-345 6789"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_1_contact_number') border-[#E31F21] @enderror">
                            @error('reference_1_contact_number')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 1 Email --}}
                        <div>
                            <label for="reference_1_email" class="block text-xs font-semibold text-slate-700 mb-1">
                                Email Address <span class="text-[#E31F21] font-bold">*</span>
                            </label>
                            <input type="email" 
                                   name="reference_1_email" 
                                   id="reference_1_email" 
                                   required 
                                   value="{{ old('reference_1_email') }}"
                                   placeholder="e.g. reference1@institution.edu.my"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_1_email') border-[#E31F21] @enderror">
                            @error('reference_1_email')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Column 2: REFERENCE 2 (Optional) --}}
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-slate-400 mr-1.5"></span>
                                Reference 2
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600 uppercase">
                                Optional
                            </span>
                        </div>

                        {{-- Ref 2 Name --}}
                        <div>
                            <label for="reference_2_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Full Name <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="reference_2_name" 
                                   id="reference_2_name" 
                                   value="{{ old('reference_2_name') }}"
                                   placeholder="e.g. Dr. Siti Aminah"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_2_name') border-[#E31F21] @enderror">
                            @error('reference_2_name')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 2 Designation --}}
                        <div>
                            <label for="reference_2_designation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Designation / Title <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="reference_2_designation" 
                                   id="reference_2_designation" 
                                   value="{{ old('reference_2_designation') }}"
                                   placeholder="e.g. Vice Chancellor / Director"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_2_designation') border-[#E31F21] @enderror">
                            @error('reference_2_designation')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 2 Organisation --}}
                        <div>
                            <label for="reference_2_organisation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Organisation / Institution <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="reference_2_organisation" 
                                   id="reference_2_organisation" 
                                   value="{{ old('reference_2_organisation') }}"
                                   placeholder="e.g. Universiti Kebangsaan Malaysia"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_2_organisation') border-[#E31F21] @enderror">
                            @error('reference_2_organisation')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 2 Contact Number --}}
                        <div>
                            <label for="reference_2_contact_number" class="block text-xs font-semibold text-slate-700 mb-1">
                                Contact Number <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="reference_2_contact_number" 
                                   id="reference_2_contact_number" 
                                   value="{{ old('reference_2_contact_number') }}"
                                   placeholder="e.g. +60 19-876 5432"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_2_contact_number') border-[#E31F21] @enderror">
                            @error('reference_2_contact_number')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ref 2 Email --}}
                        <div>
                            <label for="reference_2_email" class="block text-xs font-semibold text-slate-700 mb-1">
                                Email Address <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
                            </label>
                            <input type="email" 
                                   name="reference_2_email" 
                                   id="reference_2_email" 
                                   value="{{ old('reference_2_email') }}"
                                   placeholder="e.g. reference2@institution.edu.my"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556] @error('reference_2_email') border-[#E31F21] @enderror">
                            @error('reference_2_email')
                                <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supporting Documents (Multiple) --}}
            <div>
                <label for="documents" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Supporting Documents (Optional)
                </label>
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-[#008442] bg-slate-50 transition">
                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <input type="file" 
                           name="documents[]" 
                           id="documents" 
                           multiple 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-[#008442] hover:file:bg-emerald-100 cursor-pointer">
                    <p class="text-[11px] text-slate-500 mt-2">
                        Accepted formats: PDF, Word (DOC/DOCX), Excel, PowerPoint, JPEG, PNG. Maximum 10MB per file (Up to 5 files).
                    </p>
                </div>
                @error('documents')
                    <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                @enderror
                @error('documents.*')
                    <p class="text-xs text-[#E31F21] mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confidentiality Disclaimer --}}
            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start space-x-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="leading-relaxed">
                    <strong>Confidentiality Assurance:</strong> By submitting this due diligence observation, you confirm that your input is provided in good faith in accordance with the Academy of Sciences Malaysia Fellowship evaluation code of ethics. All submissions are encrypted and stored in private storage.
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
                <a href="{{ route('due-diligence.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#302556] hover:bg-[#241c42] shadow-sm transition inline-flex items-center cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Due Diligence Assessment
                </button>
        </form>
    </div>
</div>
@endsection
