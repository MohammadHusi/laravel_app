<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $table = 'jadwal';

    protected $fillable = [
        'guru_id', 'kelas_id', 'mata_pelajaran_id', 'tahun_ajaran_id',
        'hari', 'jam_mulai', 'jam_selesai',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'guru_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'kelas_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'mata_pelajaran_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'jadwal_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'jadwal_id')->orderBy('pertemuan_ke');
    }
}