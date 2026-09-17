@extends('layouts.app')

@section('title', 'Admin Due Diligence Review')

@section('content')
<div x-data="{}" class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Due Diligence Review</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Due Diligence Repository & Review</h1>
            <p class="text-sm text-slate-600 mt-1">
                Centralized registry of due diligence observations, integrity disclosures, and supporting attachments.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.reports.export-due-diligence-csv') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export All Due Diligence CSV
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.due-diligence.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="q" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Search Text</label>
                <input type="text" 
                       name="q" 
                       id="q"
                       value="{{ $filters['q'] ?? '' }}" 
                       placeholder="Candidate, voter name, or keyword..." 
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="sm:col-span-3">
                <label for="discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Candidate Discipline</label>
                <select name="discipline_id" id="discipline_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Disciplines</option>
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ (string)($filters['discipline_id'] ?? '') === (string)$disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Due Diligence Category</label>
                <select name="category_id" id="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex space-x-2">
                <button type="submit" class="w-full py-2 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Filter
                </button>
                @if(!empty($filters['q']) || !empty($filters['discipline_id']) || !empty($filters['category_id']))
                    <a href="{{ route('admin.due-diligence.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Clear">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Submissions Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <span>Showing <strong class="text-slate-800">{{ $submissions->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $submissions->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ $submissions->total() }}</strong> entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Candidate</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Submitter</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Category</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Observations & Supporting Details</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Attachments</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($submissions as $sub)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 align-top">
                                <div class="flex items-center space-x-2.5">
                                    @if($sub->candidate->photo_path)
                                        <img src="{{ $sub->candidate->photo_path }}" alt="{{ $sub->candidate->candidate_name }}" 
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($sub->candidate->candidate_name) }}&background=0D9488&color=fff&size=64';"
                                             class="w-8 h-8 rounded-full object-cover flex-shrink-0 cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openCandidateModal({{ $sub->candidate->id }})"
                                             title="Click to view candidate info">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-[10px] flex-shrink-0 cursor-pointer hover:bg-slate-300 transition-colors"
                                             onclick="openCandidateModal({{ $sub->candidate->id }})"
                                             title="Click to view candidate info">
                                            {{ substr($sub->candidate->candidate_name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <button type="button" 
                                                onclick="openCandidateModal({{ $sub->candidate->id }})"
                                                class="text-left group focus:outline-none cursor-pointer">
                                            <span class="font-bold text-slate-900 group-hover:text-teal-700 text-xs block leading-tight underline decoration-teal-500/40 underline-offset-2 transition-colors">
                                                {{ $sub->candidate->candidate_name }}
                                            </span>
                                        </button>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-teal-50 text-teal-800 mt-0.5">
                                            {{ $sub->candidate->discipline->code ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 align-top whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $sub->user->name ?? 'Deleted User' }}</span>
                                <span class="text-[10px] text-slate-500 block">{{ $sub->user->email ?? 'N/A' }}</span>
                                @if($sub->userDiscipline)
                                    <span class="text-[10px] text-teal-700 font-semibold block">{{ $sub->userDiscipline->code }}</span>
                                @elseif($sub->user && $sub->user->discipline)
                                    <span class="text-[10px] text-teal-700 font-semibold block">{{ $sub->user->discipline->code }}</span>
                                @else
                                    <span class="text-[10px] text-slate-400 block">{{ ucfirst($sub->user->role ?? 'User') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800">
                                    {{ $sub->category->name ?? 'General' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 align-top">
                                <div class="text-slate-700 leading-relaxed max-w-lg bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    {{ $sub->comment }}
                                </div>
                                @if($sub->reference_1_name || $sub->reference_2_name)
                                    <div class="mt-2 text-[11px] bg-slate-50/90 p-2.5 rounded-xl border border-slate-200 space-y-1.5 text-slate-700 max-w-lg">
                                        @if($sub->reference_1_name)
                                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-[#302556] flex-shrink-0">
                                                    Reference 1
                                                </span>
                                                <span>
                                                    <strong class="font-bold text-slate-900">{{ $sub->reference_1_name }}</strong> &bull;
                                                    {{ $sub->reference_1_designation }}, {{ $sub->reference_1_organisation }} &bull;
                                                    <a href="mailto:{{ $sub->reference_1_email }}" class="text-blue-600 hover:underline">{{ $sub->reference_1_email }}</a> &bull;
                                                    {{ $sub->reference_1_contact_number }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($sub->reference_2_name)
                                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 pt-1 border-t border-slate-200/60">
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-200 text-slate-700 flex-shrink-0">
                                                    Reference 2
                                                </span>
                                                <span>
                                                    <strong class="font-bold text-slate-900">{{ $sub->reference_2_name }}</strong> &bull;
                                                    {{ $sub->reference_2_designation }}, {{ $sub->reference_2_organisation }} &bull;
                                                    <a href="mailto:{{ $sub->reference_2_email }}" class="text-blue-600 hover:underline">{{ $sub->reference_2_email }}</a> &bull;
                                                    {{ $sub->reference_2_contact_number }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                @if($sub->supportingDocuments->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach($sub->supportingDocuments as $doc)
                                            <a href="{{ route('due-diligence.download', $doc) }}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                {{ Str::limit($doc->original_filename, 15) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-300 italic text-[11px]">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $sub->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No due diligence submissions match the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $submissions->links() }}
        </div>
    </div>
</div>
@endsection
