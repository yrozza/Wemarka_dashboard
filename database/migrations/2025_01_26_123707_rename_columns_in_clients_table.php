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
        Schema::table('clients', function (Blueprint $table) {
            // Renaming the columns
            $table->renameColumn('client_area', 'area_name');
            $table->renameColumn('client_city', 'city_name');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Reverting the column names in case of rollback
            $table->renameColumn('area_name', 'client_area');
            $table->renameColumn('city_name', 'client_city');
        });
    }
};
