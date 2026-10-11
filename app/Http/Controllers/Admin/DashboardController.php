<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Services\SalesMetrics;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top Level Metrics
        $totalRevenue = Invoice::sum('amount_paid');
        $outstandingReceivables = Invoice::where('status', '!=', 'paid')->sum(DB::raw('total_amount - amount_paid'));
        $pendingQuotesCount = Quote::where('status', 'pending')->count();

        $lowStockProducts = Product::lowStock()->count();

        // 3. Recent Activity Lists
        $recentQuotes = Quote::with('user')->latest()->take(5)->get();
        $recentInvoices = Invoice::with('user')->latest()->take(5)->get();

        // 4. Products Needing Reorder
        $reorderList = Product::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'totalRevenue' => $totalRevenue,
                'outstandingReceivables' => $outstandingReceivables,
                'pendingQuotes' => $pendingQuotesCount,
                'lowStockCount' => $lowStockProducts,
            ],
            'chartData' => app(SalesMetrics::class)->monthlyRevenue(),
            'topProducts' => app(SalesMetrics::class)->topProducts(5),
            'recentQuotes' => $recentQuotes,
            'recentInvoices' => $recentInvoices,
            'reorderList' => $reorderList,
        ]);
    }
}
