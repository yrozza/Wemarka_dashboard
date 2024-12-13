<?php

use App\Models\Source;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Source::class);
            $table->string('client_name')->nullable();
            $table->string('client_age')->nullable();
            $table->string('client_area')->nullable();
            $table->string('client_city')->nullable();
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
