<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCartStatus extends Command
{
    protected $signature = 'cart:update-status';
    protected $description = 'Update cart statuses and delete old abandoned carts';

    public function handle()
    {
        // Mark carts as abandoned after 24 hours
        DB::table('carts')
            ->where('status', 'active')
            ->where('updated_at', '<', now()->subHours(24))
            ->update(['status' => 'abandoned', 'updated_at' => now()]);

        // Delete abandoned carts after 24 days
        $cartsToDelete = DB::table('carts')
            ->where('status', 'abandoned')
            ->where('updated_at', '<', now()->subDays(24))
            ->pluck('id');

        if ($cartsToDelete->isNotEmpty()) {
            // Delete related cart items
            DB::table('cart_items')->whereIn('cart_id', $cartsToDelete)->delete();

            // Delete the carts
            DB::table('carts')->whereIn('id', $cartsToDelete)->delete();
        }

        $this->info('Cart status updated successfully.');
    }
}
