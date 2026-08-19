<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCloserLog;
use App\Models\VicidialStatus;
use App\Models\VicidialCampaign;

class CallLogController extends Controller
{
    public function incoming(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $campaignId = $request->input('campaign_id');

        $query = VicidialCloserLog::with('vicidialStatus');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('lead_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('campaign_id', 'like', "%{$search}%")
                  ->orWhere('list_id', 'like', "%{$search}%")
                  ->orWhere('user', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        $logs = $query->orderBy('call_date', 'desc')
                      ->paginate(15)
                      ->appends($request->only('search', 'status', 'campaign_id'));

        $campaigns = VicidialCampaign::orderBy('campaign_name')->get(['campaign_id', 'campaign_name']);

        $statusNames = VicidialStatus::pluck('status_name', 'status');

        $statuses = VicidialCloserLog::selectRaw('status, count(*) as total')
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'label' => $statusNames[$row->status] ?? $row->status,
                'total' => $row->total,
            ]);

        return view('call-logs.incoming', compact('logs', 'search', 'status', 'campaignId', 'campaigns', 'statuses'));
    }
}
