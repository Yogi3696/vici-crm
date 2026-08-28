<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $agents = \App\Models\VicidialUser::where('user_level', 1)
            ->search($search)
            ->paginate(15);

        return view('agents.index', compact('agents', 'search'));
    }
}
