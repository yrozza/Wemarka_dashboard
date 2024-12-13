<?php

use GuzzleHttp\Client;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_varient');
            $table->text('product_description');
            $table->decimal('product_price',8,2);
            $table->string('product_color',20);
            $table->decimal('product_weight',8,2);
            $table->string('product_brand');
            $table->string('product_category');
            $table->decimal('product_material');
            $table->decimal('product_volume',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
