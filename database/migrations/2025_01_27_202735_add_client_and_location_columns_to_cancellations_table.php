<?php

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
        Schema::table('cancellations', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); 
            $table->string('client_name');
            $table->string('client_phonenumber');
            $table->string('addiontal_phonenumber');
            $table->string('address', 255);
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null'); 
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null'); 
            $table->string('city_name')->nullable();
            $table->string('area_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellations', function (Blueprint $table) {
            //
        });
    }
};
