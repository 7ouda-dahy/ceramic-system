<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'supplier_invoice_reference')) {
                $table->string('supplier_invoice_reference')->nullable()->after('supplier_phone');
            }

            if (!Schema::hasColumn('purchase_invoices', 'notes')) {
                $table->text('notes')->nullable()->after('supplier_invoice_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'supplier_invoice_reference')) {
                $table->dropColumn('supplier_invoice_reference');
            }

            if (Schema::hasColumn('purchase_invoices', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};