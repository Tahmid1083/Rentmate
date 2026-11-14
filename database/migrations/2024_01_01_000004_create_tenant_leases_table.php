<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('tenant_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_name');
            $table->string('tenant_email')->nullable();
            $table->string('tenant_phone')->nullable();
            $table->date('lease_start');
            $table->date('lease_end')->nullable();
            $table->decimal('monthly_rent', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->string('status')->default('active'); // active | upcoming | expired | terminated
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_leases');
    }
};
