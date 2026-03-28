<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashboxes', function (Blueprint $table) {
            if (!Schema::hasColumn('cashboxes', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('name');
            }
        });

        if (Schema::hasTable('cashbox_transactions')) {
            $cashboxes = DB::table('cashboxes')->get();

            foreach ($cashboxes as $cashbox) {
                $in = (float) DB::table('cashbox_transactions')
                    ->where('cashbox_id', $cashbox->id)
                    ->where('type', 'IN')
                    ->sum('amount');

                $out = (float) DB::table('cashbox_transactions')
                    ->where('cashbox_id', $cashbox->id)
                    ->where('type', 'OUT')
                    ->sum('amount');

                DB::table('cashboxes')
                    ->where('id', $cashbox->id)
                    ->update([
                        'balance' => $in - $out,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('cashboxes', function (Blueprint $table) {
            if (Schema::hasColumn('cashboxes', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};