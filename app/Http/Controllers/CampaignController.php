<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCampaign;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $campaigns = VicidialCampaign::when($search, function ($query, $search) {
            return $query->where('campaign_id', 'like', "%{$search}%")
                         ->orWhere('campaign_name', 'like', "%{$search}%")
                         ->orWhere('campaign_description', 'like', "%{$search}%");
        })->paginate(15)->appends(['search' => $search]);

        return view('campaigns.index', compact('campaigns', 'search'));
    }
}
