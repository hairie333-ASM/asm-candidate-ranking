@extends('layouts.app')

@section('title', ($isSingleCandidate ?? false) ? 'Discipline Status - Single Candidate' : 'My Ranking Submission Receipt')

@section('content')
<div x-data="{}" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumbs & Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">{{ ($isSingleCandidate ?? false) ? 'Discipline Status' : 'My Submission' }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ ($isSingleCandidate ?? false) ? 'Discipline Status: Candidate Profile' : 'Official Ranking Submission Receipt' }}</h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ ($isSingleCandidate ?? false) ? 'Official candidate information and evaluation status for ' . ($discipline->discipline_name ?? 'Your Discipline') . '.' : 'Your submitted candidate preferences for ' . ($discipline->discipline_name ?? 'Your Discipline') . '.' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Receipt
            </button>
            <a href="{{ route('due-diligence.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                Due Diligence Review
            </a>
        </div>
    </div>

    @if($isSingleCandidate)
        {{-- Single Candidate Discipline Receipt --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-[#302556] to-[#241c42] px-6 py-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-emerald-300 border border-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-emerald-200 uppercase tracking-wider">Official Status</div>
                        <div class="text-lg font-bold">Ranking: Not Required (Single Candidate)</div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#008442] text-white shadow-sm">
                        SATISFIED ✓
                    </span>
                    <div class="text-xs text-emerald-100 mt-1">
                        Sole Nominee Confirmed
                    </div>
                </div>
            </div>

            {{-- Metadata Grid --}}
            <div class="p-6 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Voter Name</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $user->name }}</span>
                    <span class="text-slate-500 block">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Assigned Discipline</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $discipline->discipline_name ?? 'N/A' }}</span>
                    <span class="text-teal-700 font-semibold block">Code: {{ $discipline->code ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Ranking Exercise</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $exercise->title ?? 'General' }}</span>
                    <span class="text-slate-500 block">Exercise ID #{{ $exercise->id ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Evaluation Status</span>
                    <span class="text-teal-700 font-bold text-sm">1 Candidate</span>
                    <span class="text-emerald-700 font-medium block">Ranking Not Required</span>
                </div>
            </div>

            <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 flex items-center text-xs text-blue-900">
                <svg class="w-4 h-4 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><strong>Notice:</strong> This discipline contains only one shortlisted candidate. In accordance with Academy election guidelines, no ranking exercise is required and this candidate is automatically recognized as the sole nominee.</span>
            </div>
        </div>

        @if($singleCandidate)
            <div class="space-y-4 mb-8">
                <div class="flex items-center justify-between px-1">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Shortlisted Candidate Profile</h2>
                        <p class="text-xs text-slate-500">Sole Nominee for {{ $discipline->discipline_name ?? 'this discipline' }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Sole Nominee
                    </span>
                </div>
                <x-candidate-card :candidate="$singleCandidate" layout="full" />
            </div>
        @endif
    @elseif(!$submission)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
            <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-amber-900 mb-1">No Submission Recorded</h2>
            <p class="text-xs text-amber-800 max-w-md mx-auto mb-4">You have not submitted your final ranking for this exercise yet.</p>
            <a href="{{ route('ranking.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                Proceed to Candidate Ranking
            </a>
        </div>
    @else
        {{-- Status Banner --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-[#302556] to-[#241c42] px-6 py-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-teal-200 border border-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-teal-200 uppercase tracking-wider">Official Status</div>
                        <div class="text-lg font-bold">Ranking Locked & Confirmed</div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm">
                        {{ $submission->status }}
                    </span>
                    <div class="text-xs text-teal-100 mt-1">
                        Submitted: {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>

            {{-- Metadata Grid --}}
            <div class="p-6 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Voter Name</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $user->name }}</span>
                    <span class="text-slate-500 block">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Assigned Discipline</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $discipline->name ?? 'N/A' }}</span>
                    <span class="text-teal-700 font-semibold block">Code: {{ $discipline->code ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Ranking Exercise</span>
                    <span class="text-slate-900 font-bold text-sm">{{ $exercise->title ?? 'N/A' }}</span>
                    <span class="text-slate-500 block">Exercise ID #{{ $exercise->id ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider font-medium block">Total Ranked</span>
                    <span class="text-teal-700 font-bold text-sm">{{ $submission->rankings->count() }} Candidates</span>
                    <span class="text-emerald-700 font-medium block">Full Ordinal Sequence</span>
                </div>
            </div>

            {{-- Locked Notice --}}
            <div class="px-6 py-3 bg-amber-50 border-b border-amber-100 flex items-center text-xs text-amber-800">
                <svg class="w-4 h-4 text-amber-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>This ballot is finalized and locked. If you need to make changes, contact an ASM Administrator to request ballot reopening.</span>
            </div>

            {{-- Rankings Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-100 text-slate-700 uppercase tracking-wider text-xs border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-20 text-center font-bold">Rank</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Candidate Profile</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Organisation / Specialty</th>
                            <th scope="col" class="px-6 py-3 text-right font-semibold">Nomination File</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($submission->rankings as $ranking)
                            @php $candidate = $ranking->candidate; @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm {{ $ranking->ranking_number === 1 ? 'bg-amber-100 text-amber-800 border-2 border-amber-300' : ($ranking->ranking_number === 2 ? 'bg-slate-200 text-slate-800 border-2 border-slate-300' : ($ranking->ranking_number === 3 ? 'bg-orange-100 text-orange-800 border-2 border-orange-300' : 'bg-teal-50 text-teal-800 border border-teal-200')) }}">
                                        {{ $ranking->ranking_number }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        @if($candidate->photo_path)
                                            <img src="{{ $candidate->photo_path }}" alt="{{ $candidate->candidate_name }}" 
                                                 class="w-10 h-10 rounded-full object-cover border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="openCandidateModal({{ $candidate->id }})"
                                                 @click="openCandidateModal({{ $candidate->id }})"
                                                 title="Click to view candidate info">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs cursor-pointer hover:bg-slate-300 transition-colors"
                                                 onclick="openCandidateModal({{ $candidate->id }})"
                                                 @click="openCandidateModal({{ $candidate->id }})"
                                                 title="Click to view candidate info">
                                                {{ substr($candidate->candidate_name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <button type="button" 
                                                    onclick="openCandidateModal({{ $candidate->id }})"
                                                    @click="openCandidateModal({{ $candidate->id }})"
                                                    class="text-left group focus:outline-none cursor-pointer">
                                                <div class="font-bold text-slate-900 group-hover:text-teal-700 underline decoration-teal-500/40 underline-offset-2 transition-colors">{{ $candidate->candidate_name }}</div>
                                            </button>
                                            <div class="text-xs text-slate-500">{{ $candidate->membership_category ?? 'Candidate' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="text-slate-800 font-medium">{{ $candidate->organisation }}</div>
                                    <div class="text-slate-500 mt-0.5">{{ $candidate->sub_discipline ?? $candidate->discipline?->discipline_name ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button type="button" 
                                                onclick="openCandidateModal({{ $candidate->id }})"
                                                @click="openCandidateModal({{ $candidate->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition shadow-2xs cursor-pointer">
                                            <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Info
                                        </button>
                                        @if($candidate->onedrive_link)
                                            <a href="{{ $candidate->onedrive_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition shadow-xs">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                View Dossier
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Audit Hash / Integrity Footer --}}
            <div class="p-4 bg-slate-100/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-slate-500 text-xs gap-2">
                <span>Receipt generated securely by ASM Selection Exercise for New Election Fellow System.</span>
                <span class="font-mono text-[11px] text-slate-400">Submission Record #{{ $submission->id }} &bull; Exercise {{ $submission->exercise_id }} &bull; {{ $discipline->discipline_name ?? 'Discipline' }}</span>
            </div>
        </div>
    @endif
</div>
@endsection
