<?php

use App\Models\Product;
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
        Schema::create('varients', function (Blueprint $table) {
            $table->id(); 
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete(); 
            $table->string('color')->nullable(); 
            $table->string('volume')->nullable(); 
            $table->string('varient')->nullable();
            $table->string('SKU_code')->nullable();
            $table->enum('Stock_status', ['in_stock', 'out_of_stock', 'Almost_finished'])
            ->default('in_stock')
            ->after('Stock');
            $table->decimal('Selling_price', 10, 2)->after('price');
            $table->string('weight')->nullable();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('price', 10, 2);
            $table->string('product_image')->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('varients');
    }
};
