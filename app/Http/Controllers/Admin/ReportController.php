<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\SalesMetrics;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // ─── KEY METRICS ───────────────────────────────────────────
        $totalRevenue = Invoice::sum('amount_paid');
        $pendingReceivables = Invoice::where('status', '!=', 'paid')->sum(DB::raw('total_amount - amount_paid'));
        $inventoryValue = Product::stockItems()
            ->where('stock_quantity', '>', 0)
            ->select(DB::raw('SUM(wholesale_price * stock_quantity) as total_value'))
            ->value('total_value');
        $totalProcurement = PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])->sum('total_cost');
        $totalProducts = Product::whereNull('deleted_at')->count();
        $totalSuppliers = Supplier::whereNull('deleted_at')->count();
        $totalQuotes = Quote::where('status', '!=', 'draft')->count();
        $totalInvoices = Invoice::count();

        // ─── MONTHLY REVENUE TREND (Last 6 months) ────────────────
        $revenueChart = array_map(fn ($month) => ['month' => $month['month'], 'revenue' => $month['total'], 'count' => $month['count']], app(SalesMetrics::class)->monthlyRevenue());

        // ─── PROCUREMENT SPEND TREND (Last 6 months) ──────────────
        $procurementChart = [];
        $start = now()->timezone(config('company.timezone'))->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $month = $start->copy()->subMonthsNoOverflow($i);
            $query = PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])
                ->where('created_at', '>=', $month->copy()->utc())
                ->where('created_at', '<', $month->copy()->addMonth()->utc());
            $procurementChart[] = [
                'month' => $month->format('M Y'),
                'spend' => (float) (clone $query)->sum('total_cost'),
                'count' => $query->count(),
            ];
        }

        // ─── TOP 10 SELLING PRODUCTS ──────────────────────────────
        $topProducts = app(SalesMetrics::class)->topProducts();

        // ─── LOW STOCK ALERTS (Below reorder level) ───────────────
        $lowStockProducts = Product::lowStock()
            ->select('id', 'name', 'sku', 'brand', 'stock_quantity', 'reorder_level')
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();

        $outOfStockCount = Product::outOfStock()->count();

        // ─── INVENTORY BY BRAND (Pie chart data) ──────────────────
        $inventoryByBrand = Product::stockItems()
            ->where('stock_quantity', '>', 0)
            ->select(
                'brand',
                DB::raw('SUM(stock_quantity) as total_units'),
                DB::raw('SUM(wholesale_price * stock_quantity) as total_value')
            )
            ->groupBy('brand')
            ->orderByDesc('total_value')
            ->take(8)
            ->get();

        // ─── RECENT STOCK MOVEMENTS (Audit trail) ─────────────────
        $recentMovements = StockMovement::with(['product:id,name,sku', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // ─── RECENT PAID INVOICES ─────────────────────────────────
        $recentSales = Invoice::with('user')
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ─── QUOTE CONVERSION FUNNEL ──────────────────────────────
        $quoteFunnel = [
            'submitted' => Quote::where('status', '!=', 'draft')->count(),
            'reviewed' => Quote::whereIn('status', ['reviewed', 'approved', 'rejected'])->count(),
            'approved' => Quote::where('status', 'approved')->count(),
            'invoiced' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/Reports/Index', [
            'metrics' => [
                'totalRevenue' => $totalRevenue ?: 0,
                'pendingReceivables' => $pendingReceivables ?: 0,
                'inventoryValue' => $inventoryValue ?: 0,
                'totalProcurement' => $totalProcurement ?: 0,
                'totalProducts' => $totalProducts,
                'totalSuppliers' => $totalSuppliers,
                'totalQuotes' => $totalQuotes,
                'totalInvoices' => $totalInvoices,
                'outOfStockCount' => $outOfStockCount,
            ],
            'revenueChart' => $revenueChart,
            'procurementChart' => $procurementChart,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'inventoryByBrand' => $inventoryByBrand,
            'recentMovements' => $recentMovements,
            'recentSales' => $recentSales,
            'quoteFunnel' => $quoteFunnel,
        ]);
    }
}
