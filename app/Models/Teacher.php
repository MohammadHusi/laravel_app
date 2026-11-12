<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id', 'nama', 'jenis_kelamin', 'nip', 'email', 'no_hp', 'status', 'alamat', 'foto',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'guru_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'guru_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'wali_kelas_id');
    }
}
