<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bordereau_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_id')->constrained('bordereaux')->cascadeOnDelete();
            $table->enum('type', ['contre_bon','cheque']);
            $table->unsignedBigInteger('reference_id')->nullable(); // id de l'entité référencée (nullable pour chèques ad-hoc)
            $table->string('numero_ref')->nullable(); // cache pour impression
            $table->decimal('montant', 18, 2)->nullable(); // cache pour impression
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['type','reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereau_lignes');
    }
};