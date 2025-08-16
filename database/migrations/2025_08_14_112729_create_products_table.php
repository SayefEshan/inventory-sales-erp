<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 50)->unique();
            $table->string('category', 100);
            $table->enum('unit', ['kg', 'litre', 'piece', 'box', 'dozen']);
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('name');
            $table->index(['category', 'name']); // Composite for filtering
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
