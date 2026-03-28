<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'average_cost')) {
                $table->decimal('average_cost', 14, 2)->default(0)->after('purchase_price');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'cost_price')) {
                $table->decimal('cost_price', 14, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('invoice_items', 'profit_amount')) {
                $table->decimal('profit_amount', 14, 2)->default(0)->after('line_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            foreach (['cost_price', 'profit_amount'] as $column) {
                if (Schema::hasColumn('invoice_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'average_cost')) {
                $table->dropColumn('average_cost');
            }
        });
    }
};