<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Discipline;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCandidateController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $query = Candidate::with('discipline');

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('candidate_name', 'like', $keyword)
                    ->orWhere('organisation', 'like', $keyword)
                    ->orWhere('area_of_expertise', 'like', $keyword);
            });
        }

        if ($request->filled('discipline_id') && $request->input('discipline_id') !== 'all') {
            $query->where('discipline_id', (int) $request->input('discipline_id'));
        }

        $candidates = $query->orderBy('discipline_id')->orderBy('display_order')->paginate(15)->withQueryString();
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.candidates.index', [
            'candidates' => $candidates,
            'disciplines' => $disciplines,
            'filters' => $request->only(['q', 'discipline_id']),
        ]);
    }

    public function create(): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.candidates.create', [
            'disciplines' => $disciplines,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Support field aliases if submitted
        if (! $request->has('candidate_name') && $request->has('full_name')) {
            $request->merge(['candidate_name' => $request->input('full_name')]);
        }
        if (! $request->has('candidate_title') && $request->has('title_designation')) {
            $request->merge(['candidate_title' => $request->input('title_designation')]);
        }
        if (! $request->has('discipline_id') && $request->has('nominated_discipline_id')) {
            $request->merge(['discipline_id' => $request->input('nominated_discipline_id')]);
        }
        if (! $request->has('nomination_form_url') && $request->has('onedrive_dossier_link')) {
            $request->merge(['nomination_form_url' => $request->input('onedrive_dossier_link')]);
        }
        if (! $request->has('area_of_expertise') && $request->has('areas_of_expertise')) {
            $request->merge(['area_of_expertise' => $request->input('areas_of_expertise')]);
        }

        $validated = $request->validate([
            'candidate_name' => ['required', 'string', 'max:255'],
            'candidate_title' => ['required', 'string', 'max:255'],
            'discipline_id' => ['required', 'exists:disciplines,id'],
            'affiliation_to_asm' => ['required', 'string', 'max:1000'],
            'nomination_form_url' => ['nullable', 'url', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'area_of_expertise' => ['nullable', 'string'],
            'qualifications_professional_memberships' => ['nullable', 'string'],
            'basis_of_recommendation' => ['nullable', 'string'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $ext = $request->file('photo')->getClientOriginalExtension();
            $filename = Str::uuid()->toString().'.'.$ext;
            $request->file('photo')->storeAs('public/photos', $filename);
            $photoUrl = '/storage/photos/'.$filename;
        } else {
            $photoUrl = 'https://ui-avatars.com/api/?name='.urlencode($validated['candidate_name']).'&background=002B49&color=fff&size=256';
        }

        $qualMemberships = $request->input('qualifications_professional_memberships');

        $candidate = Candidate::create([
            'discipline_id' => $validated['discipline_id'],
            'candidate_name' => $validated['candidate_name'],
            'candidate_title' => $validated['candidate_title'],
            'organisation' => $validated['organisation'] ?? null,
            'photo_url' => $photoUrl,
            'affiliation_to_asm' => $validated['affiliation_to_asm'],
            'basis_of_recommendation' => $validated['basis_of_recommendation'] ?? null,
            'area_of_expertise' => $validated['area_of_expertise'] ?? null,
            'qualifications_professional_memberships' => $qualMemberships,
            'qualifications' => $qualMemberships,
            'professional_memberships' => null,
            'short_description' => $validated['short_description'] ?? null,
            'nomination_form_url' => $validated['nomination_form_url'] ?? null,
            'display_order' => $request->input('display_order', 0),
            'active' => $request->boolean('active', true),
        ]);

        $this->auditService->log(
            action: 'Candidate Created',
            recordType: 'Candidate',
            recordId: $candidate->id,
            description: "Created candidate '{$candidate->candidate_name}' under Discipline #{$candidate->discipline_id}"
        );

        return redirect()->route('admin.candidates.index')->with('success', "Candidate '{$candidate->candidate_name}' created successfully.");
    }

    public function edit(Candidate $candidate): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('admin.candidates.edit', [
            'candidate' => $candidate,
            'disciplines' => $disciplines,
        ]);
    }

    public function update(Request $request, Candidate $candidate): RedirectResponse
    {
        // Support field aliases if submitted
        if (! $request->has('candidate_name') && $request->has('full_name')) {
            $request->merge(['candidate_name' => $request->input('full_name')]);
        }
        if (! $request->has('candidate_title') && $request->has('title_designation')) {
            $request->merge(['candidate_title' => $request->input('title_designation')]);
        }
        if (! $request->has('discipline_id') && $request->has('nominated_discipline_id')) {
            $request->merge(['discipline_id' => $request->input('nominated_discipline_id')]);
        }
        if (! $request->has('nomination_form_url') && $request->has('onedrive_dossier_link')) {
            $request->merge(['nomination_form_url' => $request->input('onedrive_dossier_link')]);
        }
        if (! $request->has('area_of_expertise') && $request->has('areas_of_expertise')) {
            $request->merge(['area_of_expertise' => $request->input('areas_of_expertise')]);
        }

        $validated = $request->validate([
            'candidate_name' => ['required', 'string', 'max:255'],
            'candidate_title' => ['required', 'string', 'max:255'],
            'discipline_id' => ['required', 'exists:disciplines,id'],
            'affiliation_to_asm' => ['required', 'string', 'max:1000'],
            'nomination_form_url' => ['nullable', 'url', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'area_of_expertise' => ['nullable', 'string'],
            'qualifications_professional_memberships' => ['nullable', 'string'],
            'basis_of_recommendation' => ['nullable', 'string'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $photoUrl = $candidate->photo_url;
        if ($request->hasFile('photo')) {
            $ext = $request->file('photo')->getClientOriginalExtension();
            $filename = Str::uuid()->toString().'.'.$ext;
            $request->file('photo')->storeAs('public/photos', $filename);
            $photoUrl = '/storage/photos/'.$filename;
        }

        $qualMemberships = $request->input('qualifications_professional_memberships');

        $candidate->update([
            'discipline_id' => $validated['discipline_id'],
            'candidate_name' => $validated['candidate_name'],
            'candidate_title' => $validated['candidate_title'],
            'organisation' => $validated['organisation'] ?? $candidate->organisation,
            'photo_url' => $photoUrl,
            'affiliation_to_asm' => $validated['affiliation_to_asm'],
            'basis_of_recommendation' => $validated['basis_of_recommendation'] ?? null,
            'area_of_expertise' => $validated['area_of_expertise'] ?? null,
            'qualifications_professional_memberships' => $qualMemberships,
            'qualifications' => $qualMemberships ?: $candidate->qualifications,
            'short_description' => $validated['short_description'] ?? $candidate->short_description,
            'nomination_form_url' => $validated['nomination_form_url'] ?? null,
            'display_order' => $request->input('display_order', $candidate->display_order ?? 0),
            'active' => $request->boolean('active', true),
        ]);

        $this->auditService->log(
            action: 'Candidate Edited',
            recordType: 'Candidate',
            recordId: $candidate->id,
            description: "Updated candidate profile for '{$candidate->candidate_name}'"
        );

        return redirect()->route('admin.candidates.index')->with('success', "Candidate '{$candidate->candidate_name}' updated successfully.");
    }

    public function toggleActive(Candidate $candidate): RedirectResponse
    {
        $candidate->update(['active' => ! $candidate->active]);

        $this->auditService->log(
            action: $candidate->active ? 'Candidate Activated' : 'Candidate Deactivated',
            recordType: 'Candidate',
            recordId: $candidate->id,
            description: 'Set active status to '.($candidate->active ? 'Active' : 'Inactive')
        );

        return back()->with('success', "Candidate '{$candidate->candidate_name}' status updated.");
    }
}
