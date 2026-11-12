<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $table = 'murid';

    protected $fillable = [
        'user_id', 'nis', 'nisn', 'nama', 'kelas_id', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'status' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'kelas_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'murid_id');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'murid_id');
    }
}