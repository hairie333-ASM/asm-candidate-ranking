<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CandidateService
{
    /**
     * Search candidates across all 8 disciplines with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Candidate::with('discipline')
            ->where('active', true);

        if (! empty($filters['q'])) {
            $keyword = '%'.trim($filters['q']).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('candidate_name', 'like', $keyword)
                    ->orWhere('candidate_title', 'like', $keyword)
                    ->orWhere('organisation', 'like', $keyword)
                    ->orWhere('area_of_expertise', 'like', $keyword);
            });
        }

        if (! empty($filters['discipline_id']) && $filters['discipline_id'] !== 'all') {
            $query->where('discipline_id', (int) $filters['discipline_id']);
        }

        return $query->orderBy('discipline_id')
            ->orderBy('display_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get candidate with relations.
     */
    public function getCandidate(int $id): Candidate
    {
        return Candidate::with(['discipline', 'dueDiligenceSubmissions.category', 'dueDiligenceSubmissions.user'])
            ->findOrFail($id);
    }

    /**
     * Format candidate data for JSON API and Alpine modal.
     */
    public function formatForModal(Candidate $candidate): array
    {
        return [
            'id' => $candidate->id,
            'discipline_id' => $candidate->discipline_id,
            'discipline_name' => $candidate->discipline?->discipline_name ?? '',
            'nominated_discipline_name' => $candidate->discipline?->discipline_name ?? '',
            'candidate_name' => $candidate->candidate_name,
            'full_name' => $candidate->candidate_name,
            'candidate_title' => $candidate->candidate_title,
            'title_designation' => $candidate->candidate_title,
            'organisation' => $candidate->organisation,
            'photo_url' => $candidate->photo_url ?: 'https://ui-avatars.com/api/?name='.urlencode($candidate->candidate_name).'&background=0D9488&color=fff&size=256',
            'affiliation_to_asm' => $candidate->affiliation_to_asm ?: 'ASM Fellow Nominee',
            'onedrive_dossier_link' => $candidate->nomination_form_url,
            'nomination_form_url' => $candidate->nomination_form_url,
            'area_of_expertise' => $candidate->area_of_expertise,
            'areas_of_expertise' => $candidate->area_of_expertise,
            'qualifications' => $candidate->qualifications,
            'professional_memberships' => $candidate->professional_memberships,
            'qualifications_professional_memberships' => $candidate->qualifications_professional_memberships ?: 'Not provided',
            'basis_of_recommendation' => $candidate->basis_of_recommendation,
            'short_description' => $candidate->short_description,
            'display_order' => $candidate->display_order,
            'active' => $candidate->active,
        ];
    }
}
