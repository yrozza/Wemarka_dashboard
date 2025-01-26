<?php

use App\Models\Source;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Area;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Source::class);
            $table->foreignIdFor(Area::class, 'area_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->string('client_age')->nullable();
            $table->string('area_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phonenumber')->nullable();
            $table->timestamps();
        });
        
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
