<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contre_bons', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('note');
            $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('validated_cash_id')->nullable()->after('validated_by')->constrained('cash_registers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contre_bons', function (Blueprint $table) {
            // Drop FKs first for portability
            if (Schema::hasColumn('contre_bons', 'validated_cash_id')) {
                try { $table->dropConstrainedForeignId('validated_cash_id'); } catch (\Throwable $e) {
                    try { $table->dropForeign(['validated_cash_id']); } catch (\Throwable $e2) {}
                    $table->dropColumn('validated_cash_id');
                }
            }
            if (Schema::hasColumn('contre_bons', 'validated_by')) {
                try { $table->dropConstrainedForeignId('validated_by'); } catch (\Throwable $e) {
                    try { $table->dropForeign(['validated_by']); } catch (\Throwable $e2) {}
                    $table->dropColumn('validated_by');
                }
            }
            if (Schema::hasColumn('contre_bons', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
        });
    }
};