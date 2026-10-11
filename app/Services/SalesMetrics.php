<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class SalesMetrics
{
    public function topProducts(int $limit = 10)
    {
        return QuoteItem::join('quotes', 'quote_items.quote_id', '=', 'quotes.id')
            ->join('invoices', 'quotes.id', '=', 'invoices.quote_id')
            ->join('products', 'quote_items.product_id', '=', 'products.id')
            ->where('invoices.status', 'paid')
            ->select('products.name', 'products.sku', 'products.brand',
                DB::raw('SUM(quote_items.quantity) as total_sold'),
                DB::raw('SUM(quote_items.quantity * quote_items.quoted_price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.brand')
            ->orderByDesc('total_sold')->orderBy('products.id')->limit($limit)->get();
    }

    public function monthlyRevenue(): array
    {
        $months = [];
        $start = now()->timezone(config('company.timezone'))->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $month = $start->copy()->subMonthsNoOverflow($i);
            $from = $month->copy()->utc();
            $until = $month->copy()->addMonth()->utc();
            $payments = DB::table('payment_events')->where('created_at', '>=', $from)->where('created_at', '<', $until);
            $legacy = Invoice::where('legacy_paid_at', '>=', $from)->where('legacy_paid_at', '<', $until)->where('legacy_amount_paid', '>', 0);
            $months[] = [
                'month' => $month->format('M Y'),
                'total' => (float) (clone $payments)->sum('amount') + (float) (clone $legacy)->sum('legacy_amount_paid'),
                'count' => $payments->count() + $legacy->count(),
            ];
        }

        return $months;
    }
}
