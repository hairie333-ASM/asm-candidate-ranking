<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User;

class RankingPolicy
{
    /**
     * Determine whether the user can access the ranking board.
     */
    public function view(User $user): bool
    {
        return $user->active && ($user->isVotingUser() || $user->isAdmin()) && ! is_null($user->discipline_id);
    }

    /**
     * Determine whether the user can submit rankings.
     */
    public function submit(User $user): bool
    {
        return $user->active && ($user->isVotingUser() || $user->isAdmin()) && ! is_null($user->discipline_id);
    }

    /**
     * Determine whether the user can view aggregate results.
     */
    public function viewResults(User $user): bool
    {
        return $user->active && ($user->isAdmin() || $user->isReviewer());
    }

    /**
     * Determine whether the user can reopen a submission.
     */
    public function reopen(User $user): bool
    {
        return $user->active && $user->isAdmin();
    }
}
