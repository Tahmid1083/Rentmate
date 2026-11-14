<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency', 8)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('locale', 10)->default('en');
            $table->unsignedSmallInteger('late_fee_grace_days')->default(5);
            $table->decimal('late_fee_flat', 10, 2)->default(25.00);
            $table->decimal('late_fee_percent', 5, 2)->default(0.00); // % of outstanding
            $table->boolean('notify_invoice_created')->default(true);
            $table->boolean('notify_payment_received')->default(true);
            $table->boolean('notify_maintenance_status')->default(true);
            $table->boolean('notify_lease_expiring')->default(true);
            $table->boolean('notify_overdue_reminder')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
