<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCampaign;
use App\Models\VicidialInboundGroup;

class InboundGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $campaignId = $request->input('campaign_id');

        $campaigns = VicidialCampaign::orderBy('campaign_id')
                                     ->get(['campaign_id', 'campaign_name', 'xfer_groups']);

        $selectedCampaign = $campaignId
            ? $campaigns->firstWhere('campaign_id', $campaignId)
            : null;

        $inboundGroups = VicidialInboundGroup::forCampaign($selectedCampaign)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('group_id', 'like', "%{$search}%")
                      ->orWhere('group_name', 'like', "%{$search}%");
                });
            })
            ->paginate(15)
            ->appends($request->only('search', 'campaign_id'));

        // group_id => list of campaign ids that reference it, for the mapping column
        $campaignsByGroup = [];
        foreach ($campaigns as $campaign) {
            foreach ($campaign->inbound_group_ids as $groupId) {
                $campaignsByGroup[$groupId][] = $campaign->campaign_id;
            }
        }

        return view('inbound_groups.index', compact(
            'inboundGroups',
            'search',
            'campaigns',
            'selectedCampaign',
            'campaignId',
            'campaignsByGroup'
        ));
    }
}
