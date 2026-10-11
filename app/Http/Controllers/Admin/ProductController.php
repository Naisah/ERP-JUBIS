<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function exportCsv()
    {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['ID', 'SKU', 'Name', 'Brand', 'Category', 'Price', 'Stock Quantity', 'Product Type'], ',', '"', '');
        $safe = fn ($text) => preg_match('/^[\s\x{FEFF}]*[=+@-]/u', (string) $text) ? "'".$text : $text;
        foreach (Product::with('category')->withCount('variants')->orderBy('sku')->cursor() as $product) {
            fputcsv($stream, [
                $product->id, $safe($product->sku), $safe($product->name), $safe($product->brand),
                $safe($product->category?->name ?? ''), $product->wholesale_price,
                $product->variants_count ? '' : $product->stock_quantity,
                $product->variants_count ? 'Master Folder' : ($product->parent_id ? 'Variant' : 'Product'),
            ], ',', '"', '');
        }
        rewind($stream);
        $csvData = stream_get_contents($stream);
        fclose($stream);

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="jubis_erp_inventory_'.now()->timezone(config('company.timezone'))->format('Y-m-d').'.csv"');
    }

    public function index(Request $request)
    {
        $query = Product::with('category')->withCount('variants')
            ->orderBy('stock_quantity', 'asc')
            ->orderBy('sku', 'asc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('sku', 'like', '%'.$searchTerm.'%')
                    ->orWhere('brand', 'like', '%'.$searchTerm.'%');
            });
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->lowStock();
            } elseif ($request->stock === 'out') {
                $query->outOfStock();
            }
        }

        $totalInventoryValue = (clone $query)->stockItems()->sum(DB::raw('stock_quantity * wholesale_price'));
        // paginate() mutates the builder's limit/offset; aggregate before it runs.
        $stockSummary = [
            'lowStockCount' => (clone $query)->lowStock()->count(),
            'outOfStockCount' => (clone $query)->outOfStock()->count(),
        ];

        return Inertia::render('Admin/Products/Index', [
            'products' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'stock']),
            'totalInventoryValue' => $totalInventoryValue,
            'stockSummary' => $stockSummary,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::all(),
            // Get unique brands for the datalist/dropdown suggestion
            'existingBrands' => Product::select('brand')->distinct()->orderBy('brand')->pluck('brand'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'wholesale_price' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'stock_quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $imagePath = '/images/products/'.$filename;
        }

        Product::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'brand' => $validated['brand'],
            'category_id' => $validated['category_id'],
            'wholesale_price' => $validated['wholesale_price'],
            'stock_quantity' => $validated['stock_quantity'],
            'unit_of_measure' => $validated['unit_of_measure'],
            'is_active' => true,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::all(),
            'existingBrands' => Product::select('brand')->distinct()->orderBy('brand')->pluck('brand'),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'wholesale_price' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'stock_quantity' => 'required|integer|min:0',
            'original_stock_quantity' => 'sometimes|integer|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $uploadedImagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $uploadedImagePath = '/images/products/'.$filename;
        }

        DB::transaction(function () use ($product, $validated, $uploadedImagePath) {
            $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            if (isset($validated['original_stock_quantity']) && (int) $validated['original_stock_quantity'] !== (int) $product->stock_quantity) {
                throw ValidationException::withMessages(['stock_quantity' => 'Stock changed since this page was opened. Reload the product before saving.']);
            }
            $delta = (int) $validated['stock_quantity'] - $product->stock_quantity;
            $product->update([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'brand' => $validated['brand'],
                'category_id' => $validated['category_id'],
                'wholesale_price' => $validated['wholesale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'unit_of_measure' => $validated['unit_of_measure'],
                'description' => $validated['description'] ?? null,
                'image_path' => $uploadedImagePath ?? $product->getRawOriginal('image_path'),
            ]);
            if ($delta !== 0) {
                StockMovement::create([
                    'product_id' => $product->id, 'user_id' => auth()->id(), 'quantity' => $delta,
                    'type' => 'adjustment', 'reference_id' => 'PRODUCT-'.$product->id,
                    'notes' => 'Manual inventory adjustment',
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
