<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->input('action').'%');
        }

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('description', 'like', $keyword)
                    ->orWhere('ip_address', 'like', $keyword)
                    ->orWhere('action', 'like', $keyword);
            });
        }

        $logs = $query->latest('id')->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'filters' => $request->only(['user_id', 'action', 'q']),
        ]);
    }
}
