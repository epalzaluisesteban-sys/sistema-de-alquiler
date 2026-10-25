<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Quién confirmó el pago y cuándo (null mientras no esté confirmado).
            $table->foreignId('confirmado_por')->nullable()->after('estado_pago')
                ->constrained('usuarios')->nullOnDelete();
            $table->timestamp('confirmado_en')->nullable()->after('confirmado_por');

            // Los pagos son registros financieros: "eliminar" los manda a la
            // papelera en vez de borrarlos físicamente.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('confirmado_en');
            $table->dropConstrainedForeignId('confirmado_por');
        });
    }
};
