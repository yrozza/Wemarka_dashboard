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
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null'); // Link area_id
            $table->string('city_name')->nullable();  // Add city_name column
            $table->string('area_name')->nullable();  // Add area_name column
            $table->string('Address')->nullable(); // Add the destination column
            $table->string('client_name')->nullable()->after('client_id');
            $table->string('client_phone')->nullable()->after('client_name');
            $table->string('additional_phone')->nullable()->after('client_phone');
            $table->text('client_notes')->nullable();  // Add the client_notes colume
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); // Order status
            $table->enum('shipping_status', ['not_shipped', 'shipped', 'on_the_way', 'delivered', 'returned'])->default('not_shipped'); // Shipping status
            $table->decimal('total_price', 10, 2); // Total price of the order
            $table->decimal('Cost_shipping_price', 10, 2)->after('shipping_status');
            $table->decimal('Shipping_price')->after('Cost_shipping_price');
            $table->boolean('packing')->default(false);
            $table->decimal('packing_price', 10, 2)->nullable();
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
