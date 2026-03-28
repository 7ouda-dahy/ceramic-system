<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_payment_items', 'cashbox_id')) {
                $table->foreignId('cashbox_id')->nullable()->after('invoice_id')->constrained('cashboxes')->nullOnDelete();
            }

            if (!Schema::hasColumn('customer_payment_items', 'remaining_before')) {
                $table->decimal('remaining_before', 15, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('customer_payment_items', 'remaining_after')) {
                $table->decimal('remaining_after', 15, 2)->default(0)->after('remaining_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_payment_items', function (Blueprint $table) {
            if (Schema::hasColumn('customer_payment_items', 'remaining_after')) {
                $table->dropColumn('remaining_after');
            }

            if (Schema::hasColumn('customer_payment_items', 'remaining_before')) {
                $table->dropColumn('remaining_before');
            }

            if (Schema::hasColumn('customer_payment_items', 'cashbox_id')) {
                $table->dropConstrainedForeignId('cashbox_id');
            }
        });
    }
};