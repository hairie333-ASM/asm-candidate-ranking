<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\Ranking;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RankingService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Get the active open ranking exercise.
     */
    public function getActiveExercise(): ?RankingExercise
    {
        return RankingExercise::where('status', 'Open')
            ->where(function ($query) {
                $query->whereNull('start_datetime')
                    ->orWhere('start_datetime', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_datetime')
                    ->orWhere('end_datetime', '>=', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Get candidates that the user is strictly authorised to rank.
     * Enforces: candidate.discipline_id == auth()->user()->discipline_id
     */
    public function getEligibleCandidates(User $user): Collection
    {
        if (! $user->discipline_id || ! $user->active) {
            return new Collection;
        }

        return Candidate::where('discipline_id', $user->discipline_id)
            ->where('active', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Determine if candidate ranking is required for a given discipline.
     */
    public function isRankingRequiredForDiscipline(Discipline|int $discipline): bool
    {
        if (! $discipline instanceof Discipline) {
            $discipline = Discipline::find($discipline);
        }

        return $discipline ? $discipline->isRankingRequired() : false;
    }

    /**
     * Determine if the ranking requirement is completed / satisfied for a user.
     * Single-candidate disciplines automatically satisfy the ranking requirement.
     */
    public function isCompletedForUser(User $user, ?RankingExercise $exercise = null): bool
    {
        if (! $user->discipline_id) {
            return false;
        }

        $candidateCount = Candidate::where('discipline_id', $user->discipline_id)
            ->where('active', true)
            ->count();

        // If candidate count == 1, ranking is not required and automatically completed
        if ($candidateCount === 1) {
            return true;
        }

        if ($candidateCount === 0) {
            return true;
        }

        $submission = $this->getUserSubmission($user, $exercise);

        return $submission && $submission->status === 'SUBMITTED';
    }

    /**
     * Get existing submission for the user in the specified or active exercise.
     */
    public function getUserSubmission(User $user, ?RankingExercise $exercise = null): ?RankingSubmission
    {
        $exercise = $exercise ?? $this->getActiveExercise();
        if (! $exercise || ! $user->discipline_id) {
            return null;
        }

        return RankingSubmission::with(['rankings.candidate'])
            ->where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)
            ->where('discipline_id', $user->discipline_id)
            ->first();
    }

    /**
     * Validate and persist rankings with 3-layer integrity checks.
     *
     * @param  array<int, int|string>  $rankingsData  Map of candidate_id => ranking_number
     */
    public function validateAndSubmit(User $user, RankingExercise $exercise, array $rankingsData): RankingSubmission
    {
        // 1. Authenticated user validation
        if (! $user->active) {
            throw ValidationException::withMessages([
                'user' => 'Your user account is inactive. Please contact the administrator.',
            ]);
        }

        if (! $user->discipline_id) {
            throw new AuthorizationException('You do not have an assigned discipline to rank candidates.');
        }

        // 2. Exercise state validation
        if (! $exercise->isOpen() || ! $exercise->isWithinTimeWindow()) {
            throw ValidationException::withMessages([
                'exercise' => 'This ranking exercise is currently closed or outside the active submission window.',
            ]);
        }

        // 3. Submission lock validation
        $existingSubmission = $this->getUserSubmission($user, $exercise);
        if ($existingSubmission && $existingSubmission->status === 'SUBMITTED' && ! $exercise->allow_resubmission) {
            throw ValidationException::withMessages([
                'submission' => 'You have already submitted your final ranking for this exercise. Submissions are locked.',
            ]);
        }

        // 4. HARD SECURITY BOUNDARY: Candidate Discipline Verification
        // Check if ANY submitted candidate belongs to another discipline
        $submittedCandidateIds = array_keys($rankingsData);
        $mismatchedCandidates = Candidate::whereIn('id', $submittedCandidateIds)
            ->where('discipline_id', '!=', $user->discipline_id)
            ->exists();

        if ($mismatchedCandidates) {
            $this->auditService->log(
                action: 'Security Violation: Attempted Cross-Discipline Ranking',
                recordType: 'Discipline',
                recordId: $user->discipline_id,
                description: 'User attempted to rank candidates outside assigned discipline.',
                user: $user
            );

            throw new AuthorizationException(
                'Security violation: You are strictly forbidden from ranking candidates outside your assigned discipline.'
            );
        }

        // 5. Eligible candidates retrieval (server-authoritative)
        $eligibleCandidates = $this->getEligibleCandidates($user);
        $totalCandidates = $eligibleCandidates->count();

        if ($totalCandidates === 0) {
            throw ValidationException::withMessages([
                'candidates' => 'There are no active candidates available for ranking in your assigned discipline.',
            ]);
        }

        if ($totalCandidates === 1) {
            throw ValidationException::withMessages([
                'candidates' => 'This discipline has only one candidate. Ranking exercise is not required.',
            ]);
        }

        // 6. Completeness Check: Every eligible candidate in user's discipline must be ranked
        $eligibleIds = $eligibleCandidates->pluck('id')->all();
        sort($eligibleIds);
        $sortedSubmittedIds = array_map('intval', $submittedCandidateIds);
        sort($sortedSubmittedIds);

        if ($eligibleIds !== $sortedSubmittedIds) {
            throw ValidationException::withMessages([
                'rankings' => 'Incomplete ranking submission. You must assign a ranking to every candidate in your assigned discipline.',
            ]);
        }

        // 7. Sequence and Uniqueness Validation: Must form exact complete sequence 1..N
        $rankingNumbers = array_values($rankingsData);
        $parsedRanks = [];

        foreach ($rankingNumbers as $rn) {
            if (! is_numeric($rn)) {
                throw ValidationException::withMessages([
                    'rankings' => 'Invalid ranking value detected. Every ranking must be a valid number.',
                ]);
            }

            $intVal = (int) $rn;
            if ($intVal < 1 || $intVal > $totalCandidates) {
                throw ValidationException::withMessages([
                    'rankings' => "Ranking number {$intVal} is outside the valid range of 1 to {$totalCandidates}.",
                ]);
            }

            $parsedRanks[] = $intVal;
        }

        // Check for duplicates
        if (count($parsedRanks) !== count(array_unique($parsedRanks))) {
            throw ValidationException::withMessages([
                'rankings' => 'Duplicate ranking detected. Each candidate must receive a unique ranking number.',
            ]);
        }

        // Check exact complete sequence 1..N
        sort($parsedRanks);
        $expectedSequence = range(1, $totalCandidates);
        if ($parsedRanks !== $expectedSequence) {
            throw ValidationException::withMessages([
                'rankings' => "The submitted rankings do not form a complete sequence from 1 to {$totalCandidates}.",
            ]);
        }

        // 8. Transactional persistence
        return DB::transaction(function () use ($user, $exercise, $rankingsData, $existingSubmission) {
            $isResubmission = $existingSubmission !== null;

            $submission = RankingSubmission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'exercise_id' => $exercise->id,
                    'discipline_id' => $user->discipline_id,
                ],
                [
                    'status' => 'SUBMITTED',
                    'submitted_at' => now(),
                ]
            );

            // Clear any previous rankings if resubmitting
            Ranking::where('submission_id', $submission->id)->delete();

            // Insert new rankings
            foreach ($rankingsData as $candidateId => $rankingNumber) {
                Ranking::create([
                    'submission_id' => $submission->id,
                    'user_id' => $user->id,
                    'exercise_id' => $exercise->id,
                    'discipline_id' => $user->discipline_id,
                    'candidate_id' => (int) $candidateId,
                    'ranking_number' => (int) $rankingNumber,
                ]);
            }

            $this->auditService->log(
                action: $isResubmission ? 'Ranking Resubmitted' : 'Ranking Submitted',
                recordType: 'RankingSubmission',
                recordId: $submission->id,
                description: "User {$user->email} submitted ranking for Discipline #{$user->discipline_id}",
                user: $user
            );

            return $submission;
        });
    }

    /**
     * Administrator reopens a locked submission.
     */
    public function reopenSubmission(User $admin, RankingSubmission $submission, string $reason): bool
    {
        if (! $admin->isAdmin()) {
            throw new AuthorizationException('Only administrators can reopen ranking submissions.');
        }

        $submission->update([
            'status' => 'DRAFT',
            'submitted_at' => null,
        ]);

        $this->auditService->log(
            action: 'Ranking Reopened',
            recordType: 'RankingSubmission',
            recordId: $submission->id,
            description: "Admin {$admin->email} reopened submission for user #{$submission->user_id}. Reason: {$reason}",
            user: $admin
        );

        return true;
    }
}
