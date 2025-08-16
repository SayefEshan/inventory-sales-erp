<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->date('date');
            $table->integer('quantity_sold');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();

            // Indexes for 15M+ records performance
            $table->index('date');
            $table->index('outlet_id');
            $table->index('product_id');
            $table->index(['date', 'outlet_id']); // For date range queries per outlet
            $table->index(['date', 'product_id']); // For product sales over time
            $table->index(['outlet_id', 'product_id', 'date']); // Composite for complex queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
