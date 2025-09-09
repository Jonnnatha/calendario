<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use App\Models\User;
use App\Notifications\UpcomingSurgery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SurgeryController extends Controller
{
    /**
     * Display a listing of surgeries with creator and confirmer information.
     */
    public function index(Request $request): Response
    {
        $surgeries = Surgery::with(['creator:id,name', 'confirmer:id,name'])
            ->paginate(15)
            ->through(function (Surgery $surgery) {
                return [
                    'id' => $surgery->id,
                    'room_number' => $surgery->room_number,
                    'patient_name' => $surgery->patient_name,
                    'surgery_type' => $surgery->surgery_type,
                    'expected_duration' => $surgery->expected_duration,
                    'starts_at' => $surgery->starts_at,
                    'ends_at' => $surgery->ends_at,
                    'is_conflict' => (bool) $surgery->is_conflict,
                    'status' => $surgery->confirmed_by ? 'confirmado' : 'agendado',
                    'creator' => $surgery->creator?->only(['id', 'name']),
                    'confirmer' => $surgery->confirmer?->only(['id', 'name']),
                ];
            });

        return Inertia::render('Surgeries/Index', [
            'surgeries' => $surgeries,
        ]);
    }

    /**
     * Store a newly created surgery in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'room_number' => ['required', 'integer', 'between:1,9'],
            'patient_name' => ['required', 'string'],
            'surgery_type' => ['required', 'string'],
            'expected_duration' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
        ]);

        $data['ends_at'] = Carbon::parse($data['starts_at'])->addMinutes($data['expected_duration']);
        $data['created_by'] = $request->user()->id;

        $hasConflict = Surgery::roomConflicts(
            $data['room_number'],
            $data['starts_at'],
            $data['ends_at']
        )->exists();

        if ($hasConflict) {
            $data['is_conflict'] = true;
        }

        if ($request->user()->id !== $data['doctor_id']) {
            return back()->withErrors([
                'doctor_id' => 'Doctors can only schedule surgeries for themselves.',
            ]);
        }

        $surgery = Surgery::create($data);

        if ($doctor = User::find($surgery->doctor_id)) {
            $doctor->notify(new UpcomingSurgery($surgery->starts_at));
        }

        return back();
    }

    /**
     * Confirm a scheduled surgery.
     */
    public function confirm(Request $request, Surgery $surgery): RedirectResponse
    {
        $surgery->confirmed_by = $request->user()->id;
        $surgery->save();

        return back();
    }
}

