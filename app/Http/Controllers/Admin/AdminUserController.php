<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $query = User::with('discipline');

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('discipline_id')) {
            $query->where('discipline_id', (int) $request->input('discipline_id'));
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.users.index', [
            'users' => $users,
            'disciplines' => $disciplines,
            'filters' => $request->only(['q', 'role', 'discipline_id']),
        ]);
    }

    public function create(): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.users.create', [
            'disciplines' => $disciplines,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', 'in:voting_user,reviewer,administrator'],
            'discipline_id' => ['nullable', 'required_if:role,voting_user', 'exists:disciplines,id'],
            'active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'discipline_id' => $validated['role'] === 'voting_user' ? $validated['discipline_id'] : null,
            'active' => $request->boolean('active', true),
        ]);

        $this->auditService->log(
            action: 'User Created',
            recordType: 'User',
            recordId: $user->id,
            description: "Created user {$user->email} with role '{$user->role}'".($user->discipline_id ? " and assigned discipline #{$user->discipline_id}" : '')
        );

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.users.edit', [
            'user' => $user,
            'disciplines' => $disciplines,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:voting_user,reviewer,administrator'],
            'discipline_id' => ['nullable', 'required_if:role,voting_user', 'exists:disciplines,id'],
            'active' => ['boolean'],
        ]);

        $oldRole = $user->role;
        $oldDiscipline = $user->discipline_id;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'discipline_id' => $validated['role'] === 'voting_user' ? $validated['discipline_id'] : null,
            'active' => $request->boolean('active'),
        ]);

        if ($oldRole !== $user->role) {
            $this->auditService->log(
                action: 'User Role Changed',
                recordType: 'User',
                recordId: $user->id,
                description: "Role changed from '{$oldRole}' to '{$user->role}' for {$user->email}"
            );
        }

        if ($oldDiscipline != $user->discipline_id) {
            $this->auditService->log(
                action: 'User Discipline Changed',
                recordType: 'User',
                recordId: $user->id,
                description: 'Discipline assignment changed from #'.($oldDiscipline ?? 'None').' to #'.($user->discipline_id ?? 'None')." for {$user->email}"
            );
        }

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} updated successfully.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['active' => ! $user->active]);

        $this->auditService->log(
            action: $user->active ? 'User Activated' : 'User Deactivated',
            recordType: 'User',
            recordId: $user->id,
            description: 'User status set to '.($user->active ? 'Active' : 'Inactive')
        );

        return back()->with('success', "User {$user->name} status updated.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', Password::min(8)],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        $this->auditService->log(
            action: 'User Password Reset',
            recordType: 'User',
            recordId: $user->id,
            description: "Password reset for user {$user->email} by admin"
        );

        return back()->with('success', "Password for {$user->name} has been reset successfully.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', 'administrator')->count() <= 1) {
            return back()->with('error', 'Cannot delete the only remaining administrator account.');
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;
        $userId = $user->id;

        DB::transaction(function () use ($user) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        $this->auditService->log(
            action: 'User Deleted',
            recordType: 'User',
            recordId: $userId,
            description: "Deleted user {$userName} ({$userEmail}) with role '{$userRole}'"
        );

        return redirect()->route('admin.users.index')->with('success', "User {$userName} has been deleted successfully.");
    }
}
