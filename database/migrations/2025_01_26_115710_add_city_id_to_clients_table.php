<?php

use App\Models\City;
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
    Schema::table('clients', function (Blueprint $table) {
            // Add city_id as a foreign key that links to the cities table
            $table->foreignIdFor(City::class,'city_id')
                ->nullable() // Allows NULL for city_id if needed
                ->constrained('cities') // References the cities table
                ->nullOnDelete(); // Sets city_id to NULL if the city is deleted
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Drop the foreign key constraint and the column
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
