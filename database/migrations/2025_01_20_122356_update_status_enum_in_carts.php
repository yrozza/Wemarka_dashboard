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
        Schema::table('carts', function (Blueprint $table) {
            $table->enum('status', ['active', 'checked_out', 'abandoned']) // Remove 'discarded'
            ->default('active')
            ->change();
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->enum('status', ['active', 'checked_out', 'abandoned', 'discarded']) // Add 'discarded' back
            ->default('active')
            ->change();
        });
    }
};
