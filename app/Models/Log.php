<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'condition_io',
        'volume',
        'type_io',
        'battery',
        'metergas_id',
    ];

    /**
     * Get the user that owns the Log
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metergas(): BelongsTo
    {
        return $this->belongsTo(Metergas::class, 'metergas_id', 'id');
    }
}
