@extends('layouts.app')

@section('title', 'Admin User Management')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-teal-700 font-semibold">User Management</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">User & Voter Management</h1>
            <p class="text-sm text-slate-600 mt-1">
                Manage staff, voters, reviewers, discipline assignments, and access permissions.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                + Add New User
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="q" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Search Name / Email</label>
                <input type="text" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or email..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="sm:col-span-3">
                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role</label>
                <select name="role" id="role" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Roles</option>
                    <option value="voting_user" {{ ($filters['role'] ?? '') === 'voting_user' ? 'selected' : '' }}>Voting User</option>
                    <option value="reviewer" {{ ($filters['role'] ?? '') === 'reviewer' ? 'selected' : '' }}>Reviewer</option>
                    <option value="administrator" {{ ($filters['role'] ?? '') === 'administrator' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <label for="discipline_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discipline</label>
                <select name="discipline_id" id="discipline_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Disciplines</option>
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ (string)($filters['discipline_id'] ?? '') === (string)$disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex space-x-2">
                <button type="submit" class="w-full py-2 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Filter
                </button>
                @if(!empty($filters['q']) || !empty($filters['role']) || !empty($filters['discipline_id']))
                    <a href="{{ route('admin.users.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <span>Showing <strong class="text-slate-800">{{ $users->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $users->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ $users->total() }}</strong> users</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Name & Email</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Role</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Assigned Discipline</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5">
                                <span class="font-bold text-slate-900 text-sm block">{{ $u->name }}</span>
                                <span class="text-slate-500 text-xs">{{ $u->email }}</span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $u->role === 'administrator' ? 'bg-amber-100 text-amber-800' : ($u->role === 'reviewer' ? 'bg-blue-100 text-blue-800' : 'bg-teal-100 text-teal-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $u->role)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($u->discipline)
                                    <span class="font-semibold text-slate-800 block">{{ $u->discipline->code }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $u->discipline->discipline_name }}</span>
                                @else
                                    <span class="text-slate-400 italic">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.users.toggle-active', $u) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $u->active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}" title="Click to toggle status">
                                        {{ $u->active ? 'Active' : 'Suspended' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No users found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
