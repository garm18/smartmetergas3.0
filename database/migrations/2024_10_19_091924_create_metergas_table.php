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
        Schema::create('metergas', function (Blueprint $table) {
            $table->id();
            $table->string('serialNo')->max_length(10);
            $table->string('connectivity');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');

            // provinces
            $table->bigInteger('province_id', 2);
            $table->foreignId('province_id')->references('id')->on('provinces')->onDelete('restrict');

            // regencies
            $table->bigInteger('regency_id', 4);
            $table->foreignId('regency_id')->references('id')->on('regencies')->onDelete('restrict');

            // districts
            $table->bigInteger('district_id', 7);
            $table->foreignId('district_id')->references('id')->on('districts')->onDelete('restrict');

            // villages
            $table->bigInteger('village_id', 10);
            $table->foreignId('village_id')->references('id')->on('villages')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metergas', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['village_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('metergas');
    }

};
