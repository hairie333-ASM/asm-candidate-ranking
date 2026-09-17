<?php

namespace App\Policies;

use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Models\User;
use App\Models\User;

class DueDiligencePolicy
{
    /**
     * Determine whether the user can view the due diligence list.
     */
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    /**
     * Determine whether the user can view a specific submission.
     */
    public function view(User $user, DueDiligenceSubmission $submission): bool
    {
        return $user->active && ($user->isAdmin() || $user->isReviewer() || $user->id === $submission->user_id);
    }

    /**
     * Determine whether the user can create a due diligence submission.
     */
    public function create(User $user): bool
    {
        return $user->active;
    }

    /**
     * Determine whether the user can view/download a confidential supporting document.
     */
    public function viewDocument(User $user, SupportingDocument $document): bool
    {
        return $user->active && (
            $user->isAdmin() ||
            $user->isReviewer() ||
            $user->id === $document->submission?->user_id
        );
    }
}
