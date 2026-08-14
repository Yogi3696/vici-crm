<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\VicidialList;
use App\Models\VicidialStatus;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $listId = $request->input('list_id');

        $leads = Lead::with(['list', 'statusDetail'])
            ->search($search)
            ->status($status)
            ->forList($listId)
            ->orderByDesc('lead_id')
            ->paginate(25)
            ->appends($request->only('search', 'status', 'list_id'));

        $lists = VicidialList::orderBy('list_name')->get(['list_id', 'list_name']);

        // Only statuses actually present on leads, labelled where a name exists.
        $statusNames = VicidialStatus::pluck('status_name', 'status');

        $statuses = Lead::selectRaw('status, count(*) as total')
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'label' => $statusNames[$row->status] ?? $row->status,
                'total' => $row->total,
            ]);

        return view('leads.index', compact('leads', 'search', 'status', 'listId', 'lists', 'statuses'));
    }
}
