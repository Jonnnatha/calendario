<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurgeryController extends Controller
{
    public function index()
    {
        $surgeries = Surgery::with(['creator', 'confirmer'])->get()->map(function ($surgery) {
            return [
                'id' => $surgery->id,
                'title' => $surgery->title,
                'scheduled_at' => optional($surgery->scheduled_at)->toDateTimeString(),
                'creator' => $surgery->creator ? $surgery->creator->only(['id', 'name']) : null,
                'confirmer' => $surgery->confirmer ? $surgery->confirmer->only(['id', 'name']) : null,
            ];
        });

        return Inertia::render('Dashboard', [
            'surgeries' => $surgeries,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $data['created_by'] = $request->user()->id;

        Surgery::create($data);

        return redirect()->route('dashboard');
    }

    public function confirm(Request $request, Surgery $surgery)
    {
        $surgery->update([
            'confirmed_by' => $request->user()->id,
        ]);

        return redirect()->route('dashboard');
    }
}
