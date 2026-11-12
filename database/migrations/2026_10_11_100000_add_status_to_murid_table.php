<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('murid', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('murid', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

