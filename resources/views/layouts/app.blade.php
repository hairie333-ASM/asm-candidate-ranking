<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Selection Exercise for New Election Fellow' }} - Academy of Sciences Malaysia</title>

    <!-- Tailwind CSS CDN (Guarantees zero-build deployment on Streamline hosting) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'asm-blue': {
                            DEFAULT: '#302556',
                            50: '#f5f4f9',
                            100: '#ece9f3',
                            200: '#dcd7e8',
                            300: '#bebad4',
                            400: '#75689e',
                            500: '#4c3b7b',
                            600: '#3c2e68',
                            700: '#302556',
                            800: '#261e46',
                            900: '#1e1738',
                            950: '#140f26',
                        },
                        'asm-green': {
                            DEFAULT: '#008442',
                            50: '#effcf4',
                            100: '#daf8e6',
                            200: '#b7f0d0',
                            300: '#83e3af',
                            400: '#48ce88',
                            500: '#1fb568',
                            600: '#008442',
                            700: '#007038',
                            800: '#06582f',
                            900: '#074828',
                            950: '#022815',
                        },
                        'asm-red': {
                            DEFAULT: '#E31F21',
                            50: '#fff1f1',
                            100: '#ffe1e1',
                            200: '#ffc7c7',
                            300: '#ffa0a0',
                            400: '#ff696a',
                            500: '#fa3b3e',
                            600: '#E31F21',
                            700: '#be1416',
                            800: '#9d1416',
                            900: '#821719',
                            950: '#470708',
                        },
                        teal: {
                            50: '#effcf4',
                            100: '#daf8e6',
                            200: '#b7f0d0',
                            300: '#83e3af',
                            400: '#48ce88',
                            500: '#008442',
                            600: '#008442',
                            700: '#007038',
                            800: '#302556',
                            900: '#261e46',
                            950: '#1e1738',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js (Reactive Ranking & Modals) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-[#008442] selection:text-white">

    <!-- Institutional Top Bar -->
    <header class="bg-[#302556] text-white border-b border-[#008442]/40 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Branding -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="bg-white rounded-lg p-1.5 shadow-sm border border-white/20 flex items-center justify-center">
                            <img src="{{ asset('images/asm-logo-horizontal.png') }}" alt="Academy of Sciences Malaysia" class="h-9 sm:h-10 w-auto object-contain">
                        </div>
                        <div class="hidden sm:block border-l border-white/20 pl-3">
                            <span class="block text-[11px] font-semibold tracking-wider text-emerald-300 uppercase">Academy of Sciences Malaysia</span>
                            <span class="block text-sm sm:text-base font-bold tracking-tight text-white group-hover:text-emerald-200 transition-colors">Selection Exercise for New Election Fellow</span>
                        </div>
                    </a>
                </div>

                <!-- Right Side User Status / Quick Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <div class="text-right">
                            <span class="block text-xs text-slate-200 font-medium">{{ Auth::user()->name }}</span>
                            <div class="flex items-center space-x-1.5 justify-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#008442]/80 text-white border border-emerald-400/40">
                                    {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                                </span>
                                @if(Auth::user()->discipline)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#261e46] text-slate-200 border border-white/10" title="{{ Auth::user()->discipline->discipline_name }}">
                                        {{ Auth::user()->discipline->code }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-200 hover:text-white bg-[#261e46] hover:bg-[#1e1738] px-3 py-1.5 rounded-lg border border-white/10 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-white bg-[#008442] hover:bg-[#007038] px-4 py-2 rounded-lg transition shadow-sm">
                            Staff / Voter Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Main Navigation Bar -->
        <nav x-data="{ mobileOpen: false }" class="bg-[#241c42] border-t border-white/10 shadow-inner">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-12">
                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center space-x-1">
                        @guest
                            <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Home
                            </a>
                            <a href="{{ route('other-information') }}" class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('other-information') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Other Information
                            </a>
                            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('login') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Login
                            </a>
                        @else
                            {{-- Common Authenticated Links --}}
                            <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('home') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Home
                            </a>
                            <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                My Dashboard
                            </a>

                            {{-- Voting User Specific Links --}}
                            @if(Auth::user()->isVotingUser())
                                @if(Auth::user()->discipline?->isRankingRequired() ?? true)
                                    <a href="{{ route('ranking.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('ranking.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                        My Ranking
                                    </a>
                                @endif
                                <a href="{{ route('my-submission') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('my-submission') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    {{ (Auth::user()->discipline?->isSingleCandidate() ?? false) ? 'Discipline Status' : 'My Submission' }}
                                </a>
                            @endif

                            {{-- Cross-Discipline Due Diligence (All Authenticated Users) --}}
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('candidates.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('candidates.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Candidate Search (All 8 Disciplines)
                                </a>
                            @endif
                            <a href="{{ route('due-diligence.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('due-diligence.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Due Diligence
                            </a>
                            <a href="{{ route('selection-process') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('selection-process') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Selection Process
                            </a>
                            <a href="{{ route('evaluation-rubric') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('evaluation-rubric') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Evaluation Rubric
                            </a>
                            <a href="{{ route('other-information') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('other-information') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Other Information
                            </a>

                            {{-- Administrator Suite --}}
                            @if(Auth::user()->isAdmin())
                                <span class="text-slate-500 px-1">|</span>
                                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-amber-600 text-white' : 'text-amber-300 hover:bg-[#302556] hover:text-white' }}">
                                    Admin Suite
                                </a>
                                <a href="{{ route('admin.ranking-results.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('admin.ranking-results.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Results
                                </a>
                                <a href="{{ route('admin.candidates.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('admin.candidates.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Candidates
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('admin.users.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Users
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Reports
                                </a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                    Audit Logs
                                </a>
                            @endif

                            <span class="text-slate-500 px-1">|</span>
                            <a href="{{ route('profile.show') }}" class="px-3 py-1.5 rounded-md text-xs font-medium {{ request()->routeIs('profile.*') ? 'bg-[#008442] text-white font-semibold' : 'text-slate-200 hover:bg-[#302556] hover:text-white' }}">
                                Profile
                            </a>
                        @endguest
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex md:hidden">
                        <button type="button" @click="mobileOpen = !mobileOpen" class="text-slate-200 hover:text-white p-2 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path :class="{'hidden': mobileOpen, 'inline-flex': !mobileOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !mobileOpen, 'inline-flex': mobileOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileOpen" x-cloak class="md:hidden bg-[#241c42] px-2 pt-2 pb-3 space-y-1 border-b border-white/10">
                @guest
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-[#302556]">Home</a>
                    <a href="{{ route('other-information') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-[#302556]">Other Information</a>
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-[#302556]">Login</a>
                @else
                    <div class="px-3 py-2 border-b border-white/10 text-xs text-slate-300">
                        Signed in as <span class="font-bold text-white">{{ Auth::user()->name }}</span> ({{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }})
                    </div>
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Home</a>
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">My Dashboard</a>
                    @if(Auth::user()->isVotingUser())
                        @if(Auth::user()->discipline?->isRankingRequired() ?? true)
                            <a href="{{ route('ranking.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">My Ranking</a>
                        @endif
                        <a href="{{ route('my-submission') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">{{ (Auth::user()->discipline?->isSingleCandidate() ?? false) ? 'Discipline Status' : 'My Submission' }}</a>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('candidates.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Candidate Search</a>
                    @endif
                    <a href="{{ route('due-diligence.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Due Diligence</a>
                    <a href="{{ route('selection-process') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Selection Process</a>
                    <a href="{{ route('evaluation-rubric') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Evaluation Rubric</a>
                    <a href="{{ route('other-information') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Other Information</a>
                    <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Profile</a>

                    @if(Auth::user()->isAdmin())
                        <div class="pt-2 border-t border-slate-800">
                            <span class="block px-3 text-xs font-bold uppercase tracking-wider text-amber-400">Admin Functions</span>
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-amber-200 hover:bg-slate-800">Dashboard</a>
                            <a href="{{ route('admin.ranking-results.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Results</a>
                            <a href="{{ route('admin.candidates.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Candidates</a>
                            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Users</a>
                            <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Reports</a>
                            <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-slate-800">Audit Logs</a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-slate-800">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium text-rose-400 hover:bg-slate-800">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
        </nav>
    </header>

    <!-- Main Body Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- System Alerts & Flash Messages -->
        @if(session('success'))
            <div class="mb-5 rounded-lg bg-emerald-50 p-4 border border-emerald-200 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-emerald-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-5 rounded-lg bg-blue-50 p-4 border border-blue-200 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm font-medium text-blue-800">{{ session('info') }}</div>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-5 rounded-lg bg-teal-50 p-4 border border-teal-200 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-teal-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm font-medium text-teal-800">{{ session('status') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-5 rounded-lg bg-rose-50 p-4 border border-rose-200 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-rose-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-rose-800">Please review the following errors:</h3>
                    <ul class="mt-1.5 list-disc list-inside text-sm text-rose-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Institutional Footer -->
    <footer class="bg-[#241c42] text-slate-300 text-xs py-6 border-t border-[#302556] mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/asm-logo-emblem.png') }}" alt="ASM Logo" class="h-6 w-auto object-contain">
                <div>
                    <span class="font-semibold text-white">Academy of Sciences Malaysia (ASM)</span> &copy; {{ date('Y') }}. All rights reserved.
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-300">
                <span>Selection Exercise for New Election Fellow</span>
                <span>&bull;</span>
                <a href="mailto:membership@akademisains.gov.my" class="text-emerald-400 hover:text-emerald-300 underline underline-offset-2">membership@akademisains.gov.my</a>
                <span>&bull;</span>
                <span class="text-amber-300 font-semibold">STRICTLY FOR AUTHORISED USERS ONLY</span>
            </div>
        </div>
    </footer>

    <!-- Global Accessible Reusable Candidate Information Modal -->
    <x-candidate-modal />

    @stack('scripts')
</body>
</html>
