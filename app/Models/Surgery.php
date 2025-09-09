<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'starts_at',
        'ends_at',
        'is_conflict',
        'created_by',
        'confirmed_by',
    ];

    /**
     * Scope a query to only include surgeries that conflict with the given room and time.
     */
    public function scopeRoomConflicts(Builder $query, int $roomNumber, $startsAt, $endsAt): Builder
    {
        return $query->where('room_number', $roomNumber)
            ->where(function (Builder $query) use ($startsAt, $endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
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
                'starts_at' => $surgery->starts_at,
                'ends_at' => $surgery->ends_at,
                'created_by' => auth()->id(),
            ]);
        });

        static::updated(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room_number,
                'starts_at' => $surgery->starts_at,
                'ends_at' => $surgery->ends_at,
                'confirmed_by' => auth()->id(),
            ]);
        });

        static::deleted(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room_number,
                'starts_at' => $surgery->starts_at,
                'ends_at' => $surgery->ends_at,
                'confirmed_by' => auth()->id(),
            ]);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

