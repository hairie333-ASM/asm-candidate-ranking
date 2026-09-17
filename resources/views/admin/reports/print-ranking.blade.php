<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Candidate Ranking Sheet - {{ $results['discipline']->discipline_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; background: #fff; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-900">
    {{-- Print Control Header --}}
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.ranking-results.index', ['discipline_id' => $results['discipline']->id]) }}" class="text-xs font-semibold text-teal-700 hover:underline">
            &larr; Return to Results Matrix
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm inline-flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print / Save as PDF
        </button>
    </div>

    {{-- Official Printable Document Container --}}
    <div class="max-w-5xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-300 print:border-none print:shadow-none print:p-0">
        {{-- Document Header --}}
        <div class="border-b-2 border-slate-900 pb-6 mb-6 flex items-start justify-between">
            <div>
                <div class="text-xs uppercase font-bold tracking-widest text-slate-500">Academy of Sciences Malaysia (ASM)</div>
                <h1 class="text-2xl font-black text-slate-900 mt-1">CONFIDENTIAL RANKING RESULTS DOSSIER</h1>
                <p class="text-sm font-semibold text-slate-700 mt-1">
                    {{ $results['discipline']->discipline_name }}
                </p>
            </div>
            <div class="text-right text-xs text-slate-600">
                <div><strong>Exercise:</strong> {{ $results['exercise']->title ?? 'General' }}</div>
                <div><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                <div><strong>Completed Ballots:</strong> {{ $results['submissions_count'] }}</div>
            </div>
        </div>

        {{-- Tie-break note --}}
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-[11px] text-slate-600 mb-6">
            <strong>Standard Evaluation Algorithm:</strong> Composite standings are determined by Average Rank (Lowest = 1st Preference). Ties are resolved by: (1) Higher Count of Rank 1 votes, (2) Higher Count of Rank 2 votes, (3) Candidate ID.
        </div>

        {{-- Candidates Result Table --}}
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 text-slate-900 uppercase font-bold border-b border-slate-300 text-[10px]">
                    <tr>
                        <th class="p-2 border-r border-slate-300 text-center w-12">Pos</th>
                        <th class="p-2 border-r border-slate-300">Candidate Name</th>
                        <th class="p-2 border-r border-slate-300">Organisation</th>
                        <th class="p-2 border-r border-slate-300 text-center">Avg Rank</th>
                        <th class="p-2 border-r border-slate-300 text-center">Rank 1s</th>
                        <th class="p-2 border-r border-slate-300 text-center">Rank 2s</th>
                        <th class="p-2 text-center">Total Votes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300 text-slate-800">
                    @forelse($results['candidates_results'] as $index => $item)
                        @php 
                            $candidate = $item['candidate']; 
                            $pos = $index + 1;
                        @endphp
                        <tr class="{{ $pos === 1 ? 'font-bold bg-slate-50' : '' }}">
                            <td class="p-2 border-r border-slate-300 text-center font-bold">{{ $pos }}</td>
                            <td class="p-2 border-r border-slate-300">
                                <div>{{ $candidate->candidate_name }}</div>
                                <div class="text-[10px] text-slate-500 font-normal">{{ $candidate->sub_discipline ?? 'General' }}</div>
                            </td>
                            <td class="p-2 border-r border-slate-300 text-[11px]">{{ $candidate->organisation }}</td>
                            <td class="p-2 border-r border-slate-300 text-center font-bold text-slate-900">
                                {{ $item['average_rank'] > 0 ? number_format($item['average_rank'], 2) : 'N/A' }}
                            </td>
                            <td class="p-2 border-r border-slate-300 text-center">{{ $item['rank_1_count'] }}</td>
                            <td class="p-2 border-r border-slate-300 text-center">{{ $item['rank_2_count'] }}</td>
                            <td class="p-2 text-center">{{ $item['total_submissions'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-500">No ranking data recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Verification / Sign-off Footer --}}
        <div class="mt-12 pt-8 border-t border-slate-300 grid grid-cols-2 gap-8 text-xs text-slate-700">
            <div>
                <p class="font-bold uppercase tracking-wider mb-8">Verified By (Scrutineer / Administrator):</p>
                <div class="border-b border-slate-400 w-3/4 mb-1"></div>
                <p class="text-slate-500">Name &amp; Designation</p>
                <p class="text-slate-400 text-[10px] mt-0.5">Date: ________________________</p>
            </div>
            <div>
                <p class="font-bold uppercase tracking-wider mb-8">Certified By (Discipline Chair / Council):</p>
                <div class="border-b border-slate-400 w-3/4 mb-1"></div>
                <p class="text-slate-500">Signature &amp; Stamp</p>
                <p class="text-slate-400 text-[10px] mt-0.5">Date: ________________________</p>
            </div>
        </div>
    </div>
</body>
</html>
