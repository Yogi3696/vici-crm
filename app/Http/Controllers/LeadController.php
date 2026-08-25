<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\VicidialList;
use App\Models\VicidialStatus;

class LeadController extends Controller
{
    /**
     * Sortable table columns mapped to their database column. Keeps user input
     * out of the order by clause. The displayed name is assembled in PHP, so
     * 'name' is handled separately in applySort() rather than mapped here.
     */
    private const SORTABLE = [
        'lead_id' => 'lead_id',
        'name' => 'last_name',
        'phone_number' => 'phone_number',
        'status' => 'status',
        'list_id' => 'list_id',
        'called_count' => 'called_count',
        'entry_date' => 'entry_date',
        'modify_date' => 'modify_date',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $listId = $request->input('list_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        [$sort, $direction] = $this->sortFrom($request);

        $leads = Lead::with(['list', 'statusDetail'])
            ->search($search)
            ->status($status)
            ->forList($listId)
            ->entryDateBetween($fromDate, $toDate)
            ->tap(fn ($q) => $this->applySort($q, $sort, $direction))
            ->paginate(25)
            ->appends($request->only('search', 'status', 'list_id', 'from_date', 'to_date', 'sort', 'direction'));

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

        return view('leads.index', compact('leads', 'search', 'status', 'listId', 'fromDate', 'toDate', 'lists', 'statuses', 'sort', 'direction'));
    }

    /**
     * Order the query by the resolved sort column.
     *
     * Most of the table has no last_name, so sorting the name column on that
     * alone scatters the leads that carry only a first name. Falling through to
     * first_name keeps them together, and pushing the entirely nameless rows to
     * the end keeps an ascending sort useful.
     */
    private function applySort($query, string $sort, string $direction)
    {
        if ($sort === 'name') {
            // Nameless leads sort last in both directions; flipping them with
            // the direction would just bury the named ones behind 170 blanks.
            return $query->orderByRaw("COALESCE(NULLIF(last_name, ''), NULLIF(first_name, '')) IS NULL asc")
                ->orderBy('last_name', $direction)
                ->orderBy('first_name', $direction);
        }

        return $query->orderBy(self::SORTABLE[$sort], $direction);
    }

    /**
     * Resolve the requested sort column and direction, falling back to the
     * newest leads first. Only whitelisted keys reach the query builder.
     */
    private function sortFrom(Request $request): array
    {
        $sort = $request->input('sort');
        $sort = isset(self::SORTABLE[$sort]) ? $sort : 'lead_id';

        $direction = strtolower((string) $request->input('direction')) === 'asc' ? 'asc' : 'desc';

        return [$sort, $direction];
    }
}
