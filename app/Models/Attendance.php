<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'murid_id', 'guru_id', 'kelas_id', 'mata_pelajaran_id', 'jadwal_id',
        'tanggal', 'jam', 'status', 'keterangan',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'murid_id');
    }

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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }
}