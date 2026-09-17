@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-slate-800">Users</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">New User</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-teal-900 px-6 py-4 text-white">
            <h1 class="text-lg font-bold">Register New System User / Voter</h1>
            <p class="text-xs text-teal-200 mt-0.5">Assign role and strict discipline boundaries for voting access.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 sm:p-8 space-y-5" x-data="{ role: '{{ old('role', 'voting_user') }}' }">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="e.g. Prof. Dr. Ahmad Ismail" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-rose-500 @enderror">
                @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="voter@example.test" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-rose-500 @enderror">
                @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Initial Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" id="password" required placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-rose-500 @enderror">
                @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">System Role <span class="text-rose-500">*</span></label>
                <select name="role" id="role" x-model="role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="voting_user">Voting User (Ranks assigned discipline)</option>
                    <option value="reviewer">Reviewer (Cross-discipline due diligence only)</option>
                    <option value="administrator">Administrator (Full System Control)</option>
                </select>
                @error('role') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-show="role === 'voting_user'" class="p-4 bg-teal-50/70 rounded-xl border border-teal-200">
                <label for="discipline_id" class="block text-xs font-bold text-teal-900 uppercase tracking-wider mb-1">
                    Assigned Discipline <span class="text-rose-500">*</span>
                </label>
                <select name="discipline_id" id="discipline_id" class="w-full px-3.5 py-2 bg-white border border-teal-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">-- Select One of 8 Disciplines --</option>
                    @foreach($disciplines as $disc)
                        <option value="{{ $disc->id }}" {{ old('discipline_id') == $disc->id ? 'selected' : '' }}>
                            {{ $disc->discipline_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-teal-700 mt-1.5 font-medium">
                    Strict Security Rule: Voting Users can ONLY rank candidates belonging to their assigned discipline.
                </p>
                @error('discipline_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                <label for="active" class="text-xs font-semibold text-slate-700">Account is Active and Enabled</label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
