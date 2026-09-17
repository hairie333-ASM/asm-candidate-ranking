<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDueDiligenceController extends Controller
{
    public function index(Request $request): View
    {
        $query = DueDiligenceSubmission::with([
            'candidate.discipline',
            'category',
            'user',
            'userDiscipline',
            'supportingDocuments',
        ]);

        if ($request->filled('discipline_id') && $request->input('discipline_id') !== 'all') {
            $query->whereHas('candidate', fn ($q) => $q->where('discipline_id', (int) $request->input('discipline_id')));
        }

        if ($request->filled('category_id') && $request->input('category_id') !== 'all') {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('comment', 'like', $keyword)
                    ->orWhereHas('candidate', fn ($cq) => $cq->where('candidate_name', 'like', $keyword))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $keyword));
            });
        }

        $submissions = $query->latest()->paginate(20)->withQueryString();
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();
        $categories = DueDiligenceCategory::where('active', true)->orderBy('display_order')->get();

        return view('admin.due-diligence.index', [
            'submissions' => $submissions,
            'disciplines' => $disciplines,
            'categories' => $categories,
            'filters' => $request->only(['discipline_id', 'category_id', 'q']),
        ]);
    }
}
