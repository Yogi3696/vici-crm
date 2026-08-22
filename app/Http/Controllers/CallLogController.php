<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCloserLog;
use App\Models\VicidialStatus;
use App\Models\VicidialCampaign;
use App\Models\VicidialUser;

class CallLogController extends Controller
{
    /**
     * Sortable table columns mapped to their database column. Keeps user
     * input out of the order by clause.
     */
    private const SORTABLE = [
        'call_date' => 'call_date',
        'phone_number' => 'phone_number',
        'lead_id' => 'lead_id',
        'campaign_id' => 'campaign_id',
        'list_id' => 'list_id',
        'length_in_sec' => 'length_in_sec',
        'status' => 'status',
        'user' => 'user',
    ];

    public function incoming(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $campaignId = $request->input('campaign_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $missed = $request->input('missed');
        $agent = $request->input('agent');
        [$sort, $direction] = $this->sortFrom($request);

        $query = VicidialCloserLog::with(['vicidialStatus', 'vicidialList']);

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

        $logs = $query->orderBy(self::SORTABLE[$sort], $direction)
                      ->paginate(15)
                      ->appends($request->only('search', 'status', 'campaign_id', 'from_date', 'to_date', 'missed', 'agent', 'sort', 'direction'));

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

        return view('call-logs.incoming', compact('logs', 'search', 'status', 'campaignId', 'fromDate', 'toDate', 'missed', 'missedCount', 'agent', 'agents', 'noAgentTotal', 'campaigns', 'statuses', 'sort', 'direction'));
    }

    /**
     * Resolve the requested sort column and direction, falling back to the
     * newest calls first. Only whitelisted keys reach the query builder.
     */
    private function sortFrom(Request $request): array
    {
        $sort = $request->input('sort');
        $sort = isset(self::SORTABLE[$sort]) ? $sort : 'call_date';

        $direction = strtolower((string) $request->input('direction')) === 'asc' ? 'asc' : 'desc';

        return [$sort, $direction];
    }
}
