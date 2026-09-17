<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDisciplineController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(): View
    {
        $disciplines = Discipline::withCount(['candidates', 'users'])
            ->orderBy('display_order')
            ->get();

        return view('admin.disciplines.index', [
            'disciplines' => $disciplines,
        ]);
    }

    public function create(): View
    {
        return view('admin.disciplines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'discipline_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'display_order' => ['integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $discipline = Discipline::create([
            'discipline_name' => $validated['discipline_name'],
            'description' => $validated['description'],
            'display_order' => $request->input('display_order', 0),
            'active' => $request->boolean('active', true),
        ]);

        $this->auditService->log(
            action: 'Discipline Created',
            recordType: 'Discipline',
            recordId: $discipline->id,
            description: "Created discipline: {$discipline->discipline_name}"
        );

        return redirect()->route('admin.disciplines.index')->with('success', "Discipline '{$discipline->discipline_name}' created successfully.");
    }

    public function edit(Discipline $discipline): View
    {
        return view('admin.disciplines.edit', [
            'discipline' => $discipline,
        ]);
    }

    public function update(Request $request, Discipline $discipline): RedirectResponse
    {
        $validated = $request->validate([
            'discipline_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'display_order' => ['integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $discipline->update([
            'discipline_name' => $validated['discipline_name'],
            'description' => $validated['description'],
            'display_order' => $request->input('display_order', 0),
            'active' => $request->boolean('active', true),
        ]);

        $this->auditService->log(
            action: 'Discipline Edited',
            recordType: 'Discipline',
            recordId: $discipline->id,
            description: "Updated discipline: {$discipline->discipline_name}"
        );

        return redirect()->route('admin.disciplines.index')->with('success', "Discipline '{$discipline->discipline_name}' updated successfully.");
    }

    public function toggleActive(Discipline $discipline): RedirectResponse
    {
        $discipline->update(['active' => ! $discipline->active]);

        $this->auditService->log(
            action: $discipline->active ? 'Discipline Activated' : 'Discipline Deactivated',
            recordType: 'Discipline',
            recordId: $discipline->id,
            description: 'Set status to '.($discipline->active ? 'Active' : 'Inactive')
        );

        return back()->with('success', "Discipline '{$discipline->discipline_name}' status updated.");
    }
}
