<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->decimal('invoice_total', 10, 2)->default(0);
            $table->decimal('advance_credit', 10, 2)->default(0);
        });

        DB::table('rent_invoices')->update([
            'invoice_total' => DB::raw('amount_due'),
        ]);
    }

    public function down(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_total', 'advance_credit']);
        });
    }
};
