@extends('layouts.app')

@section('title', 'My Profile & Account Security')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-teal-700 font-semibold">User Profile</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">User Profile & Security</h1>
        <p class="text-sm text-slate-600 mt-1">
            Manage your account credentials and view your institutional assignment.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Profile Details Column --}}
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-tr from-teal-700 to-teal-500 text-white rounded-2xl flex items-center justify-center font-bold text-2xl mx-auto mb-4 shadow-md">
                    {{ substr($user->name, 0, 2) }}
                </div>
                <h2 class="text-base font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>
                <div class="mt-3 flex justify-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 text-left space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 uppercase text-[10px] font-semibold block">Discipline Assignment</span>
                        @if($user->discipline)
                            <span class="font-bold text-slate-800">{{ $user->discipline->discipline_name }}</span>
                        @else
                            <span class="text-slate-500 italic">None (All Disciplines / Admin)</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase text-[10px] font-semibold block">Account Status</span>
                        <span class="inline-flex items-center text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Active & Verified
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase text-[10px] font-semibold block">Last Sign In</span>
                        <span class="text-slate-600">{{ $user->last_login ? $user->last_login->format('d M Y, h:i A') : 'Current Session' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Change Password Form Column --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="text-lg font-bold text-slate-900 mb-1">Update Account Password</h2>
                <p class="text-xs text-slate-500 mb-6">
                    Ensure your account is protected using a strong, unique password with at least 8 characters.
                </p>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               name="current_password" 
                               id="current_password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 @error('current_password') border-rose-500 @enderror">
                        @error('current_password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-rose-500 @enderror">
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Confirm New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition shadow-sm inline-flex items-center">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
