<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCloserLog;
use App\Models\VicidialStatus;
use App\Models\VicidialCampaign;
use App\Models\VicidialUser;

class CallLogController extends Controller
{
    public function incoming(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $campaignId = $request->input('campaign_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $missed = $request->input('missed');
        $agent = $request->input('agent');

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

        if ($fromDate) {
            $query->where('call_date', '>=', $fromDate . ' 00:00:00');
        }

        if ($toDate) {
            $query->where('call_date', '<=', $toDate . ' 23:59:59');
        }

        if ($agent) {
            $query->forAgent($agent);
        }

        if ($missed === 'yes') {
            $query->missed();
        } elseif ($missed === 'no') {
            $query->answered();
        }

        // Count missed calls under the current filters, before pagination.
        $missedCount = (clone $query)->missed()->count();

        $logs = $query->orderBy('call_date', 'desc')
                      ->paginate(15)
                      ->appends($request->only('search', 'status', 'campaign_id', 'from_date', 'to_date', 'missed', 'agent'));

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

        $userNames = VicidialUser::pluck('full_name', 'user');

        $agents = VicidialCloserLog::selectRaw('user, count(*) as total')
            ->whereNotNull('user')
            ->where('user', '!=', '')
            ->where('user', '!=', VicidialCloserLog::NO_AGENT_USER)
            ->groupBy('user')
            ->orderBy('user')
            ->get()
            ->map(fn ($row) => [
                'user' => $row->user,
                // full_name often just repeats the extension; only add it when it differs.
                'label' => ($userNames[$row->user] ?? '') && $userNames[$row->user] !== $row->user
                    ? $userNames[$row->user] . ' (' . $row->user . ')'
                    : $row->user,
                'total' => $row->total,
            ]);

        $noAgentTotal = VicidialCloserLog::where('user', VicidialCloserLog::NO_AGENT_USER)->count();

        return view('call-logs.incoming', compact('logs', 'search', 'status', 'campaignId', 'fromDate', 'toDate', 'missed', 'missedCount', 'agent', 'agents', 'noAgentTotal', 'campaigns', 'statuses'));
    }
}
