<?php

use App\Models\brand;
use App\Models\Category;
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
            $table->foreignIdFor(Brand::class);
            $table->foreignIdFor(Category::class);
            $table->string('Product_name');
            $table->text('Product_description');
            $table->string('Origin');
            $table->string('Benefit');
            $table->string('Effect');
            $table->text('Ingredients');
            $table->string('Supplier');
            $table->string('Category_name')->after('category_id');
            $table->string('Brand_name')->after('brand_id');
            $table->string('Subcategory')->after('Category_name');
            $table->string('Tags');
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
