<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use App\Models\User;
use App\Notifications\UpcomingSurgery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SurgeryController extends Controller
{
    /**
     * Display a listing of the doctor's surgeries.
     */
    public function index(Request $request): Response
    {
        $surgeries = Surgery::where('doctor_id', $request->user()->id)
            ->get(['id', 'room_number', 'patient_name', 'surgery_type', 'expected_duration', 'start_time', 'end_time', 'status']);

        $surgeries->each(function (Surgery $surgery) {
            $hasConflict = Surgery::roomConflicts(
                $surgery->room_number,
                $surgery->start_time,
                $surgery->end_time
            )->where('id', '!=', $surgery->id)->exists();

            if ($hasConflict) {
                $surgery->status = 'conflict';
            }
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
            'doctor_id' => ['required', 'exists:users,id'],
            'room_number' => ['required', 'integer'],
            'patient_name' => ['required', 'string'],
            'surgery_type' => ['required', 'string'],
            'expected_duration' => ['required', 'integer'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        if ($request->user()->id !== $data['doctor_id']) {
            return back()->withErrors([
                'doctor_id' => 'Doctors can only schedule surgeries for themselves.',
            ]);
        }

        $surgery = Surgery::create($data);

        if ($doctor = User::find($surgery->doctor_id)) {
            $doctor->notify(new UpcomingSurgery($surgery->start_time));
        }

        return back();
    }

    /**
     * Confirm a scheduled surgery.
     */
    public function confirm(Request $request, Surgery $surgery): RedirectResponse
    {
        $surgery->status = 'confirmed';
        $surgery->confirmed_by = $request->user()->id;
        $surgery->save();

        return back();
    }
}

