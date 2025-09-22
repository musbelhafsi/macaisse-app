<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recouvrement_bons', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->index();
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('livreur_id')->constrained('users');
            $table->date('date_recouvrement');
            $table->foreignId('client_id')->constrained('clients');
            $table->decimal('montant', 18, 2);
            $table->enum('type', ['espece', 'cheque'])->default('espece');
            $table->text('note')->nullable();
            $table->foreignId('contre_bon_id')->nullable()->constrained('contre_bons')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recouvrement_bons');
    }
};