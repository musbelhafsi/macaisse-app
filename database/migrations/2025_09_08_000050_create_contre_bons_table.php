<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contre_bons', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->index(); // saisi par l'utilisateur, modifiable
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('livreur_id')->constrained('users');
            $table->date('date');
            $table->decimal('montant', 18, 2)->default(0); // somme des bons + chèques si inclus
            $table->integer('nombre_bons')->default(0);
            $table->decimal('ecart', 18, 2)->default(0); // calculé
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contre_bons');
    }
};