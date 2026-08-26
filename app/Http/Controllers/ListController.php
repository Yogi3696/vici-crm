<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCampaign;
use App\Models\VicidialList;

class ListController extends Controller
{
    /**
     * Sortable table columns mapped to their database column. Keeps user input
     * out of the order by clause. leads_count is the withCount alias.
     */
    private const SORTABLE = [
        'list_id' => 'list_id',
        'list_name' => 'list_name',
        'campaign_id' => 'campaign_id',
        'active' => 'active',
        'leads_count' => 'leads_count',
        'list_lastcalldate' => 'list_lastcalldate',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $campaignId = $request->input('campaign_id');
        $active = $request->input('active');
        [$sort, $direction] = $this->sortFrom($request);

        $lists = VicidialList::withCount('leads')
            ->with('campaign')
            ->search($search)
            ->forCampaign($campaignId)
            ->active($active)
            ->orderBy(self::SORTABLE[$sort], $direction)
            ->paginate(15)
            ->appends($request->only('search', 'campaign_id', 'active', 'sort', 'direction'));

        $campaigns = VicidialCampaign::orderBy('campaign_name')->get(['campaign_id', 'campaign_name']);

        // Both totals track the current filters, so the toolbar summarises the
        // result set rather than mixing it with counts from the whole table.
        $filtered = fn () => VicidialList::query()
            ->search($search)
            ->forCampaign($campaignId)
            ->active($active);

        $activeTotal = $filtered()->where('active', 'Y')->count();

        $leadTotal = $filtered()->withCount('leads')->get()->sum('leads_count');

        return view('lists.index', compact('lists', 'search', 'campaignId', 'active', 'campaigns', 'activeTotal', 'leadTotal', 'sort', 'direction'));
    }

    /**
     * Resolve the requested sort column and direction, falling back to the
     * lowest list id first. Only whitelisted keys reach the query builder.
     */
    private function sortFrom(Request $request): array
    {
        $sort = $request->input('sort');
        $sort = isset(self::SORTABLE[$sort]) ? $sort : 'list_id';

        $direction = strtolower((string) $request->input('direction')) === 'desc' ? 'desc' : 'asc';

        return [$sort, $direction];
    }
}
