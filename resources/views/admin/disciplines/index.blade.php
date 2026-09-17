@extends('layouts.app')

@section('title', 'Admin Discipline Management')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">Disciplines</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Disciplines Management</h1>
            <p class="text-sm text-slate-600 mt-1">
                Configure the 8 scientific disciplines, quotas, and voting domain boundaries.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.disciplines.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Add New Discipline
            </a>
        </div>
    </div>

    {{-- Disciplines Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold w-16 text-center">ID</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Discipline Name</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Description</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Candidates</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Voters</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($disciplines as $disc)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 text-center font-bold text-slate-900">
                                {{ $disc->id }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $disc->discipline_name }}</span>
                                <span class="text-[10px] text-teal-700 font-semibold">{{ $disc->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 max-w-sm">
                                {{ $disc->description ?? 'No description provided.' }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-800">
                                {{ $disc->candidates_count }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-800">
                                {{ $disc->users_count }}
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.disciplines.toggle-active', $disc) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $disc->active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                        {{ $disc->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.disciplines.edit', $disc) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
