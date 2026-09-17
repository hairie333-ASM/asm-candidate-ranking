@extends('layouts.app')

@section('title', 'Add Shortlisted Candidate')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
     x-data="{
         imagePreview: null,
         handleFileSelect(event) {
             const file = event.target.files[0];
             if (file) {
                 this.imagePreview = URL.createObjectURL(file);
             } else {
                 this.imagePreview = null;
             }
         }
     }">
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.candidates.index') }}" class="hover:text-slate-800">Candidates</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">Add Shortlisted Candidate</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 px-6 py-5 text-white">
            <h1 class="text-xl font-bold">Add Shortlisted Candidate</h1>
            <p class="text-xs text-teal-200 mt-0.5">Enter nominee details and dossier links following the standardized 9-field candidate structure.</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.candidates.store') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-7">
            @csrf

            {{-- 1. PICTURE --}}
            <div class="p-5 bg-slate-50/80 rounded-2xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">
                    1. Picture <span class="text-slate-400 font-normal lowercase">(optional)</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    {{-- Image Preview Container --}}
                    <div class="w-32 h-40 rounded-xl overflow-hidden bg-slate-200 border-2 border-dashed border-slate-300 flex items-center justify-center flex-shrink-0 shadow-2xs relative">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover object-top">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="flex flex-col items-center justify-center text-center p-2 text-slate-400">
                                <svg class="w-10 h-10 mb-1 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <span class="text-[10px] font-semibold uppercase tracking-wider">Placeholder</span>
                            </div>
                        </template>
                    </div>

                    {{-- Upload Controls & Specs --}}
                    <div class="flex-1 space-y-2">
                        <input type="file" 
                               name="photo" 
                               id="photo" 
                               accept=".jpg,.jpeg,.png,image/jpeg,image/png" 
                               @change="handleFileSelect($event)"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-800 file:cursor-pointer transition">
                        
                        <div class="text-[11px] text-slate-500 space-y-0.5">
                            <p class="font-semibold text-slate-700">Allowed formats: <span class="font-normal text-slate-600">JPG, JPEG, PNG (Max 5MB)</span></p>
                            <p class="font-semibold text-slate-700">Recommended aspect ratio: <span class="font-normal text-slate-600">Square (1:1) or 4:5 Portrait</span></p>
                            <p class="text-slate-400">A clean silhouette placeholder will automatically be used if no image is uploaded.</p>
                        </div>
                        @error('photo') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 2. FULL NAME --}}
            <div>
                <label for="candidate_name" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    2. Full Name <span class="text-rose-600">*</span>
                </label>
                <input type="text" 
                       name="candidate_name" 
                       id="candidate_name" 
                       required 
                       value="{{ old('candidate_name') }}" 
                       placeholder="e.g. Academician Tan Sri Dr. Ahmad Zaidi" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('candidate_name') border-rose-500 @enderror">
                <p class="text-[11px] text-slate-400 mt-1">Full legal or academic name as nominated.</p>
                @error('candidate_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. TITLE / DESIGNATION --}}
            <div>
                <label for="candidate_title" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    3. Title / Designation <span class="text-rose-600">*</span>
                </label>
                <input type="text" 
                       name="candidate_title" 
                       id="candidate_title" 
                       required 
                       value="{{ old('candidate_title') }}" 
                       placeholder="e.g. Professor of Molecular Biology / Senior Principal Consultant / Ir. Dr." 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('candidate_title') border-rose-500 @enderror">
                <p class="text-[11px] text-slate-400 mt-1">Examples: Professor, Academician, Ir., Dr., Ts., etc.</p>
                @error('candidate_title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 4. NOMINATED DISCIPLINE --}}
            <div>
                <label for="discipline_id" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    4. Nominated Discipline <span class="text-rose-600">*</span>
                </label>
                <select name="discipline_id" 
                        id="discipline_id" 
                        required 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('discipline_id') border-rose-500 @enderror">
                    <option value="">-- Select Nominated Discipline --</option>
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ old('discipline_id') == $disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Official Academy of Sciences Malaysia discipline master list.</p>
                @error('discipline_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 5. AFFILIATION TO ASM --}}
            <div>
                <label for="affiliation_to_asm" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    5. Affiliation to ASM <span class="text-rose-600">*</span>
                </label>
                <input type="text" 
                       name="affiliation_to_asm" 
                       id="affiliation_to_asm" 
                       required 
                       value="{{ old('affiliation_to_asm', 'ASM Fellow Nominee') }}" 
                       placeholder="e.g. TRP Recipient, Young Scientist Network (YSN-ASM), Task Force Member, None" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('affiliation_to_asm') border-rose-500 @enderror">
                <p class="text-[11px] text-slate-400 mt-1">Examples: TRP Recipient, Young Scientist Network (YSN-ASM), Task Force Member, None, etc.</p>
                @error('affiliation_to_asm') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 6. ONEDRIVE DOSSIER LINK (URL) --}}
            <div>
                <label for="nomination_form_url" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    6. OneDrive Dossier Link (URL)
                </label>
                <div class="relative">
                    <input type="url" 
                           name="nomination_form_url" 
                           id="nomination_form_url" 
                           value="{{ old('nomination_form_url') }}" 
                           placeholder="https://onedrive.live.com/... or https://akademisains-my.sharepoint.com/..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('nomination_form_url') border-rose-500 @enderror">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Validated secure link to nominee's full dossier folder. Opens in a new tab.</p>
                @error('nomination_form_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 7. AREAS OF EXPERTISE --}}
            <div>
                <label for="area_of_expertise" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    7. Areas of Expertise
                </label>
                <textarea name="area_of_expertise" 
                          id="area_of_expertise" 
                          rows="3" 
                          placeholder="e.g. Plant Genomics, Sustainable Crop Genetics, CRISPR-Cas9 technology in tropical agriculture..." 
                          class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('area_of_expertise') border-rose-500 @enderror">{{ old('area_of_expertise') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Specific scientific research fields, specializations, or technological domains (multi-line supported).</p>
                @error('area_of_expertise') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 8. QUALIFICATIONS / PROFESSIONAL MEMBERSHIPS --}}
            <div>
                <label for="qualifications_professional_memberships" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    8. Qualifications / Professional Memberships
                </label>
                <textarea name="qualifications_professional_memberships" 
                          id="qualifications_professional_memberships" 
                          rows="3" 
                          placeholder="e.g. PhD in Molecular Biology (Cambridge); Fellow, Institute of Materials Malaysia; Senior Member IEEE..." 
                          class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('qualifications_professional_memberships') border-rose-500 @enderror">{{ old('qualifications_professional_memberships') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Academic degrees, honorary fellowships, professional registrations, and memberships (multi-line supported).</p>
                @error('qualifications_professional_memberships') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 9. BASIS OF RECOMMENDATION --}}
            <div>
                <label for="basis_of_recommendation" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                    9. Basis of Recommendation
                </label>
                <textarea name="basis_of_recommendation" 
                          id="basis_of_recommendation" 
                          rows="5" 
                          placeholder="Principal scientific discoveries, national/international recognitions, citations, technological breakthroughs, and societal impacts justifying election as ASM Fellow..." 
                          class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 @error('basis_of_recommendation') border-rose-500 @enderror">{{ old('basis_of_recommendation') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Detailed justification from the nominating committee (multi-line supported).</p>
                @error('basis_of_recommendation') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Additional Administration Details --}}
            <div class="pt-6 border-t border-slate-200">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Additional Administration Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="organisation" class="block text-xs font-semibold text-slate-700 mb-1">Organisation / University</label>
                        <input type="text" 
                               name="organisation" 
                               id="organisation" 
                               value="{{ old('organisation') }}" 
                               placeholder="e.g. Universiti Malaya (UM)" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label for="display_order" class="block text-xs font-semibold text-slate-700 mb-1">Display Sort Order</label>
                        <input type="number" 
                               name="display_order" 
                               id="display_order" 
                               value="{{ old('display_order', 0) }}" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="flex items-center space-x-2.5 mt-4">
                    <input type="checkbox" 
                           name="active" 
                           id="active" 
                           value="1" 
                           {{ old('active', true) ? 'checked' : '' }} 
                           class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                    <label for="active" class="text-xs font-semibold text-slate-700">Candidate is Active in Roster</label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.candidates.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">
                    Save Shortlisted Candidate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
