<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceSubmission;
use App\Models\Ranking;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;

class ReportService
{
    /**
     * Compute aggregated results for candidates in a discipline for an exercise.
     *
     * @return array{
     *     discipline: Discipline,
     *     exercise: RankingExercise,
     *     submissions_count: int,
     *     voters: list<array>,
     *     candidates_results: list<array>
     * }
     */
    public function getDisciplineResults(int $disciplineId, ?int $exerciseId = null): array
    {
        $discipline = Discipline::findOrFail($disciplineId);
        $exercise = $exerciseId
            ? RankingExercise::findOrFail($exerciseId)
            : RankingExercise::where('status', 'Open')->latest()->first() ?? RankingExercise::latest()->first();

        if (! $exercise) {
            return [
                'discipline' => $discipline,
                'exercise' => null,
                'submissions_count' => 0,
                'voters' => [],
                'candidates_results' => [],
            ];
        }

        $submissions = RankingSubmission::with(['user', 'rankings'])
            ->where('exercise_id', $exercise->id)
            ->where('discipline_id', $discipline->id)
            ->where('status', 'SUBMITTED')
            ->get();

        $candidates = Candidate::where('discipline_id', $discipline->id)
            ->where('active', true)
            ->orderBy('display_order')
            ->get();

        $voters = $submissions->map(fn ($sub) => [
            'id' => $sub->user->id,
            'name' => $sub->user->name,
            'email' => $sub->user->email,
            'submitted_at' => $sub->submitted_at,
        ])->all();

        $candidatesResults = [];

        foreach ($candidates as $candidate) {
            $candidateRanks = [];
            $rank1Count = 0;
            $rank2Count = 0;

            foreach ($submissions as $sub) {
                $rankingRecord = $sub->rankings->firstWhere('candidate_id', $candidate->id);
                $rn = $rankingRecord?->ranking_number;
                $candidateRanks[$sub->user_id] = $rn;

                if ($rn === 1) {
                    $rank1Count++;
                } elseif ($rn === 2) {
                    $rank2Count++;
                }
            }

            $validRanks = array_filter(array_values($candidateRanks), fn ($v) => ! is_null($v));
            $totalRankings = count($validRanks);
            $avgRank = $totalRankings > 0 ? (array_sum($validRanks) / $totalRankings) : null;

            $candidatesResults[] = [
                'candidate' => $candidate,
                'ranks' => $candidateRanks,
                'average_rank' => $avgRank !== null ? round($avgRank, 2) : 0,
                'rank_1_count' => $rank1Count,
                'rank_2_count' => $rank2Count,
                'total_submissions' => $totalRankings,
            ];
        }

        // Sort candidates based on tie-breaking rules:
        // 1. Lower average rank
        // 2. Higher Rank 1 count
        // 3. Higher Rank 2 count
        usort($candidatesResults, function ($a, $b) {
            if ($a['total_submissions'] === 0 && $b['total_submissions'] === 0) {
                return $a['candidate']->display_order <=> $b['candidate']->display_order;
            }
            if ($a['total_submissions'] === 0) {
                return 1;
            }
            if ($b['total_submissions'] === 0) {
                return -1;
            }

            // Lower average rank is better
            if ($a['average_rank'] != $b['average_rank']) {
                return $a['average_rank'] <=> $b['average_rank'];
            }

            // Higher Rank 1 count is better
            if ($a['rank_1_count'] != $b['rank_1_count']) {
                return $b['rank_1_count'] <=> $a['rank_1_count'];
            }

            // Higher Rank 2 count is better
            if ($a['rank_2_count'] != $b['rank_2_count']) {
                return $b['rank_2_count'] <=> $a['rank_2_count'];
            }

            return 0; // TIE
        });

        // Assign positions and flag ties
        $currentRank = 1;
        foreach ($candidatesResults as $index => &$res) {
            if ($index > 0) {
                $prev = $candidatesResults[$index - 1];
                if (
                    $res['average_rank'] == $prev['average_rank'] &&
                    $res['rank_1_count'] == $prev['rank_1_count'] &&
                    $res['rank_2_count'] == $prev['rank_2_count']
                ) {
                    $res['position'] = $prev['position'];
                    $res['is_tie'] = true;
                    $candidatesResults[$index - 1]['is_tie'] = true;
                } else {
                    $res['position'] = $index + 1;
                    $res['is_tie'] = false;
                }
            } else {
                $res['position'] = 1;
                $res['is_tie'] = false;
            }
        }
        unset($res);

        $isSingleCandidate = count($candidatesResults) === 1;
        $isRankingRequired = count($candidatesResults) > 1;

        return [
            'discipline' => $discipline,
            'exercise' => $exercise,
            'submissions_count' => $submissions->count(),
            'voters' => $voters,
            'candidates_results' => $candidatesResults,
            'is_single_candidate' => $isSingleCandidate,
            'is_ranking_required' => $isRankingRequired,
            'status_text' => $isSingleCandidate ? 'Ranking: Not Required (Single Candidate)' : ($submissions->count() > 0 ? 'Active' : 'Pending'),
        ];
    }

    /**
     * Generate CSV content for discipline ranking results.
     */
    public function generateRankingCsv(int $disciplineId, ?int $exerciseId = null): string
    {
        $data = $this->getDisciplineResults($disciplineId, $exerciseId);
        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'Position',
            'Candidate Name',
            'Organisation',
            'Average Rank',
            'Rank 1 Count',
            'Rank 2 Count',
            'Total Submissions',
            'Status',
        ]);

        $isSingleCandidate = $data['is_single_candidate'] ?? false;

        foreach ($data['candidates_results'] as $row) {
            $status = $isSingleCandidate
                ? 'Ranking: Not Required (Single Candidate)'
                : ($row['is_tie'] ? 'Tied - Requires Review' : 'Final');
            $avgRank = $isSingleCandidate ? 'N/A' : $row['average_rank'];
            $position = $isSingleCandidate
                ? '1 (Sole Candidate)'
                : ($row['position'].($row['is_tie'] ? ' (TIE)' : ''));

            fputcsv($output, [
                $position,
                $row['candidate']->candidate_name,
                $row['candidate']->organisation,
                $avgRank,
                $row['rank_1_count'],
                $row['rank_2_count'],
                $row['total_submissions'],
                $status,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }

    /**
     * Generate CSV content for due diligence submissions.
     */
    public function generateDueDiligenceCsv(?int $disciplineId = null): string
    {
        $query = DueDiligenceSubmission::with([
            'candidate.discipline',
            'category',
            'user',
            'userDiscipline',
            'supportingDocuments',
        ]);

        if ($disciplineId) {
            $query->whereHas('candidate', fn ($q) => $q->where('discipline_id', $disciplineId));
        }

        $submissions = $query->latest()->get();
        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'Submission ID',
            'Candidate Name',
            'Candidate Discipline',
            'Reviewer Name',
            'Reviewer Assigned Discipline',
            'Category',
            'Comment',
            'Reference 1 Name',
            'Reference 1 Designation',
            'Reference 1 Organisation',
            'Reference 1 Contact Number',
            'Reference 1 Email',
            'Reference 2 Name',
            'Reference 2 Designation',
            'Reference 2 Organisation',
            'Reference 2 Contact Number',
            'Reference 2 Email',
            'Supporting Documents',
            'Submission Date',
        ]);

        foreach ($submissions as $sub) {
            $docs = $sub->supportingDocuments->pluck('original_filename')->implode('; ');
            fputcsv($output, [
                $sub->id,
                $sub->candidate->candidate_name,
                $sub->candidate->discipline?->discipline_name ?? 'N/A',
                $sub->user->name,
                $sub->userDiscipline?->discipline_name ?? 'General / None',
                $sub->category?->name ?? 'General',
                $sub->comment,
                $sub->reference_1_name ?? '',
                $sub->reference_1_designation ?? '',
                $sub->reference_1_organisation ?? '',
                $sub->reference_1_contact_number ?? '',
                $sub->reference_1_email ?? '',
                $sub->reference_2_name ?? '',
                $sub->reference_2_designation ?? '',
                $sub->reference_2_organisation ?? '',
                $sub->reference_2_contact_number ?? '',
                $sub->reference_2_email ?? '',
                $docs ?: 'None',
                $sub->created_at->toDateTimeString(),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }
}
