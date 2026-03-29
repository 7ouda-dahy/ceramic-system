<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $cashboxes = DB::table('cashboxes')->select('id', 'name', 'slug')->get();

        foreach ($cashboxes as $cashbox) {
            if (!empty($cashbox->slug)) {
                continue;
            }

            $slug = Str::slug($cashbox->name);

            if (empty($slug)) {
                $slug = 'cashbox-' . $cashbox->id;
            }

            $originalSlug = $slug;
            $counter = 1;

            while (DB::table('cashboxes')
                ->where('slug', $slug)
                ->where('id', '!=', $cashbox->id)
                ->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            DB::table('cashboxes')
                ->where('id', $cashbox->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        //
    }
};