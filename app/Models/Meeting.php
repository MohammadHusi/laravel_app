<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $table = 'pertemuan';

    protected $fillable = [
        'jadwal_id', 'pertemuan_ke', 'nama_pembelajaran', 'tanggal', 'jam_mulai', 'jam_selesai',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'pertemuan_id');
    }

    /** Total tiap status untuk ringkasan pertemuan. */
    public function statusCounts(): array
    {
        $counts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'terlambat' => 0];

        foreach ($this->logs as $log) {
            $counts[$log->status] = ($counts[$log->status] ?? 0) + 1;
        }

        return $counts;
    }
}

