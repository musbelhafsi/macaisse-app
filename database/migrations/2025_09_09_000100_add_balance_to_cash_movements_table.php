<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->decimal('balance', 18, 2)->nullable()->after('montant');
            $table->index(['cash_id','date_mvt']);
        });
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex(['cash_id','date_mvt']);
            $table->dropColumn('balance');
        });
    }
};