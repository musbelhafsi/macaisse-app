<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_id')->constrained('cash_registers');
            
            $table->enum('type', ['recette', 'depense', 'transfert_debit', 'transfert_credit', 'ajustement']);
            $table->decimal('montant', 18, 2);
            $table->string('source_type')->nullable(); // morph-like pointer for provenance
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date_mvt')->useCurrent();
            $table->timestamps();
            $table->index(['source_type','source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};