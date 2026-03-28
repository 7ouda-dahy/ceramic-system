<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_cashbox_id')->constrained('cashboxes')->cascadeOnDelete();
            $table->foreignId('to_cashbox_id')->constrained('cashboxes')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('notes')->nullable();
            $table->string('reference_code')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');
    }
};