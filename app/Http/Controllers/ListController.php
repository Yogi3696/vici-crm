<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
     * Show the new list form. Vicidial's list_id is not auto incrementing, so
     * the next free id is suggested rather than generated on insert.
     */
    public function create()
    {
        $campaigns = VicidialCampaign::orderBy('campaign_name')->get(['campaign_id', 'campaign_name']);

        $suggestedId = (int) VicidialList::max('list_id') + 1;

        return view('lists.create', compact('campaigns', 'suggestedId'));
    }

    /**
     * Persist a new list.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'list_id' => ['required', 'integer', 'min:1', 'unique:asterisk.vicidial_lists,list_id'],
            'list_name' => ['required', 'string', 'max:30'],
            'campaign_id' => ['required', 'string', 'max:8', 'exists:asterisk.vicidial_campaigns,campaign_id'],
            'active' => ['required', Rule::in(['Y', 'N'])],
            'list_description' => ['nullable', 'string', 'max:255'],
        ]);

        VicidialList::create([
            'list_id' => $data['list_id'],
            'list_name' => $data['list_name'],
            'campaign_id' => $data['campaign_id'],
            'active' => $data['active'],
            'list_description' => $data['list_description'] ?? '',
            // Vicidial expects these set on every list; mirror its own defaults.
            'list_changedate' => now(),
            'local_call_time' => 'campaign',
        ]);

        return redirect()
            ->route('lists.index', ['search' => $data['list_id']])
            ->with('status', __('List :name created.', ['name' => $data['list_name']]));
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
