@extends('layouts.app', ['title' => 'Evaluation Rubric - Academy of Sciences Malaysia'])

@section('content')
<div x-data="{ activeTab: 'all' }" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- Breadcrumb --}}
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">Evaluation Rubric</span>
    </nav>

    {{-- Page Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-teal-800/40 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl space-y-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-teal-500/20 text-teal-300 border border-teal-400/30">
                Vetting & Assessment Standards
            </span>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white uppercase">
                Evaluation Rubric
            </h1>
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-normal">
                Standardized assessment criteria and weightage framework established by the Academy of Sciences Malaysia (ASM) for the evaluation and vetting of Fellowship candidate nominees.
            </p>
        </div>
    </div>

    {{-- Quick Comparison Info Banner --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-700 font-black text-sm shrink-0">
                7
            </div>
            <div class="space-y-1">
                <h2 class="text-sm font-bold text-slate-800">Standard Discipline Groups</h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Used across 7 disciplines. Emphasizes academic, scientific, and research leadership with <strong>15%</strong> weightage for Capacity Building.
                </p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 font-black text-sm shrink-0">
                1
            </div>
            <div class="space-y-1">
                <h2 class="text-sm font-bold text-slate-800">Science & Technology Development and Industry</h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Customised for industry nominees. Heightens Scientific/Technical/Knowledge contributions to <strong>30%</strong>, with <strong>10%</strong> Capacity Building.
                </p>
            </div>
        </div>
    </div>

    {{-- SECTION 1: Standard Evaluation Rubric (Seven Disciplines) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Section Header --}}
        <div class="p-6 sm:p-8 border-b border-slate-100 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="space-y-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                        Primary Framework
                    </span>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                        Standard Evaluation Rubric
                    </h2>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-700 rounded-full">
                    Total: 100%
                </span>
            </div>

            <p class="text-sm text-slate-700 leading-relaxed font-medium">
                The standard evaluation rubric were used by seven discipline groups:
            </p>

            {{-- 7 Disciplines Pill Badges --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">BAES</span>
                    <span class="truncate" title="BAES - Biological Agriculture and Environmental Sciences">BAES - Biological Agriculture and Environmental Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">CS</span>
                    <span class="truncate" title="CS - Chemical Sciences">CS - Chemical Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">ES</span>
                    <span class="truncate" title="ES - Engineering Sciences">ES - Engineering Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">ITCS</span>
                    <span class="truncate" title="ITCS - Information Technology and Computer Sciences">ITCS - Information Technology and Computer Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">MHS</span>
                    <span class="truncate" title="MHS - Medical and Health Sciences">MHS - Medical and Health Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">MPES</span>
                    <span class="truncate" title="MPES - Mathematical and Physical Sciences">MPES - Mathematical and Physical Sciences</span>
                </div>
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-medium text-slate-800 sm:col-span-2 lg:col-span-1">
                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-xs shrink-0">SSH</span>
                    <span class="truncate" title="SSH - Social Sciences and Humanities">SSH - Social Sciences and Humanities</span>
                </div>
            </div>
        </div>

        {{-- Standard Rubric Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-16">
                            No.
                        </th>
                        <th scope="col" class="py-3.5 px-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Criteria
                        </th>
                        <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-36">
                            Weightage (%)
                        </th>
                        <th scope="col" class="py-3.5 pl-3 pr-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-44 hidden md:table-cell">
                            Proportion
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">1</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Scientific/ Technical/ Knowledge contributions
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            25%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 25%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">2</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Leadership
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            25%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 25%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">3</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Contributions to Nation
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            20%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 20%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">4</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Capacity Building
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            15%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 15%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">5</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Current/ past contributions to ASM and/or society
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            5%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 5%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">6</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            International or Regional Networking
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-teal-700 text-base">
                            10%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-teal-600 h-2.5 rounded-full" style="width: 10%"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-300">
                    <tr>
                        <th scope="row" colspan="2" class="py-3.5 pl-6 pr-3 text-left font-black text-slate-900 uppercase">
                            Total
                        </th>
                        <td class="py-3.5 px-4 text-center font-black text-slate-900 text-lg">
                            100%
                        </td>
                        <td class="py-3.5 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-teal-700 h-2.5 rounded-full"></div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- SECTION 2: Science & Technology Development and Industry (STDI) --}}
    <div class="bg-white rounded-2xl border border-amber-200/80 shadow-sm overflow-hidden">
        {{-- Section Header --}}
        <div class="p-6 sm:p-8 border-b border-amber-100 space-y-4 bg-gradient-to-br from-amber-50/40 via-white to-white">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="space-y-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                        Customised Industry Rubric
                    </span>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                        STDI - Science and Technology Development and Industry
                    </h2>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-amber-100 text-amber-900 rounded-full border border-amber-200">
                    Total: 100%
                </span>
            </div>

            {{-- Policy Callout Note --}}
            <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-sm text-amber-950 leading-relaxed space-y-2">
                <div class="flex items-center space-x-2 font-bold text-amber-900">
                    <svg class="w-5 h-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Membership Committee Mandate:</span>
                </div>
                <p class="text-slate-800">
                    As for Science & Technology Development and Industry discipline group, the evaluation rubric was customised to cater nominees from industries and was approved by Membership Committee prior to the vetting exercise.
                </p>
            </div>
        </div>

        {{-- STDI Rubric Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-amber-50/50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-16">
                            No.
                        </th>
                        <th scope="col" class="py-3.5 px-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Criteria
                        </th>
                        <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-36">
                            Weightage (%)
                        </th>
                        <th scope="col" class="py-3.5 pl-3 pr-6 text-left text-xs font-bold text-slate-700 uppercase tracking-wider w-44 hidden md:table-cell">
                            Proportion
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">1</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            <div class="flex items-center space-x-2">
                                <span>Scientific/ Technical/ Knowledge contributions</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-md border border-amber-200">
                                    +5% (Industry Focus)
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            30%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 30%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">2</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Leadership
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            25%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 25%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">3</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Contributions to Nation
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            20%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 20%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">4</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            <div class="flex items-center space-x-2">
                                <span>Capacity Building</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-slate-100 text-slate-600 rounded-md border border-slate-200">
                                    -5% (Adjusted)
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            10%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 10%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">5</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            Current/ past contributions to ASM and/or society
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            5%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 5%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-amber-50/30 transition">
                        <td class="py-4 pl-6 pr-3 font-bold text-slate-900">6</td>
                        <td class="py-4 px-3 font-semibold text-slate-800">
                            International or Regional Networking
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-amber-700 text-base">
                            10%
                        </td>
                        <td class="py-4 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-amber-600 h-2.5 rounded-full" style="width: 10%"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-amber-50/50 border-t-2 border-amber-300">
                    <tr>
                        <th scope="row" colspan="2" class="py-3.5 pl-6 pr-3 text-left font-black text-slate-900 uppercase">
                            Total
                        </th>
                        <td class="py-3.5 px-4 text-center font-black text-slate-900 text-lg">
                            100%
                        </td>
                        <td class="py-3.5 pl-3 pr-6 hidden md:table-cell">
                            <div class="w-full bg-amber-600 h-2.5 rounded-full"></div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Comparative Analysis Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
            <h3 class="text-base font-bold text-slate-900 uppercase tracking-wider">
                Comparative Weightage Summary
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="py-3 px-3 text-left font-bold text-slate-700 uppercase">Criteria</th>
                        <th class="py-3 px-3 text-center font-bold text-teal-800 uppercase">7 Standard Disciplines</th>
                        <th class="py-3 px-3 text-center font-bold text-amber-800 uppercase">Industry (STDI)</th>
                        <th class="py-3 px-3 text-center font-bold text-slate-700 uppercase">Variance Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">1. Scientific/ Technical/ Knowledge contributions</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">25%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">30%</td>
                        <td class="py-3 px-3 text-center text-xs text-amber-700 font-semibold">+5% (Industry application)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">2. Leadership</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">25%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">25%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-400">Equal</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">3. Contributions to Nation</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">20%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">20%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-400">Equal</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">4. Capacity Building</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">15%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">10%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-600 font-semibold">-5% (Industry adjustment)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">5. Current/ past contributions to ASM and/or society</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">5%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">5%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-400">Equal</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">6. International or Regional Networking</td>
                        <td class="py-3 px-3 text-center font-bold text-teal-700">10%</td>
                        <td class="py-3 px-3 text-center font-bold text-amber-700">10%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-400">Equal</td>
                    </tr>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <td class="py-3 px-3 font-black text-slate-900 uppercase">Total</td>
                        <td class="py-3 px-3 text-center font-black text-teal-700 text-base">100%</td>
                        <td class="py-3 px-3 text-center font-black text-amber-700 text-base">100%</td>
                        <td class="py-3 px-3 text-center text-xs text-slate-500 font-medium">Standardized Sum</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection

