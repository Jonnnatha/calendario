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
            ->get(['id', 'room_number', 'start_time', 'end_time']);

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
            'room_number' => ['required', 'integer', 'between:1,9'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        if ($request->user()->id !== $data['doctor_id']) {
            return back()->withErrors([
                'doctor_id' => 'Doctors can only schedule surgeries for themselves.',
            ]);
        }

        if (Surgery::roomConflicts($data['room_number'], $data['start_time'], $data['end_time'])->exists()) {
            return back()->withErrors([
                'room_number' => 'Room already booked for the selected time.',
            ]);
        }

        $surgery = Surgery::create($data);

        if ($doctor = User::find($surgery->doctor_id)) {
            $doctor->notify(new UpcomingSurgery($surgery->start_time));
        }

        return back();
    }
}

