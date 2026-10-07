<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // These checks use only a disposable in-memory database, never local inventory.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('brand');
            $table->string('sku'); $table->integer('category_id');
            $table->integer('parent_id')->nullable(); $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        DB::table('categories')->insert([['id' => 1, 'name' => 'Lighting'], ['id' => 2, 'name' => 'Wire']]);
        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Zulu', 'brand' => 'Alpha', 'sku' => 'Z', 'category_id' => 1],
            ['id' => 2, 'name' => 'Alpha', 'brand' => 'Zulu', 'sku' => 'A', 'category_id' => 2],
            ['id' => 3, 'name' => 'Bravo', 'brand' => 'Alpha', 'sku' => 'B', 'category_id' => 1],
        ]);
    }

    public function test_catalog_sorts_by_name_and_keeps_the_selection(): void
    {
        $this->get('/products?sort=name')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')->where('filters.sort', 'name')
            ->where('products.data.0.name', 'Alpha')->where('products.data.1.name', 'Bravo')
            ->where('products.data.2.name', 'Zulu'));
    }

    public function test_catalog_sorts_by_brand_then_name(): void
    {
        $this->get('/products?sort=brand')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.name', 'Bravo')->where('products.data.1.name', 'Zulu')
            ->where('products.data.2.name', 'Alpha'));
    }

    public function test_category_filter_updates_total_and_preserves_sort(): void
    {
        $this->get('/products?categories=1&sort=name')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.total', 2)->where('filters.categories', '1')->where('filters.sort', 'name')
            ->where('products.data.0.name', 'Bravo'));
    }

    public function test_contact_accepts_fields_without_sending_real_email(): void
    {
        Mail::fake();
        $this->post('/contact', ['first_name' => 'Test', 'last_name' => 'Visitor', 'email' => 'test@example.com', 'message' => 'Automated test.'])
            ->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
        Mail::assertSent(\App\Mail\ContactMessageMail::class);
    }
}
