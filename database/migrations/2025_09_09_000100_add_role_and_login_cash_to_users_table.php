<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add role only if not already present (another migration may have added it)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('viewer')->after('password');
            }
            // Add current_cash_id only if not present
            if (!Schema::hasColumn('users', 'current_cash_id')) {
                $table->foreignId('current_cash_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('cash_registers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Only drop current_cash_id; leave role intact (it may come from a different migration)
        if (Schema::hasColumn('users', 'current_cash_id')) {
            Schema::table('users', function (Blueprint $table) {
                // For portability: drop foreign then column
                try { $table->dropConstrainedForeignId('current_cash_id'); } catch (\Throwable $e) {
                    try { $table->dropForeign(['current_cash_id']); } catch (\Throwable $e2) {}
                    $table->dropColumn('current_cash_id');
                }
            });
        }
    }
};