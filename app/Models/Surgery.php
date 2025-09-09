<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surgery extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'room_number',
        'patient_name',
        'surgery_type',
        'expected_duration',
        'start_time',
        'end_time',
    ];

    /**
     * Scope a query to only include surgeries that conflict with the given room and time.
     */
    public function scopeRoomConflicts(Builder $query, int $roomNumber, $startTime, $endTime): Builder
    {
        return $query->where('room_number', $roomNumber)
            ->where(function (Builder $query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SurgeryAudit::class);
    }

    protected static function booted(): void
    {
        static::created(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room_number,
                'start_time' => $surgery->start_time,
                'end_time' => $surgery->end_time,
            ]);
        });

        static::updated(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room_number,
                'start_time' => $surgery->start_time,
                'end_time' => $surgery->end_time,
            ]);
        });

        static::deleted(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room_number,
                'start_time' => $surgery->start_time,
                'end_time' => $surgery->end_time,
            ]);
        });
    }
}

