<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RankingSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->active && ($user->isVotingUser() || $user->isAdmin()) && ! is_null($user->discipline_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        if ($user && $user->discipline && ! $user->discipline->isRankingRequired()) {
            return [];
        }

        return [
            'rankings' => ['required', 'array', 'min:1'],
            'rankings.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
