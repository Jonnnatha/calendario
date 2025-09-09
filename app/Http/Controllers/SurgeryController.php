<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SurgeryController extends Controller
{
    /**
     * Display a listing of all surgeries.
     */
    public function index(Request $request): Response
    {
        // Recalculate conflicts to ensure stored state is consistent
        Surgery::all()->each(function (Surgery $surgery) {
            $endsAt = Carbon::parse($surgery->starts_at)->addMinutes($surgery->duration_min);

            $hasConflict = Surgery::roomConflicts(
                $surgery->room,
                $surgery->starts_at,
                $endsAt,
            )->where('id', '!=', $surgery->id)->exists();

            if ($surgery->is_conflict !== $hasConflict) {
                $surgery->is_conflict = $hasConflict;
                $surgery->save();
            }
        });

        $surgeries = Surgery::with(['creator', 'confirmer'])->paginate(15);

        $surgeries->getCollection()->transform(function (Surgery $surgery) {
            $surgery->status = $surgery->confirmed_by ? 'confirmado' : 'agendado';
            return $surgery;
        });

        return Inertia::render('Medico/Calendar', [
            'surgeries' => $surgeries,
        ]);
    }

    /**
     * Store a newly created surgery in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_name' => ['required', 'string'],
            'surgery_type' => ['required', 'string'],
            'room' => ['required', 'integer', 'between:1,9'],
            'starts_at' => ['required', 'date'],
            'duration_min' => ['required', 'integer'],
        ]);

        $endsAt = Carbon::parse($data['starts_at'])->addMinutes($data['duration_min']);

        $data['is_conflict'] = Surgery::roomConflicts(
            $data['room'],
            $data['starts_at'],
            $endsAt,
        )->exists();

        $data['created_by'] = $request->user()->id;

        $surgery = Surgery::create($data);

        // Update conflicts for existing surgeries sharing the same room and time
        Surgery::roomConflicts(
            $surgery->room,
            $surgery->starts_at,
            $surgery->ends_at,
        )->where('id', '!=', $surgery->id)->update(['is_conflict' => true]);

        return back();
    }

    /**
     * Confirm a scheduled surgery.
     */
    public function confirm(Request $request, Surgery $surgery): Response
    {
        $surgery->confirmed_by = $request->user()->id;
        $surgery->save();

        $surgery->status = 'confirmado';

        return Inertia::render('Medico/Calendar', [
            'surgery' => $surgery->load(['creator', 'confirmer']),
        ]);
    }
}

