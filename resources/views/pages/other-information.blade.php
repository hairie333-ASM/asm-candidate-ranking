@extends('layouts.app', ['title' => 'Guidelines & Information - Selection Exercise for New Election Fellow'])

@section('content')
<div class="space-y-8 max-w-4xl mx-auto py-4">

    <!-- Page Header -->
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Guidelines & Other Information</h1>
        <p class="text-sm text-slate-600 mt-1">Official ranking instructions, evaluation criteria, and due diligence submission guidelines for ASM voters and reviewers.</p>
    </div>

    <!-- Section 1: Ranking Guidelines -->
    <div class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-3 text-teal-800">
            <span class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center font-bold text-sm">1</span>
            <h2 class="text-lg font-bold">Ranking Instructions & Ordinal Sequencing</h2>
        </div>

        <div class="space-y-3 text-sm text-slate-700 leading-relaxed pl-11">
            <p>
                Each authorized Voting User is assigned to evaluate candidates belonging to their primary discipline. The application automatically determines your assigned discipline upon authentication.
            </p>
            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                <li><strong>Rank 1 represents your Highest Preference</strong> for fellowship / shortlisted recognition.</li>
                <li>Rank 2 represents your Second Preference, and so forth, down to Rank N (Lowest Preference).</li>
                <li><strong>All candidates must be ranked</strong>: You cannot submit partial rankings.</li>
                <li><strong>No duplicate ranks allowed</strong>: Each candidate must be assigned a unique ranking number.</li>
                <li><strong>Sequence completeness</strong>: The assigned numbers must form an unbroken sequence from 1 to N.</li>
                <li><strong>Real-Time Duplicate Prevention</strong>: When you assign a ranking number to a candidate, the system immediately reserves it and prevents other candidates from selecting that same number. If you change a selection, the previously held number becomes instantly re-available.</li>
                <li><strong>Final Submission Lock</strong>: Once submitted, your ranking is recorded and locked. It cannot be altered unless reopened upon written request by the Academy Administrator.</li>
            </ul>
        </div>
    </div>

    <!-- Section 2: Due Diligence Guidelines -->
    <div class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-3 text-teal-800">
            <span class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center font-bold text-sm">2</span>
            <h2 class="text-lg font-bold">Due Diligence Review Process</h2>
        </div>

        <div class="space-y-3 text-sm text-slate-700 leading-relaxed pl-11">
            <p>
                Unlike ranking (which is restricted to your assigned discipline), <strong>due diligence assessment is open across all 8 disciplines</strong>.
            </p>
            <p>
                Any authenticated voter, reviewer, or administrator may provide formal due diligence comments, observations, or supporting documentation regarding any candidate in any discipline.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-2">
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ General Comment</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Professional Background</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Academic / Research Record</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Leadership & Impact</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Significant Achievements</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Potential Conflicts of Interest</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Integrity & Professional Reputation</div>
                <div class="p-2.5 rounded bg-slate-50 border border-slate-100 font-medium">✓ Other Material Considerations</div>
            </div>
            <p class="text-xs text-slate-500 pt-2">
                Supporting files (.pdf, .docx, .xlsx, .pptx, .jpg, .png) up to 10MB each may be attached and are held in private, encrypted institutional storage accessible only to authorised committee reviewers.
            </p>
        </div>
    </div>

    <!-- Section 3: Confidentiality Notice -->
    <div class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-3 text-teal-800">
            <span class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center font-bold text-sm">3</span>
            <h2 class="text-lg font-bold">Confidentiality & Code of Conduct</h2>
        </div>

        <div class="space-y-3 text-sm text-slate-700 leading-relaxed pl-11">
            <p>
                All assessment records, candidate scores, due diligence commentary, and personal data accessed within this system are classified as <strong>Strictly Confidential</strong>.
            </p>
            <ul class="list-disc list-inside space-y-1 text-slate-600">
                <li>Do not disclose candidate names or ranking outcomes to non-committee members.</li>
                <li>Do not download or disseminate candidate nomination packages outside authorized Academy evaluation channels.</li>
                <li>All logins, ranking actions, document access, and modifications are logged permanently with timestamp and client IP attribution.</li>
            </ul>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="bg-slate-100 p-6 rounded-xl border border-slate-200 text-center space-y-2">
        <h3 class="text-base font-bold text-slate-800">Need Assistance or Submission Reopening?</h3>
        <p class="text-xs text-slate-600 max-w-xl mx-auto">
            If you encounter technical issues, require clarification regarding candidate dossiers, or need your final submission reopened, please contact the <strong>ASM Membership Secretariat</strong> at <a href="mailto:membership@akademisains.gov.my" class="font-semibold text-[#302556] hover:underline">membership@akademisains.gov.my</a>.
        </p>
    </div>

</div>
@endsection
