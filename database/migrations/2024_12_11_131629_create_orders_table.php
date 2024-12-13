<?php

use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade'); 
            $table->decimal('selling_price',8,2);
            $table->timestamp('created_at')->default(Carbon::now()->format('Y-m-d H:i:s')); 
            $table->timestamp('updated_at')->default(Carbon::now()->format('Y-m-d H:i:s')); 
            $table->decimal('total_price',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
