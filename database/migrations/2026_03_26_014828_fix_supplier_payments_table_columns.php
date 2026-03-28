<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_payments', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('supplier_payments', 'cashbox_id')) {
                $table->foreignId('cashbox_id')->nullable()->after('supplier_id')->constrained('cashboxes')->nullOnDelete();
            }

            if (!Schema::hasColumn('supplier_payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('cashbox_id')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('supplier_payments', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('created_by');
            }

            if (!Schema::hasColumn('supplier_payments', 'notes')) {
                $table->text('notes')->nullable()->after('total_amount');
            }

            if (!Schema::hasColumn('supplier_payments', 'reference_code')) {
                $table->string('reference_code')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_payments', 'reference_code')) {
                $table->dropColumn('reference_code');
            }

            if (Schema::hasColumn('supplier_payments', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('supplier_payments', 'total_amount')) {
                $table->dropColumn('total_amount');
            }

            if (Schema::hasColumn('supplier_payments', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('supplier_payments', 'cashbox_id')) {
                $table->dropConstrainedForeignId('cashbox_id');
            }

            if (Schema::hasColumn('supplier_payments', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });
    }
};