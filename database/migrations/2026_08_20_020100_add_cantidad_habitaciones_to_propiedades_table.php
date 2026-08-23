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
            $table->unsignedInteger('cantidad_habitaciones')->nullable()->after('tipo');
        });

        $conteos = DB::table('habitaciones')
            ->select('propiedad_id', DB::raw('count(*) as cantidad'))
            ->groupBy('propiedad_id')
            ->get();

        foreach ($conteos as $fila) {
            DB::table('propiedades')
                ->where('id', $fila->propiedad_id)
                ->update(['cantidad_habitaciones' => $fila->cantidad]);
        }
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('cantidad_habitaciones');
        });
    }
};
