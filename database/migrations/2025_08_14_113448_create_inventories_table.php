<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            // Composite unique key
            $table->unique(['outlet_id', 'product_id']);

            // Indexes for performance
            $table->index('outlet_id');
            $table->index('product_id');
            $table->index('quantity'); // For low stock queries
            $table->index(['outlet_id', 'quantity']); // For outlet-specific stock queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
