<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DueDiligenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'candidate_id' => ['required', 'exists:candidates,id'],
            'category_id' => ['required', 'exists:due_diligence_categories,id'],
            'comment' => ['required', 'string', 'min:5', 'max:10000'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:10240'],

            // Reference 1 (Mandatory)
            'reference_1_name' => ['required', 'string', 'max:255'],
            'reference_1_designation' => ['required', 'string', 'max:255'],
            'reference_1_organisation' => ['required', 'string', 'max:255'],
            'reference_1_contact_number' => ['required', 'string', 'max:50'],
            'reference_1_email' => ['required', 'string', 'email', 'max:255'],

            // Reference 2 (Optional)
            'reference_2_name' => ['nullable', 'string', 'max:255'],
            'reference_2_designation' => ['nullable', 'string', 'max:255'],
            'reference_2_organisation' => ['nullable', 'string', 'max:255'],
            'reference_2_contact_number' => ['nullable', 'string', 'max:50'],
            'reference_2_email' => ['nullable', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'due diligence category',
            'comment' => 'due diligence comments',
            'reference_1_name' => 'Reference 1 Name',
            'reference_1_designation' => 'Reference 1 Designation',
            'reference_1_organisation' => 'Reference 1 Organisation',
            'reference_1_contact_number' => 'Reference 1 Contact Number',
            'reference_1_email' => 'Reference 1 Email',
            'reference_2_name' => 'Reference 2 Name',
            'reference_2_designation' => 'Reference 2 Designation',
            'reference_2_organisation' => 'Reference 2 Organisation',
            'reference_2_contact_number' => 'Reference 2 Contact Number',
            'reference_2_email' => 'Reference 2 Email',
        ];
    }
}
