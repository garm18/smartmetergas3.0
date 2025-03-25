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
        Schema::table('logs', function (Blueprint $table) {
            // Pastikan kolom 'rssi' ada sebelum menghapusnya
            if (Schema::hasColumn('logs', 'rssi')) {
                $table->dropColumn('rssi');
            }
        });

        Schema::table('logs', function (Blueprint $table) {
            // Tambahkan kembali dengan nama 'signal_strength' setelah metergas_id
            $table->integer('signal_strength')->nullable()->after('metergas_id');

            // Tambahkan 'signal_level' setelah 'signal_strength'
            $table->string('signal_level')->nullable()->after('signal_strength');

            // Tambahkan 'imei' setelah 'id'
            $table->bigInteger('imei')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Hapus kolom yang baru ditambahkan saat rollback
            if (Schema::hasColumn('logs', 'signal_strength')) {
                $table->dropColumn('signal_strength');
            }
            if (Schema::hasColumn('logs', 'signal_level')) {
                $table->dropColumn('signal_level');
            }
            if (Schema::hasColumn('logs', 'imei')) {
                $table->dropColumn('imei');
            }
        });

        // Tambahkan kembali 'rssi' hanya jika sebelumnya ada
        Schema::table('logs', function (Blueprint $table) {
            $table->integer('rssi')->nullable();
        });
    }
};
