<?php

use App\Models\Client;
use App\Models\Cart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignIdFor(Cart::class)->constrained()->onDelete('cascade'); // Links to cart (one-to-one relationship)
            $table->foreignIdFor(Client::class)->constrained()->onDelete('cascade'); // Links to client
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); // Order status
            $table->enum('shipping_status', ['not_shipped', 'shipped', 'on_the_way', 'delivered', 'returned'])->default('not_shipped'); // Shipping status
            $table->decimal('total_price', 10, 2); // Total price of the order
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
