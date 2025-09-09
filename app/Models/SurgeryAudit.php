<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgeryAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_id',
        'doctor_id',
        'created_by',
        'confirmed_by',
        'room_number',
        'starts_at',
        'ends_at',
    ];

    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }
}
