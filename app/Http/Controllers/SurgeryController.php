<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use App\Models\User;
use App\Notifications\UpcomingSurgery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurgeryController extends Controller
{
    /**
     * Store a newly created surgery in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'room_number' => ['required', 'integer'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

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

