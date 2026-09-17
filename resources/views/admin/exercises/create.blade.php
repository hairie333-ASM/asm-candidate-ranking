@extends('layouts.app')

@section('title', 'Create Ranking Exercise')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.exercises.index') }}" class="hover:text-slate-800">Exercises</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">New Exercise</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-teal-900 px-6 py-4 text-white">
            <h1 class="text-lg font-bold">Schedule Ranking Cycle</h1>
            <p class="text-xs text-teal-200 mt-0.5">Define exercise status, schedule, and resubmission permissions.</p>
        </div>

        <form method="POST" action="{{ route('admin.exercises.store') }}" class="p-6 sm:p-8 space-y-5">
            @csrf

            <div>
                <label for="exercise_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Exercise Title <span class="text-rose-500">*</span></label>
                <input type="text" name="exercise_name" id="exercise_name" required value="{{ old('exercise_name') }}" placeholder="e.g. ASM Fellowship Election 2026 / 2027" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 @error('exercise_name') border-rose-500 @enderror">
                @error('exercise_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description / Guidelines</label>
                <textarea name="description" id="description" rows="3" placeholder="Instructions shown to voters on their ranking dashboard..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status <span class="text-rose-500">*</span></label>
                <select name="status" id="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">
                    <option value="Open" {{ old('status', 'Open') === 'Open' ? 'selected' : '' }}>Open (Accepting Ballots)</option>
                    <option value="Draft" {{ old('status') === 'Draft' ? 'selected' : '' }}>Draft (Inactive)</option>
                    <option value="Closed" {{ old('status') === 'Closed' ? 'selected' : '' }}>Closed (Submissions Locked)</option>
                </select>
                @error('status') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_datetime" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime" value="{{ old('start_datetime') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label for="end_datetime" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">End Date & Time (Deadline)</label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime" value="{{ old('end_datetime') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 @error('end_datetime') border-rose-500 @enderror">
                    @error('end_datetime') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.exercises.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">Create Exercise</button>
            </div>
        </form>
    </div>
</div>
@endsection
