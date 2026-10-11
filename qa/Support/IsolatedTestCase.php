<?php
namespace JubisQA;

use App\Models\{Category, Invoice, Product, Quote, QuoteItem, Supplier, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http, Mail, Notification};

abstract class IsolatedTestCase extends \Tests\TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (!app()->environment('testing') || config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('QA refused to migrate a non-isolated database.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Mail::fake(); Notification::fake(); Http::preventStrayRequests();
        $this->withoutVite();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 10:00:00', 'Asia/Manila'));
    }

    protected function actor(string $role = 'client'): User
    {
        return User::factory()->create(['role' => $role, 'credit_status' => 'approved', 'company_name' => 'QA Synthetic Company', 'shipping_address' => 'QA Test Address']);
    }

    protected function product(array $overrides = []): Product
    {
        $category = Category::create(['name' => 'QA Category', 'slug' => 'qa-'.uniqid()]);
        return Product::create(array_merge(['name' => 'QA Cable', 'sku' => 'QA-'.uniqid(), 'brand' => 'QA', 'category_id' => $category->id,
            'wholesale_price' => '100.00', 'stock_quantity' => 100, 'unit_of_measure' => 'pcs', 'is_active' => true, 'reorder_level' => 10], $overrides));
    }

    protected function quote(User $user, Product $product, int $quantity = 2, string $status = 'pending'): Quote
    {
        $subtotal = round($quantity * (float) $product->wholesale_price, 2);
        $quote = Quote::create(['user_id' => $user->id, 'status' => $status, 'subtotal' => $subtotal, 'vat_amount' => round($subtotal * .12, 2),
            'total_amount' => round($subtotal * 1.12, 2), 'shipping_address' => 'QA Test Address']);
        QuoteItem::create(['quote_id' => $quote->id, 'product_id' => $product->id, 'quantity' => $quantity, 'quoted_price' => $product->wholesale_price]);
        return $quote;
    }

    protected function invoice(User $user, int $quantity = 2, string $status = 'unpaid'): Invoice
    {
        $quote = $this->quote($user, $this->product(['stock_quantity' => 2000]), $quantity, 'approved');
        return Invoice::create(['quote_id' => $quote->id, 'user_id' => $user->id, 'subtotal' => $quote->subtotal, 'vat_amount' => $quote->vat_amount,
            'total_amount' => $quote->total_amount, 'amount_paid' => $status === 'paid' ? $quote->total_amount : 0,
            'status' => $status, 'due_date' => now()->addDays(30), 'billing_address' => 'QA Test Address']);
    }

    protected function productPayload(Product $product, array $overrides = []): array
    {
        return array_merge($product->only(['name', 'sku', 'brand', 'category_id', 'wholesale_price', 'stock_quantity', 'unit_of_measure']), ['description' => 'QA synthetic fixture'], $overrides);
    }
}
