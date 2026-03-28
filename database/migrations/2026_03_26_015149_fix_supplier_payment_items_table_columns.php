<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_payment_items', 'supplier_payment_id')) {
                $table->foreignId('supplier_payment_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('supplier_payment_items', 'purchase_invoice_id')) {
                $table->foreignId('purchase_invoice_id')->nullable()->after('supplier_payment_id')->constrained('purchase_invoices')->nullOnDelete();
            }

            if (!Schema::hasColumn('supplier_payment_items', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('purchase_invoice_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payment_items', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_payment_items', 'amount')) {
                $table->dropColumn('amount');
            }

            if (Schema::hasColumn('supplier_payment_items', 'purchase_invoice_id')) {
                $table->dropConstrainedForeignId('purchase_invoice_id');
            }

            if (Schema::hasColumn('supplier_payment_items', 'supplier_payment_id')) {
                $table->dropConstrainedForeignId('supplier_payment_id');
            }
        });
    }
};