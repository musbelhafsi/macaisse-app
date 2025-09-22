<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->boolean('is_bank')->default(false)->after('is_main');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            if (Schema::hasColumn('cash_registers', 'is_bank')) {
                $table->dropColumn('is_bank');
            }
        });
    }
};