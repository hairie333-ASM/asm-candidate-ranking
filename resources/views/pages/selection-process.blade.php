@extends('layouts.app', ['title' => 'Selection Process - Academy of Sciences Malaysia'])

@section('content')
<div x-data="{
    openSteps: { 1: true, 2: false, 3: false, 4: false, 5: false, 6: false, 7: false, 8: false },
    toggle(step) {
        this.openSteps[step] = !this.openSteps[step];
    },
    isOpen(step) {
        return !!this.openSteps[step];
    },
    expandAll() {
        for (let i = 1; i <= 8; i++) this.openSteps[i] = true;
    },
    collapseAll() {
        for (let i = 1; i <= 8; i++) this.openSteps[i] = false;
    }
}" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- Breadcrumb --}}
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">Selection Process</span>
    </nav>

    {{-- Page Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-teal-800/40 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-teal-500/20 text-teal-300 border border-teal-400/30">
                Fellowship Election Pathway
            </span>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white uppercase">
                Selection Process
            </h1>
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-normal">
                The Academy of Sciences Malaysia (ASM) harnesses the expertise, knowledge, wisdom and network of its Fellows to provide STI input to the government and stakeholders. Leveraging on Fellows, ASM continues to demonstrate its commitment in advising policy makers, charting the way forward in the country’s development and championing strategic programmes and ideas at the national and international level.
            </p>
            <p class="text-xs sm:text-sm text-teal-200/90 leading-relaxed font-normal">
                Therefore, the election of suitable individuals as Fellows is crucial to enable ASM to exercise its functions as a Thought Leader in Science, Technology, Innovation and Economy (STIE) toward a progressive, harmonious, prosperous, and sustainable society, nationally and internationally.
            </p>
        </div>
    </div>

    {{-- Controls Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">8-Step Comprehensive Evaluation Process</span>
            <span class="text-xs text-slate-400 font-normal">(Click any step to reveal details)</span>
        </div>
        <div class="flex items-center space-x-2 text-xs">
            <button type="button" 
                    @click="expandAll()" 
                    class="px-3 py-1.5 rounded-lg font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 transition border border-teal-200 cursor-pointer">
                Expand All
            </button>
            <button type="button" 
                    @click="collapseAll()" 
                    class="px-3 py-1.5 rounded-lg font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition border border-slate-200 cursor-pointer">
                Collapse All
            </button>
        </div>
    </div>

    {{-- Accordion Container --}}
    <div class="space-y-4">

        {{-- STEP 1 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(1)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        01
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 1: Nomination</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Eligibility Criteria, Scientific Merit & Active Contribution</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(1) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(1) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(1)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-4 text-sm text-slate-700 leading-relaxed">
                <p>
                    The nomination of Fellows is based on scientific merit. The criteria for election as Fellow are as follows:
                </p>
                <ul class="space-y-2 pl-2">
                    <li class="flex items-start">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-600 mt-2 mr-2.5 flex-shrink-0"></span>
                        <span><strong>Outstanding individual achievements or leadership</strong> in science, engineering and technology, social sciences and humanities;</span>
                    </li>
                    <li class="flex items-start">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-600 mt-2 mr-2.5 flex-shrink-0"></span>
                        <span><strong>Innovative management or development of technological industries</strong>; or of technological operations within non-technological industries; or of government or non-government organisations or institutions dealing with issues relating to the science, engineering and technology;</span>
                    </li>
                    <li class="flex items-start">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-600 mt-2 mr-2.5 flex-shrink-0"></span>
                        <span><strong>Outstanding contributions</strong> at the interface between science, engineering and technology and society.</span>
                    </li>
                </ul>

                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Proposer & Seconder Regulations</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        A nominee can only be nominated by ASM Fellows. Proposer must be from the same discipline or field of the nominee while the seconder can be from any discipline or field. Each Fellow who signs a proposal document will be deemed thereby to certify that to the best of the Fellow’s knowledge and belief, the person nominated therein is qualified to be elected as a Fellow.
                    </p>
                </div>

                <div class="p-4 bg-teal-50/80 rounded-xl border border-teal-200 space-y-2">
                    <div class="flex items-center space-x-2 text-teal-900 font-bold text-xs">
                        <svg class="w-4 h-4 text-teal-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Council 163rd Meeting Policy (10 April 2025): Demonstrated Active Participation</span>
                    </div>
                    <p class="text-xs text-teal-900/90 leading-relaxed">
                        In an effort to establish a committed ASM Fellowship, the ASM Council at its 163rd meeting on 10 April 2025, approved the introduction of an additional eligibility criterion for the nomination of new Fellows. Under this new criterion, Proposers must ensure that nominees have demonstrated active participation and meaningful contributions to ASM. These may include service as a member of a working committee, task force, or project committee, or involvement as a writer, editor, or subject matter expert. Nominees who do not meet this requirement will not be considered for evaluation. However, this eligibility criteria is not applicable to forwarded nominations.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 2 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(2)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        02
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 2: Administrative Screening</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Secretary-General Validation & Statutory Consent</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(2) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(2) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(2)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3 text-sm text-slate-700 leading-relaxed">
                <p>
                    Administrative screening is done by ASM Secretary-General. Only completed nominations will be considered.
                </p>
                <div class="p-4 bg-white rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        The consent of the nominee is obtained in writing. It shall include both willingness to be nominated and willingness, if elected a Fellow, to comply with the ASM Act 1995 and Regulations and contribute to ASM.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 3 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(3)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        03
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 3: Vetting Exercise</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Discipline Vetting Committee Evaluation & 80% Benchmark</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(3) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(3) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(3)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-4 text-sm text-slate-700 leading-relaxed">
                <p>
                    The Discipline Vetting Committee carries out the first level of evaluation. Secretary-General appoints members of the Committee in consultation with Chairperson of respective discipline. The selection of Committee members is made upon considering the following requirements:
                </p>
                <ul class="space-y-2 pl-2">
                    <li class="flex items-start">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-600 mt-2 mr-2.5 flex-shrink-0"></span>
                        <span><strong>Independence & Non-Bias</strong>: Fellows who are neither a Proposer nor a Seconder and not a Council Member.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-600 mt-2 mr-2.5 flex-shrink-0"></span>
                        <span><strong>Equitable Balance</strong>: Suggestions are made keeping in mind a good balance of members (e.g. Male/female, academia/industry, area of expertise).</span>
                    </li>
                </ul>
                <p>
                    The Discipline Vetting Committees will access the nomination documents. Each Committee is required to use the endorsed evaluation criteria as a guide in evaluating a nominee. Either Proposer or Seconder must present their nominee in a structured manner during the vetting exercise. The Proposer or Seconder can present their nominee via physical meeting or tele or online conferencing platforms.
                </p>
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                    <p class="text-xs text-emerald-900 font-semibold leading-relaxed">
                        Nominations that meet the cut-off point of 80% during the vetting exercise will be shortlisted. Unsuccessful nominations will be forwarded for consideration in the following year.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 4 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(4)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        04
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 4: Review of Shortlisted Nominees</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Membership Committee Deliberation & Approval for Online Ranking</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(4) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(4) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(4)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3 text-sm text-slate-700 leading-relaxed">
                <p>
                    The Discipline Vetting Committee is required to present the result of its evaluation to the Membership Committee.
                </p>
                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        The Membership Committee will review the result of the vetting exercise and evaluate shortlisted nominees and borderline nominees. The Membership Committee may add potential nominees to be shortlisted for consideration or subtract nominees. The Membership Committee may also override the decision of the Discipline Vetting Committee, if required prior to the voting exercise by ASM Fellows. The Membership Committee then recommends shortlisted nominees for online ranking.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 5 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(5)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        05
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 5: Ranking by Peers</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Discipline-Restricted Ranking & Cross-Discipline Due Diligence</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(5) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(5) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(5)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-4 text-sm text-slate-700 leading-relaxed">
                <div class="p-4 bg-teal-50/60 rounded-xl border border-teal-200">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900 mb-1">Discipline-Restricted Voting</h4>
                    <p class="text-xs text-teal-950 leading-relaxed">
                        Fellows of a respective Discipline will have the opportunity to read the basis of recommendation of the shortlisted nominees and rank them. Fellows can vote to rank nominees only in their respective disciplines.
                    </p>
                </div>
                <div class="p-4 bg-slate-100 rounded-xl border border-slate-200">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-1">Cross-Discipline Due Diligence Observations</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Fellows can also view recommended nominees of other disciplines and submit comments/ inquiries on the shortlisted nominee(s) that are deemed not meeting the criteria of a Fellow.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 6 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(6)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        06
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 6: Final Shortlisting</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Descending Rank Analysis, Objections Review & 10% Quota Harmonisation</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(6) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(6) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(6)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3 text-sm text-slate-700 leading-relaxed">
                <p>
                    Membership Committee will consider the list of shortlisted nominees ranked in descending order (highest ranked first) for each discipline together with objections from Fellows (if any).
                </p>
                <div class="p-4 bg-amber-50/80 rounded-xl border border-amber-200">
                    <p class="text-xs text-amber-950 font-medium leading-relaxed">
                        Membership Committee is responsible for finalising the list of recommended nominees and harmonising the distribution between disciplines, keeping the overall membership quota to a maximum of 10% increase each year.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 7 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(7)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        07
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 7: Recommendation to AGM</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">ASM Council Endorsement for Annual General Meeting</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(7) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(7) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(7)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3 text-sm text-slate-700 leading-relaxed">
                <div class="p-4 bg-white rounded-xl border border-slate-200">
                    <p class="text-xs text-slate-700 leading-relaxed">
                        ASM Council will review the recommendations of the ASM Membership Committee and recommend nominees for consideration for election at the Annual General Meeting.
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 8 --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-teal-400/50">
            <button type="button" 
                    @click="toggle(8)" 
                    class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer focus:outline-none bg-white hover:bg-slate-50/80 transition-colors">
                <div class="flex items-center space-x-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-50 text-teal-800 font-extrabold text-sm flex items-center justify-center border border-teal-200">
                        08
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">STEP 8: Election of New Fellow</h2>
                        <span class="text-xs text-teal-700 font-medium block mt-0.5">Annual General Meeting (AGM) Official Election</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <span class="hidden sm:inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600" x-text="isOpen(8) ? 'Hide Info' : 'Show Info'"></span>
                    <svg class="w-5 h-5 text-slate-500 transform transition-transform duration-200" 
                         :class="{ 'rotate-180 text-teal-700': isOpen(8) }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div x-show="isOpen(8)" x-cloak class="px-6 pb-6 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3 text-sm text-slate-700 leading-relaxed">
                <div class="p-4 bg-teal-900 text-white rounded-xl shadow-xs">
                    <p class="text-xs font-semibold leading-relaxed text-teal-100">
                        Election of Fellows will take place at the Annual General Meeting.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
