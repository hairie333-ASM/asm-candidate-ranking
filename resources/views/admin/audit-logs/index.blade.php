@extends('layouts.app')

@section('title', 'System Audit Trail & Security Logs')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
            <span class="mx-2">/</span>
            <span class="text-teal-700 font-semibold">Audit Logs</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">System Audit Trail &amp; Activity Log</h1>
        <p class="text-sm text-slate-600 mt-1">
            Immutable log of ranking submissions, ballot unlocks, administrative edits, and confidential file access.
        </p>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="q" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Search Keywords / IP</label>
                <input type="text" 
                       name="q" 
                       id="q"
                       value="{{ $filters['q'] ?? '' }}" 
                       placeholder="Search action details, description, IP..." 
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="sm:col-span-3">
                <label for="user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">User</label>
                <select name="user_id" id="user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string)($filters['user_id'] ?? '') === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label for="action" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Action Type</label>
                <select name="action" id="action" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ (string)($filters['action'] ?? '') === (string)$act ? 'selected' : '' }}>
                            {{ $act }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex space-x-2">
                <button type="submit" class="w-full py-2 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Filter
                </button>
                @if(!empty($filters['q']) || !empty($filters['user_id']) || !empty($filters['action']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Clear">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Audit Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 text-xs text-slate-500 flex items-center justify-between">
            <span>Displaying <strong class="text-slate-800">{{ $logs->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $logs->lastItem() ?? 0 }}</strong> of <strong class="text-slate-800">{{ $logs->total() }}</strong> audit events</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Timestamp</th>
                        <th scope="col" class="px-5 py-3 font-semibold">User</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Action</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Description</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Target Entity</th>
                        <th scope="col" class="px-4 py-3 font-semibold font-mono">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($log->user)
                                    <span class="font-bold text-slate-900 block">{{ $log->user->name }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $log->user->email }}</span>
                                @else
                                    <span class="text-slate-400 italic">System / Unauthenticated</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ str_contains(strtolower($log->action), 'reopen') ? 'bg-amber-100 text-amber-800' : (str_contains(strtolower($log->action), 'submit') ? 'bg-emerald-100 text-emerald-800' : (str_contains(strtolower($log->action), 'delete') ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 max-w-md">
                                <span class="leading-relaxed">{{ $log->description }}</span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                @if($log->record_type)
                                    <span class="font-mono">{{ class_basename($log->record_type) }} #{{ $log->record_id }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No audit log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
