<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Order;
use App\Models\Varient;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignIdFor(Order::class)->constrained()->onDelete('cascade'); // Links to orders table
            $table->foreignIdFor(Varient::class)->constrained()->onDelete('cascade'); // Links to variants table
            $table->integer('quantity'); // Quantity of the variant in the order
            $table->decimal('price', 10, 2); // Price of the variant at the time of purchase
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
