<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;

class PurgeDiscardedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:purge-discarded';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge discarded carts older than 30 days';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch discarded carts older than 30 days
        $deletedCount = Cart::where('status', 'discarded')
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();

        // Output the result to the console
        $this->info("Successfully deleted {$deletedCount} discarded carts.");
    }
}
