<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Caisse name (including main/bank)
            $table->boolean('is_main')->default(false); // main cash/bank
            $table->string('currency', 3)->default('DA');
            $table->decimal('balance', 18, 2)->default(0); // transit balance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};