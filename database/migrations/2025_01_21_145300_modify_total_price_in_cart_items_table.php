<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Modify the total_price column and set the default value to 0
            $table->decimal('total_price', 10, 2)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Revert the total_price column to its original state if needed
            $table->decimal('total_price', 10, 2)->change();
        });
    }

};
