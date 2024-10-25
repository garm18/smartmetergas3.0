<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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

    // Define accessor to get the province name
    public function getProvinceNameAttribute()
    {
        // Query to get the province name where the province_id matches
        $provinceName = DB::table('provinces')
            ->where('id', $this->province_id) // Match province_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $provinceName ?? 'Unknown'; // Return 'Unknown' if no matching province found
    }

    public function getRegencyNameAttribute()
    {
        // Query to get the province name where the province_id matches
        $regencyName = DB::table('regencies')
            ->where('id', $this->regency_id) // Match regency_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $regencyName ?? 'Unknown'; // Return 'Unknown' if no matching regency found
    }

    public function getDistrictNameAttribute()
    {
        // Query to get the province name where the province_id matches
        $districtName = DB::table('districts')
            ->where('id', $this->district_id) // Match district_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $districtName ?? 'Unknown'; // Return 'Unknown' if no matching district found
    }

    public function getVillageNameAttribute()
    {
        // Query to get the province name where the province_id matches
        $villageName = DB::table('villages')
            ->where('id', $this->village_id) // Match village_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $villageName ?? 'Unknown'; // Return 'Unknown' if no matching village found
    }

    public function getUsernameAttribute()
    {
        // Query to get the user name where the user_id matches
        $userName = DB::table('users')
            ->where('id', $this->user_id) // Match user_id from Metergas model
            ->value('name'); // Get only the 'name' column value

        return $userName?? 'Unknown'; // Return 'Unknown' if no matching user found
    }
}
