<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->index(); // saisi par l'utilisateur
            $table->foreignId('from_cash_id')->constrained('cash_registers');
            $table->foreignId('to_cash_id')->constrained('cash_registers');
            $table->decimal('montant', 18, 2);
            $table->decimal('montant_recu', 18, 2)->nullable(); // saisi par la caisse receptrice
            $table->decimal('ecart', 18, 2)->default(0); // calculé (montant_recu - montant)
            $table->enum('statut', ['emis', 'valide'])->default('emis');
            $table->timestamp('emitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};