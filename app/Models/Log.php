<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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

    public function getOwnerAttribute()
    {
        $user_id = DB::table('metergas')
            ->where('id', $this->metergas_id)
            ->value('user_id');

        // Query to get the user name where the user_id matches
        $userName = DB::table('users')
            ->where('id', $user_id) // Match user_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $userName?? 'Unknown'; // Return 'Unknown' if no matching user found
    }

    public function getSerialAttribute()
    {
        $serialNo = DB::table('metergas')
            ->where('id', $this->metergas_id)
            ->value('serialNo');

        return $serialNo?? 'Unknown';
    }
}
