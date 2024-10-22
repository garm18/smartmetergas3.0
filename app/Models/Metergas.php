<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Metergas extends Model
{
    use HasFactory;

    protected $fillable = [
        'serialNo',
        'connectivity',
        'user_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
    ];

    /**
     * Get the user that owns the metergas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all of the comments for the metergas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'metergas_id', 'id');
    }

    public function province(): BelongsTo
    {
        return $this ->belongsTo(Province::class);
    }
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
    public function district(): BelongsTo
    {
        return $this ->belongsTo(District::class);
    }
    public function village(): BelongsTo
    {
        return $this ->belongsTo(Village::class);
    }
}
