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
        // Find carts that need to be abandoned (older than 24 hours)
        $abandonedCarts = DB::table('carts')
            ->where('status', 'active')
            ->where('updated_at', '<', now()->subHours(24))
            ->pluck('id');

        if ($abandonedCarts->isNotEmpty()) {
            // Delete related cart items before deleting carts
            DB::table('cart_items')->whereIn('cart_id', $abandonedCarts)->delete();

            // Delete the carts that are being marked as abandoned
            DB::table('carts')->whereIn('id', $abandonedCarts)->delete();
        }

        // Find abandoned carts older than 24 days and delete them
        $oldAbandonedCarts = DB::table('carts')
            ->where('status', 'abandoned')
            ->where('updated_at', '<', now()->subDays(1))
            ->pluck('id');

        if ($oldAbandonedCarts->isNotEmpty()) {
            DB::table('cart_items')->whereIn('cart_id', $oldAbandonedCarts)->delete();
            DB::table('carts')->whereIn('id', $oldAbandonedCarts)->delete();
        }

        $this->info('Cart status updated and old abandoned carts deleted successfully.');
    }
}
