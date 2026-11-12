<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Principal extends Model
{
    protected $table = 'kepala_sekolah';

    protected $fillable = [
        'user_id', 'nama', 'nip', 'email', 'no_hp', 'alamat', 'foto',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
