@extends('layouts.app')

@section('title', 'Create Discipline')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <nav class="flex text-xs text-slate-500 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Admin</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.disciplines.index') }}" class="hover:text-slate-800">Disciplines</a>
        <span class="mx-2">/</span>
        <span class="text-teal-700 font-semibold">New Discipline</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-teal-900 px-6 py-4 text-white">
            <h1 class="text-lg font-bold">Add Academic Discipline</h1>
            <p class="text-xs text-teal-200 mt-0.5">Define new discipline entity and display ordering.</p>
        </div>

        <form method="POST" action="{{ route('admin.disciplines.store') }}" class="p-6 sm:p-8 space-y-5">
            @csrf

            <div>
                <label for="discipline_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discipline Name <span class="text-rose-500">*</span></label>
                <input type="text" name="discipline_name" id="discipline_name" required value="{{ old('discipline_name') }}" placeholder="e.g. BAES - Biological Agriculture and Environmental Sciences" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500 @error('discipline_name') border-rose-500 @enderror">
                @error('discipline_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Scope and subject areas covered..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="display_order" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Display Order</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 h-4 w-4">
                <label for="active" class="text-xs font-semibold text-slate-700">Discipline is Active</label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.disciplines.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">Save Discipline</button>
            </div>
        </form>
    </div>
</div>
@endsection
