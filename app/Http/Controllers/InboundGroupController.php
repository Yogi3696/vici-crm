<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialInboundGroup;

class InboundGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $inboundGroups = VicidialInboundGroup::when($search, function ($query, $search) {
            return $query->where('group_id', 'like', "%{$search}%")
                         ->orWhere('group_name', 'like', "%{$search}%");
        })->paginate(15)->appends(['search' => $search]);

        return view('inbound_groups.index', compact('inboundGroups', 'search'));
    }
}
