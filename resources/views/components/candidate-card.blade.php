@props([
    'candidate',
    'mode' => 'public', // 'public' or 'admin'
    'showActions' => true,
    'layout' => 'card', // 'card' or 'full'
])

@if($layout === 'full')
    {{-- FULL PROFILE LAYOUT (For single candidate discipline receipts, spotlights, and dedicated profiles) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Profile Header: Picture (fixed portrait), Name, Designation, Discipline, Affiliation, Dossier Link --}}
        <div class="p-6 sm:p-8 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row items-center sm:items-start gap-6">
            {{-- 1. PICTURE (Fixed portrait frame, never stretched or distorted) --}}
            <div class="flex-shrink-0">
                @if($candidate->photo_url)
                    <img src="{{ $candidate->photo_url }}" 
                         alt="{{ $candidate->candidate_name }}" 
                         class="w-36 sm:w-44 h-48 sm:h-56 rounded-2xl object-cover object-top border-2 border-slate-200 shadow-md bg-slate-100 cursor-pointer hover:opacity-95 transition-opacity"
                         onclick="openCandidateModal({{ $candidate->id }})"
                         title="Click to view candidate info">
                @else
                    <div class="w-36 sm:w-44 h-48 sm:h-56 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 flex flex-col items-center justify-center border-2 border-slate-200 shadow-sm cursor-pointer"
                         onclick="openCandidateModal({{ $candidate->id }})"
                         title="Click to view candidate info">
                        <svg class="w-14 h-14 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-2">No Photo Provided</span>
                    </div>
                @endif
            </div>

            {{-- Profile Meta Details --}}
            <div class="flex-1 min-w-0 text-center sm:text-left space-y-3">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    {{-- 4. NOMINATED DISCIPLINE --}}
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                        {{ $candidate->discipline->discipline_name ?? 'Not provided' }}
                    </span>

                    @if($mode === 'admin')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $candidate->active ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                            {{ $candidate->active ? 'Active' : 'Inactive' }}
                        </span>
                    @endif

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                        Candidate ID #{{ $candidate->id }}
                    </span>
                </div>

                {{-- 2. FULL NAME --}}
                <div>
                    <button type="button" 
                            onclick="openCandidateModal({{ $candidate->id }})" 
                            class="text-center sm:text-left group/title focus:outline-none cursor-pointer">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 group-hover/title:text-teal-700 leading-snug transition-colors">
                            {{ $candidate->candidate_name }}
                        </h3>
                    </button>

                    {{-- 3. TITLE / DESIGNATION --}}
                    <p class="text-sm sm:text-base font-semibold text-teal-700 mt-1 leading-snug">
                        {{ $candidate->candidate_title ?: 'Not provided' }}
                    </p>
                    @if($candidate->organisation)
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            {{ $candidate->organisation }}
                        </p>
                    @endif
                </div>

                {{-- 5. AFFILIATION TO ASM --}}
                <div class="pt-2 border-t border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Affiliation to ASM</span>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 whitespace-pre-line leading-relaxed">
                        {{ $candidate->affiliation_to_asm ?: 'Not provided' }}
                    </p>
                </div>

                {{-- 6. ONEDRIVE DOSSIER LINK (URL) --}}
                <div class="pt-1">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">OneDrive Dossier Link</span>
                    @if($candidate->nomination_form_url)
                        <a href="{{ $candidate->nomination_form_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors group/btn">
                            <svg class="w-4 h-4 mr-2 text-blue-200 group-hover/btn:text-white transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span>View OneDrive Dossier</span>
                            <svg class="w-3.5 h-3.5 ml-1.5 opacity-70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100 rounded-xl border border-slate-200">
                            Not provided
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Full Content Body (Fields 7, 8, 9 - 100% visible, zero truncation/clamping) --}}
        <div class="p-6 sm:p-8 space-y-6 bg-white">
            {{-- 7. AREAS OF EXPERTISE --}}
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Areas of Expertise
                </span>
                <div class="text-xs sm:text-sm text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-200 font-medium whitespace-pre-line leading-relaxed">
                    {{ $candidate->area_of_expertise ?: 'Not provided' }}
                </div>
            </div>

            {{-- 8. QUALIFICATIONS / PROFESSIONAL MEMBERSHIPS --}}
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Qualifications / Professional Memberships
                </span>
                <div class="text-xs sm:text-sm text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-200 whitespace-pre-line leading-relaxed">
                    {{ $candidate->qualifications_professional_memberships ?: 'Not provided' }}
                </div>
            </div>

            {{-- 9. BASIS OF RECOMMENDATION --}}
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Basis of Recommendation
                </span>
                <div class="text-xs sm:text-sm text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-200 whitespace-pre-line leading-relaxed">
                    {{ $candidate->basis_of_recommendation ?: 'Not provided' }}
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        @if($showActions)
            <div class="bg-slate-50 px-6 sm:px-8 py-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    @if($mode === 'admin')
                        <form method="POST" action="{{ route('admin.candidates.toggle-active', $candidate) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $candidate->active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }} transition">
                                {{ $candidate->active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.candidates.edit', $candidate) }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-teal-700 bg-white border border-slate-300 rounded-xl hover:bg-teal-50 hover:border-teal-300 transition shadow-2xs">
                            Edit Candidate
                        </a>
                    @else
                        <a href="{{ route('due-diligence.create', $candidate) }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-xs transition">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Submit Due Diligence Assessment
                        </a>
                        <a href="{{ route('candidates.show', $candidate) }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 rounded-xl shadow-2xs transition">
                            Full Dossier & Profile
                            <svg class="w-3.5 h-3.5 ml-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @endif
                </div>

                <button type="button" 
                        onclick="openCandidateModal({{ $candidate->id }})" 
                        class="inline-flex items-center text-xs font-bold text-teal-700 hover:text-teal-800 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Quick Modal View
                </button>
            </div>
        @endif
    </div>
@else
    {{-- STANDARD CARD LAYOUT (For multi-candidate grids in Directory / Admin) --}}
    <div class="h-full flex flex-col justify-between bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-teal-400/60 hover:shadow-md transition-all overflow-hidden group">
        <div>
            {{-- 1. PICTURE --}}
            <div class="relative w-full h-56 bg-slate-100 overflow-hidden flex items-center justify-center border-b border-slate-100">
                @if($candidate->photo_url)
                    <img src="{{ $candidate->photo_url }}" 
                         alt="{{ $candidate->candidate_name }}" 
                         class="w-full h-full object-cover object-top transition duration-300 group-hover:scale-105 cursor-pointer"
                         onclick="openCandidateModal({{ $candidate->id }})"
                         title="Click to view candidate info">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 cursor-pointer"
                         onclick="openCandidateModal({{ $candidate->id }})"
                         title="Click to view candidate info">
                        <svg class="w-16 h-16 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="text-[11px] font-semibold text-slate-400 mt-1 uppercase tracking-wider">No Photo Provided</span>
                    </div>
                @endif

                {{-- Candidate ID & Active badge overlay --}}
                <div class="absolute top-3 right-3 flex items-center gap-1.5">
                    @if($mode === 'admin')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold shadow-xs {{ $candidate->active ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                            {{ $candidate->active ? 'Active' : 'Inactive' }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/70 text-white backdrop-blur-xs shadow-xs">
                        ID #{{ $candidate->id }}
                    </span>
                </div>
            </div>

            {{-- Card Content Body --}}
            <div class="p-5 space-y-3.5">
                {{-- 2. FULL NAME --}}
                <div>
                    <button type="button" 
                            onclick="openCandidateModal({{ $candidate->id }})" 
                            class="text-left w-full group/title focus:outline-none cursor-pointer">
                        <h3 class="text-base font-bold text-slate-900 group-hover/title:text-teal-700 leading-snug line-clamp-2 transition-colors" title="{{ $candidate->candidate_name }}">
                            {{ $candidate->candidate_name }}
                        </h3>
                    </button>

                    {{-- 3. TITLE / DESIGNATION --}}
                    <p class="text-xs font-semibold text-teal-700 mt-0.5 line-clamp-1" title="{{ $candidate->candidate_title }}">
                        {{ $candidate->candidate_title ?: 'Not provided' }}
                    </p>
                    @if($candidate->organisation)
                        <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                            {{ $candidate->organisation }}
                        </p>
                    @endif
                </div>

                {{-- 4. NOMINATED DISCIPLINE --}}
                <div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200 leading-tight max-w-full truncate" title="{{ $candidate->discipline->discipline_name ?? 'Not provided' }}">
                        {{ $candidate->discipline->discipline_name ?? 'Not provided' }}
                    </span>
                </div>

                {{-- 5. AFFILIATION TO ASM --}}
                <div class="pt-2 border-t border-slate-100">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Affiliation to ASM</span>
                    <p class="text-xs font-semibold text-slate-800 whitespace-pre-line leading-relaxed">
                        {{ $candidate->affiliation_to_asm ?: 'Not provided' }}
                    </p>
                </div>

                {{-- 6. ONEDRIVE DOSSIER LINK (URL) --}}
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">OneDrive Dossier Link</span>
                    @if($candidate->nomination_form_url)
                        <a href="{{ $candidate->nomination_form_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors group/btn">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-blue-200 group-hover/btn:text-white transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span class="truncate">View OneDrive Dossier</span>
                            <svg class="w-3 h-3 ml-1 opacity-70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <div class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-medium text-slate-400 bg-slate-50 rounded-xl border border-slate-200 cursor-default">
                            <span>Not provided</span>
                        </div>
                    @endif
                </div>

                {{-- 7. AREAS OF EXPERTISE --}}
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Areas of Expertise</span>
                    <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100" title="{{ $candidate->area_of_expertise }}">
                        <p class="line-clamp-2 leading-snug whitespace-pre-line">
                            {{ $candidate->area_of_expertise ?: 'Not provided' }}
                        </p>
                    </div>
                </div>

                {{-- 8. QUALIFICATIONS / PROFESSIONAL MEMBERSHIPS --}}
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Qualifications / Professional Memberships</span>
                    <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100" title="{{ $candidate->qualifications_professional_memberships }}">
                        <p class="line-clamp-2 leading-snug whitespace-pre-line">
                            {{ $candidate->qualifications_professional_memberships ?: 'Not provided' }}
                        </p>
                    </div>
                </div>

                {{-- 9. BASIS OF RECOMMENDATION --}}
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Basis of Recommendation</span>
                    <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100" title="{{ $candidate->basis_of_recommendation }}">
                        <p class="line-clamp-3 leading-snug whitespace-pre-line">
                            {{ $candidate->basis_of_recommendation ?: 'Not provided' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Footer Action Buttons --}}
        @if($showActions)
            <div class="bg-slate-50 px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                <button type="button" 
                        onclick="openCandidateModal({{ $candidate->id }})" 
                        class="inline-flex items-center text-xs font-bold text-teal-700 hover:text-teal-800 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Quick View
                </button>

                @if($mode === 'admin')
                    <div class="flex items-center space-x-2">
                        <form method="POST" action="{{ route('admin.candidates.toggle-active', $candidate) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $candidate->active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }} transition">
                                {{ $candidate->active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.candidates.edit', $candidate) }}" class="inline-flex items-center px-3 py-1 text-xs font-bold text-teal-700 bg-white border border-slate-300 rounded-lg hover:bg-teal-50 hover:border-teal-300 transition shadow-2xs">
                            Edit
                        </a>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('due-diligence.create', $candidate) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 hover:border-slate-400 transition shadow-2xs" title="Provide Due Diligence">
                            <svg class="w-3.5 h-3.5 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Due Diligence
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
