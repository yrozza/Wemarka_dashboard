<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Varient;
use App\Models\Cart;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignIdFor(Cart::class)->constrained()->onDelete('cascade'); // Links to carts table
            $table->foreignIdFor(Varient::class)->constrained()->onDelete('cascade'); // Links to variants table
            $table->integer('quantity'); // Quantity of the variant in the cart
            $table->decimal('price', 10, 2); // Price at the time of adding to the cart
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
