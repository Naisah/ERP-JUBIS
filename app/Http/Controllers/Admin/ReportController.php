<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // ─── KEY METRICS ───────────────────────────────────────────
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $pendingReceivables = Invoice::where('status', 'unpaid')->sum('total_amount');
        $inventoryValue = DB::table('products')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where('stock_quantity', '>', 0)
            ->select(DB::raw('SUM(wholesale_price * stock_quantity) as total_value'))
            ->value('total_value');
        $totalProcurement = PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])->sum('total_cost');
        $totalProducts = Product::whereNull('deleted_at')->count();
        $totalSuppliers = Supplier::whereNull('deleted_at')->count();
        $totalQuotes = Quote::where('status', '!=', 'draft')->count();
        $totalInvoices = Invoice::count();

        // ─── MONTHLY REVENUE TREND (Last 6 months) ────────────────
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fill in missing months with zero
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $found = $monthlyRevenue->firstWhere('month', $monthKey);
            $revenueChart[] = [
                'month' => $monthLabel,
                'revenue' => $found ? (float) $found->revenue : 0,
                'count' => $found ? (int) $found->count : 0,
            ];
        }

        // ─── PROCUREMENT SPEND TREND (Last 6 months) ──────────────
        $monthlyProcurement = PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_cost) as spend'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $procurementChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $found = $monthlyProcurement->firstWhere('month', $monthKey);
            $procurementChart[] = [
                'month' => $monthLabel,
                'spend' => $found ? (float) $found->spend : 0,
                'count' => $found ? (int) $found->count : 0,
            ];
        }

        // ─── TOP 10 SELLING PRODUCTS ──────────────────────────────
        $topProducts = QuoteItem::join('quotes', 'quote_items.quote_id', '=', 'quotes.id')
            ->join('invoices', 'quotes.id', '=', 'invoices.quote_id')
            ->join('products', 'quote_items.product_id', '=', 'products.id')
            ->select(
                'products.name', 
                'products.sku', 
                'products.brand',
                DB::raw('SUM(quote_items.quantity) as total_sold'), 
                DB::raw('SUM(quote_items.quantity * quote_items.quoted_price) as total_revenue')
            )
            ->where('invoices.status', 'paid')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.brand')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        // ─── LOW STOCK ALERTS (Below reorder level) ───────────────
        $lowStockProducts = Product::whereNull('deleted_at')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->select('id', 'name', 'sku', 'brand', 'stock_quantity', 'reorder_level')
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();

        $outOfStockCount = Product::whereNull('deleted_at')
            ->where('is_active', true)
            ->where('stock_quantity', '<=', 0)
            ->count();

        // ─── INVENTORY BY BRAND (Pie chart data) ──────────────────
        $inventoryByBrand = Product::whereNull('deleted_at')
            ->where('is_active', true)
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
