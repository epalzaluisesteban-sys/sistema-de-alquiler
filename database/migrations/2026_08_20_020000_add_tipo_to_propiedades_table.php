<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->string('tipo')->default('completa')->after('descripcion');
        });

        DB::table('propiedades')
            ->whereIn('id', DB::table('habitaciones')->select('propiedad_id')->distinct())
            ->update(['tipo' => 'habitaciones']);
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
