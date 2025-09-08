<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SurgeryController extends Controller
{
    /**
     * Handle a newly created surgery request.
     */
    public function store(Request $request)
    {
        return redirect()->route('calendar')->with('message', 'Surgery created successfully.');
    }

    /**
     * Handle confirming a surgery.
     */
    public function confirm(Request $request)
    {
        return redirect()->route('calendar')->with('message', 'Surgery confirmed.');
    }
}
