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
        'created_by',
        'confirmed_by',
        'room_number',
        'start_time',
        'end_time',
    ];

    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }
}
