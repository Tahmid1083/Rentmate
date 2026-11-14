<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('category'); // mortgage | taxes | insurance | utilities | repair | management_fee | other
            $table->string('vendor')->nullable();
            $table->string('description')->nullable();
            $table->date('incurred_on');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_recurring')->default(false);
            $table->string('receipt_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'incurred_on']);
            $table->index(['property_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
