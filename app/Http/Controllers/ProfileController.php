<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('discipline');

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->auditService->log(
            action: 'Password Changed',
            recordType: 'User',
            recordId: $user->id,
            description: "User {$user->email} updated their password.",
            user: $user
        );

        return back()->with('success', 'Your password has been changed successfully.');
    }
}
