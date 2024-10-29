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
            $table->bigInteger('province_id')->nullable()->change();
            $table->bigInteger('regency_id')->nullable()->change();
            $table->bigInteger('district_id')->nullable()->change();
            $table->bigInteger('village_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metergas', function (Blueprint $table) {
            $table->dropColumn('province_id')->change();
            $table->dropColumn('regency_id')->change();
            $table->dropColumn('district_id')->change();
            $table->dropColumn('village_id')->change();
        });
    }
};
