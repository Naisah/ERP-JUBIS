<?php

use App\Services\CompanySettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refuse ambiguous existing data; never delete duplicate business documents.
        foreach (['invoices' => 'quote_id', 'shipments' => 'invoice_id', 'r_m_a_s' => 'invoice_id'] as $table => $column) {
            if (DB::table($table)->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException("Resolve duplicate {$table}.{$column} records before migrating.");
            }
        }
        if (DB::table('invoices')->whereNotNull('payment_reference_id')->select('payment_reference_id')->groupBy('payment_reference_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Resolve duplicate invoice payment references before migrating.');
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('quote_id');
            $table->unique('payment_reference_id');
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(12);
            $table->decimal('legacy_amount_paid', 12, 2)->default(0);
            $table->timestamp('legacy_paid_at')->nullable();
        });
        Schema::table('quotes', fn (Blueprint $table) => $table->decimal('vat_rate', 5, 2)->default(12));
        Schema::table('shipments', fn (Blueprint $table) => $table->unique('invoice_id'));
        Schema::table('r_m_a_s', fn (Blueprint $table) => $table->unique('invoice_id'));
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->timestamp('updated_at')->nullable();
        });
        DB::table('app_settings')->insert([
            'key' => 'company', 'value' => json_encode(app(CompanySettings::class)->all(), JSON_THROW_ON_ERROR), 'updated_at' => now(),
        ]);
        DB::table('invoices')->where('amount_paid', '>', 0)->update([
            'legacy_amount_paid' => DB::raw('amount_paid'), 'legacy_paid_at' => DB::raw('updated_at'),
        ]);
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source');
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('created_at');
        });
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('created_at');
        });
        DB::table('invoices')->whereNotNull('payment_reference_id')->orderBy('id')->each(function ($invoice) {
            DB::table('payment_links')->insert([
                'invoice_id' => $invoice->id, 'reference' => $invoice->payment_reference_id,
                'amount' => $invoice->total_amount, 'created_at' => $invoice->created_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_links');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('app_settings');
        Schema::table('r_m_a_s', fn (Blueprint $table) => $table->dropUnique(['invoice_id']));
        Schema::table('shipments', fn (Blueprint $table) => $table->dropUnique(['invoice_id']));
        Schema::table('quotes', fn (Blueprint $table) => $table->dropColumn('vat_rate'));
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['quote_id']);
            $table->dropUnique(['payment_reference_id']);
            $table->dropColumn(['shipping_amount', 'vat_rate', 'legacy_amount_paid', 'legacy_paid_at']);
        });
    }
};
