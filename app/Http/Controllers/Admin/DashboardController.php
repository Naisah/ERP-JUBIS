<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top Level Metrics
        $totalRevenue = Invoice::where('status', 'paid')->sum('amount_paid');
        $outstandingReceivables = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum(DB::raw('total_amount - amount_paid'));
        $pendingQuotesCount = Quote::where('status', 'pending')->count();
        
        $lowStockProducts = Product::where('stock_quantity', '<', 20)->where('is_active', true)->count();

        // 2. Monthly Revenue Chart Data (Last 6 Months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sum = Invoice::where('status', 'paid')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('amount_paid');
                
            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'total' => $sum
            ];
        }

        // 3. Recent Activity Lists
        $recentQuotes = Quote::with('user')->latest()->take(5)->get();
        $recentInvoices = Invoice::with('user')->latest()->take(5)->get();
        
        // 4. Products Needing Reorder
        $reorderList = Product::where('stock_quantity', '<', 20)
            ->where('is_active', true)
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
            'chartData' => $monthlyRevenue,
            'recentQuotes' => $recentQuotes,
            'recentInvoices' => $recentInvoices,
            'reorderList' => $reorderList,
        ]);
    }
}

