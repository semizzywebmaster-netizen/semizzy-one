<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->has('action')) {
            $query->forAction($request->action);
        }

        if ($request->has('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->has('result')) {
            $query->where('result', $request->result);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }

    public function show(AuditLog $audit)
    {
        $audit->load('user');

        return view('admin.audit.show', compact('audit'));
    }
}