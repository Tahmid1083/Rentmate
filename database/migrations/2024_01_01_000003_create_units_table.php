<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('label');              // e.g. "Apt 12", "Suite 200"
            $table->string('floor')->nullable();
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(0);
            $table->unsignedInteger('square_feet')->nullable();
            $table->decimal('market_rent', 10, 2)->default(0);
            $table->boolean('is_occupied')->default(false);
            $table->timestamps();

            $table->index(['property_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
