<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bordereaux', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // auto-généré
            $table->date('date_envoi');
            $table->text('note')->nullable();
            $table->enum('status', ['draft','sent'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux');
    }
};