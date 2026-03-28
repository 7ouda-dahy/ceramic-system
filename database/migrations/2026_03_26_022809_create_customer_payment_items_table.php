<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashbox_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 15, 2);

            $table->decimal('remaining_before', 15, 2)->default(0);
            $table->decimal('remaining_after', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_items');
    }
};