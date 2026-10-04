<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for products running low on stock and logs alerts for the purchasing department.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowStockItems = Product::whereNotNull('parent_id')
            ->where('stock_quantity', '<=', 10)
            ->where('is_active', true)
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('All inventory levels are healthy.');
            return;
        }

        $this->warn('Found ' . $lowStockItems->count() . ' items running low on stock.');
        
        foreach ($lowStockItems as $item) {
            $msg = "LOW STOCK ALERT: {$item->sku} ({$item->name}) has only {$item->stock_quantity} units remaining.";
            $this->error($msg);
            Log::warning($msg);
            
            // In a real production system with mail configured, we would do:
            // Mail::to('purchasing@jubismarketing.com')->send(new LowStockAlertMail($item));
        }

        $this->info('Low stock check complete. Notifications logged for Purchasing Department.');
    }
}
