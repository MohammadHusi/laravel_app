<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel pertemuan: satu sesi pembelajaran pada sebuah jadwal.
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal')->cascadeOnDelete();
            $table->unsignedInteger('pertemuan_ke');
            $table->string('nama_pembelajaran');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->unique(['jadwal_id', 'pertemuan_ke']);
        });

        // Tabel attendance_log: status kehadiran per murid per pertemuan.
        Schema::create('attendance_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            // hadir | sakit | izin | alpa | terlambat
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat']);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_id', 'murid_id']);
            $table->index(['pertemuan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_log');
        Schema::dropIfExists('pertemuan');
    }
};

