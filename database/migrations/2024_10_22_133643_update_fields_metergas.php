<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('metergas', function (Blueprint $table) {
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('regency_id')->nullable();
            $table->bigInteger('district_id')->nullable();
            $table->bigInteger('village_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metergas', function (Blueprint $table) {
            $table->dropColumn('province_id');
            $table->dropColumn('regency_id');
            $table->dropColumn('district_id');
            $table->dropColumn('village_id');
        });
    }
};
