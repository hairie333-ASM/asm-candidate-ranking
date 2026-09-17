@extends('layouts.app', ['title' => 'Sign In - Selection Exercise for New Election Fellow'])

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center py-10 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center space-y-3">
        <div class="flex justify-center">
            <img src="{{ asset('images/asm-logo-horizontal.png') }}" alt="Academy of Sciences Malaysia" class="h-16 w-auto object-contain">
        </div>
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">
            Sign in to Your Account
        </h2>
        <p class="text-xs text-slate-500 font-medium">
            Confidential Selection Exercise for New Election Fellow Portal
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-slate-200 sm:px-10">
            <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
                @csrf

                {{-- Username / Email Address --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Username / Email Address
                    </label>
                    <div class="mt-1.5">
                        <input id="email" 
                               name="email" 
                               type="text" 
                               autocomplete="username" 
                               required 
                               value="{{ old('email') }}"
                               placeholder="Enter your registered email or username"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556]">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-[#E31F21] font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               placeholder="••••••••"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#302556] focus:border-[#302556]">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-[#E31F21] font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confidentiality Agreement Tickbox (Mandatory) --}}
                <div class="pt-3 pb-2 border-t border-b border-slate-100 bg-slate-50/70 p-3.5 rounded-xl">
                    <label class="relative flex items-start cursor-pointer group select-none">
                        <div class="flex items-center h-5">
                            <input id="confidentiality_agreement" 
                                   name="confidentiality_agreement" 
                                   type="checkbox" 
                                   value="1"
                                   required
                                   {{ old('confidentiality_agreement') ? 'checked' : '' }}
                                   class="h-4 w-4 text-[#008442] focus:ring-[#008442] border-slate-300 rounded cursor-pointer transition">
                        </div>
                        <div class="ml-3 text-xs leading-relaxed text-slate-700">
                            <span class="font-medium group-hover:text-[#302556] transition-colors">
                                I agree to maintain the confidentiality of the contents made available to me in this exercise.
                            </span>
                        </div>
                    </label>
                    @error('confidentiality_agreement')
                        <p class="mt-2 text-xs text-[#E31F21] font-semibold flex items-center">
                            <svg class="w-4 h-4 mr-1 text-[#E31F21] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox" 
                               class="h-4 w-4 text-[#008442] focus:ring-[#008442] border-slate-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-xs text-slate-600 font-medium cursor-pointer">
                            Remember this device
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-[#302556] hover:bg-[#241c42] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#302556] transition-colors uppercase tracking-wider">
                        LOGIN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
