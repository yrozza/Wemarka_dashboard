<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Area;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignIdFor(Area::class, 'area_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['area_id']); // Drop the foreign key
            $table->dropColumn('area_id'); // Drop the area_id column
        });

    }
};
