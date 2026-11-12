<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = ['tahun_ajaran', 'semester', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'tahun_ajaran_id');
    }
}