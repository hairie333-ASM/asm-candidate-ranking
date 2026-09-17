@extends('layouts.app')

@section('title', 'Ranking Exercise Closed')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-8 sm:p-12">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-3">Ranking Exercise Not Active</h1>
        
        <p class="text-slate-600 max-w-xl mx-auto mb-8">
            @if($exercise)
                The ranking exercise <strong class="text-slate-800">"{{ $exercise->title }}"</strong> is currently <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 uppercase tracking-wide">{{ $exercise->status }}</span>.
                @if($exercise->status === 'Scheduled')
                    It is scheduled to open on {{ $exercise->start_date ? $exercise->start_date->format('d M Y, h:i A') : 'a later date' }}.
                @elseif($exercise->status === 'Closed')
                    It officially concluded on {{ $exercise->end_date ? $exercise->end_date->format('d M Y, h:i A') : 'the deadline' }}.
                @endif
            @else
                There is currently no active ranking exercise configured in the system.
            @endif
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Return to Dashboard
            </a>
            <a href="{{ route('due-diligence.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Due Diligence Review
            </a>
        </div>
    </div>
</div>
@endsection
