<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cashbox_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('cashbox_transactions', 'cashbox_id')) {
                $table->foreignId('cashbox_id')->nullable()->after('id')->constrained('cashboxes')->nullOnDelete();
            }
            if (!Schema::hasColumn('cashbox_transactions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('cashbox_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('cashbox_transactions', 'reference_code')) {
                $table->string('reference_code')->nullable()->after('reason');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'cashbox_id')) {
                $table->foreignId('cashbox_id')->nullable()->after('supplier_id')->constrained('cashboxes')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('cashbox_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_returns', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('invoice_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            foreach (['created_by'] as $column) {
                if (Schema::hasColumn('sales_returns', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            foreach (['cashbox_id', 'created_by'] as $column) {
                if (Schema::hasColumn('purchase_invoices', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['branch_id', 'created_by'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });

        Schema::table('cashbox_transactions', function (Blueprint $table) {
            foreach (['cashbox_id', 'created_by'] as $column) {
                if (Schema::hasColumn('cashbox_transactions', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            if (Schema::hasColumn('cashbox_transactions', 'reference_code')) {
                $table->dropColumn('reference_code');
            }
        });
    }
};