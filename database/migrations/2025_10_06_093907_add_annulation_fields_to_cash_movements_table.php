<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->boolean('annule')->default(false)->after('date_mvt');
            $table->text('raison_annulation')->nullable()->after('annule');
            $table->timestamp('annule_le')->nullable()->after('raison_annulation');
            $table->foreignId('annule_par')->nullable()->after('annule_le')
                  ->constrained('users')->onDelete('set null');
        });

        // Ajout de la valeur 'annulation' à la contrainte CHECK sur le champ type (PostgreSQL)
        DB::statement("ALTER TABLE cash_movements DROP CONSTRAINT IF EXISTS cash_movements_type_check;");
        DB::statement("ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_type_check CHECK (type IN ('recette', 'depense', 'transfert_debit', 'transfert_credit', 'ajustement', 'annulation'));");
    }

    public function down()
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['annule_par']);
            $table->dropColumn(['annule', 'raison_annulation', 'annule_le', 'annule_par']);
        });
        // Restaure la contrainte CHECK d'origine (sans 'annulation')
        DB::statement("ALTER TABLE cash_movements DROP CONSTRAINT IF EXISTS cash_movements_type_check;");
        DB::statement("ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_type_check CHECK (type IN ('recette', 'depense', 'transfert_debit', 'transfert_credit', 'ajustement'));");
    }
};

