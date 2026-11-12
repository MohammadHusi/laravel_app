<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $table = 'attendance_log';

    protected $fillable = ['pertemuan_id', 'murid_id', 'status', 'keterangan'];

    /** Kode badge untuk status: H, S, I, A, T. */
    public const CODES = [
        'hadir' => 'H',
        'sakit' => 'S',
        'izin' => 'I',
        'alpa' => 'A',
        'terlambat' => 'T',
    ];

    public const LABELS = [
        'hadir' => 'Hadir',
        'sakit' => 'Sakit',
        'izin' => 'Izin',
        'alpa' => 'Alpa',
        'terlambat' => 'Terlambat',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'pertemuan_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'murid_id');
    }

    public function code(): string
    {
        return self::CODES[$this->status] ?? '?';
    }

    public function label(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
    }
}

