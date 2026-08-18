<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialCloserLog;

class CallLogController extends Controller
{
    public function incoming(Request $request)
    {
        $search = $request->input('search');

        $logs = VicidialCloserLog::with('vicidialStatus')->when($search, function ($query, $search) {
            return $query->where('lead_id', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%")
                         ->orWhere('campaign_id', 'like', "%{$search}%")
                         ->orWhere('list_id', 'like', "%{$search}%")
                         ->orWhere('user', 'like', "%{$search}%");
        })->orderBy('call_date', 'desc')->paginate(15)->appends(['search' => $search]);

        return view('call-logs.incoming', compact('logs', 'search'));
    }
}
