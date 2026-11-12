<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('role')->default('murid')->after('password');
        });

        Schema::create('kepala_sekolah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->string('nip')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->string('nip')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas')->unique();
            $table->string('jurusan')->nullable();
            $table->unsignedTinyInteger('tingkat')->nullable();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('murid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nis')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('nama');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel');
            $table->string('kode_mapel')->unique();
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran');
            $table->string('semester', 20);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
            $table->string('hari', 12);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['murid_id', 'tanggal', 'kelas_id', 'mata_pelajaran_id', 'jadwal_id'], 'absensi_siswa_slot_unique');
            $table->index(['tanggal', 'kelas_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('murid');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('kepala_sekolah');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role']);
        });
    }
};