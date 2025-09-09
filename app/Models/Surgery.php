<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Surgery extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_name',
        'surgery_type',
        'room',
        'starts_at',
        'duration_min',
        'created_by',
        'confirmed_by',
        'is_conflict',
    ];

    protected $appends = [
        'ends_at',
    ];

    /**
     * Scope a query to only include surgeries that conflict with the given room and time.
     */
    public function scopeRoomConflicts(Builder $query, int $room, $startsAt, $endsAt): Builder
    {
        return $query->where('room', $room)
            ->where(function (Builder $query) use ($startsAt, $endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->whereRaw('DATE_ADD(starts_at, INTERVAL duration_min MINUTE) > ?', [$startsAt]);
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

    public function getEndsAtAttribute(): Carbon
    {
        return Carbon::parse($this->starts_at)->addMinutes($this->duration_min);
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
                'room_number' => $surgery->room,
                'start_time' => $surgery->starts_at,
                'end_time' => $surgery->ends_at,
                'created_by' => auth()->id(),
            ]);
        });

        static::updated(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room,
                'start_time' => $surgery->starts_at,
                'end_time' => $surgery->ends_at,
                'confirmed_by' => auth()->id(),
            ]);
        });

        static::deleted(function (Surgery $surgery) {
            $surgery->audits()->create([
                'doctor_id' => $surgery->doctor_id,
                'room_number' => $surgery->room,
                'start_time' => $surgery->starts_at,
                'end_time' => $surgery->ends_at,
                'confirmed_by' => auth()->id(),
            ]);
        });
    }
}

