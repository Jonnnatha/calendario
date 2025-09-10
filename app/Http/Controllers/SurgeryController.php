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
        $surgeries = Surgery::with(['creator', 'confirmer'])
            ->paginate(20)
            ->through(fn (Surgery $surgery) => [
                'id' => $surgery->id,
                'patient_name' => $surgery->patient_name,
                'surgery_type' => $surgery->surgery_type,
                'room' => $surgery->room,
                'starts_at' => $surgery->starts_at,
                'duration_min' => $surgery->duration_min,
                'end_time' => $surgery->ends_at,
                'status' => $surgery->is_conflict
                    ? 'conflito'
                    : ($surgery->confirmed_by ? 'confirmado' : 'agendado'),
            ]);

        return Inertia::render('Medico/Calendar', [
            'surgeries' => $surgeries,
            'rooms' => range(1, 9),
            'canCreate' => $request->user()->hasRole('medico'),
            'canConfirm' => $request->user()->hasRole('enfermeiro'),
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
    public function confirm(Request $request, Surgery $surgery): RedirectResponse
    {
        $surgery->confirmed_by = $request->user()->id;
        $surgery->save();

        return redirect()->route('surgeries.index');
    }
}

