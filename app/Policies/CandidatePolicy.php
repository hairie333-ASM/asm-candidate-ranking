<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;
use App\Models\User;

class CandidatePolicy
{
    /**
     * Determine whether the user can view any candidates across all disciplines.
     */
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    /**
     * Determine whether the user can view a specific candidate.
     * Allowed across ALL 8 disciplines.
     */
    public function view(User $user, Candidate $candidate): bool
    {
        return $user->active;
    }

    /**
     * Determine whether the user can rank the candidate.
     * HARD SECURITY BOUNDARY:
     * User's assigned discipline MUST equal Candidate's discipline.
     */
    public function rank(User $user, Candidate $candidate): bool
    {
        return $user->active
            && ($user->isVotingUser() || $user->isAdmin())
            && ! is_null($user->discipline_id)
            && (int) $user->discipline_id === (int) $candidate->discipline_id;
    }

    /**
     * Determine whether the user can submit due diligence for the candidate.
     * Allowed across ALL 8 disciplines.
     */
    public function submitDueDiligence(User $user, Candidate $candidate): bool
    {
        return $user->active;
    }

    /**
     * Determine whether the user can manage candidates (Admin only).
     */
    public function manage(User $user): bool
    {
        return $user->active && $user->isAdmin();
    }
}
