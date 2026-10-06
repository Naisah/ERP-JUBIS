<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function exportCsv()
    {
        $products = Product::with('category')->orderBy('sku')->get();
        $csvData = "ID,SKU,Name,Brand,Category,Price,Stock Quantity,Product Type\n";
        
        foreach($products as $p) {
            $type = $p->parent_id ? 'Variant' : 'Master Folder';
            $category = $p->category ? str_replace('"', '""', $p->category->name) : '';
            $name = str_replace('"', '""', $p->name);
            $csvData .= "{$p->id},{$p->sku},\"{$name}\",{$p->brand},\"{$category}\",{$p->wholesale_price},{$p->stock_quantity},{$type}\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="jubis_erp_inventory_' . date('Y-m-d') . '.csv"');
    }

    public function index(Request $request)
    {
        $query = Product::with('category')->withCount('variants')
            ->orderBy('stock_quantity', 'asc')
            ->orderBy('sku', 'asc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'reorder_level');
            } elseif ($request->stock === 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'stock'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => \App\Models\Category::all(),
            // Get unique brands for the datalist/dropdown suggestion
            'existingBrands' => Product::select('brand')->distinct()->orderBy('brand')->pluck('brand')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'wholesale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048' // 2MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $imagePath = '/images/products/' . $filename;
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
            'description' => $validated['description'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => \App\Models\Category::all(),
            'existingBrands' => Product::select('brand')->distinct()->orderBy('brand')->pluck('brand')
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'wholesale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $product->image_path = '/images/products/' . $filename;
        }

        $product->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'brand' => $validated['brand'],
            'category_id' => $validated['category_id'],
            'wholesale_price' => $validated['wholesale_price'],
            'stock_quantity' => $validated['stock_quantity'],
            'unit_of_measure' => $validated['unit_of_measure'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}

