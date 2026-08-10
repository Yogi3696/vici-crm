<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCampaign;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = VicidialCampaign::paginate(15);
        return view('campaigns.index', compact('campaigns'));
    }
}
