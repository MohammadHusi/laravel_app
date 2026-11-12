<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('jenis_kelamin', 1)->nullable()->after('nama');
            $table->boolean('status')->default(true)->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'status']);
        });
    }
};

