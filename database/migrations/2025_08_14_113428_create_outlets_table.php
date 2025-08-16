<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->foreignId('distributor_id')->constrained();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('distributor_id');
            $table->index('city');
            $table->index('state');
            $table->index(['distributor_id', 'city']); // Composite for region queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
