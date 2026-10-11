<?php
require __DIR__.'/environment.php';
if(!file_exists($qaUiDatabase)) touch($qaUiDatabase);
// Migrations are additive and confined to the exact QA file. Never migrate:fresh a user database.
\Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
foreach(['super_admin','admin','sales','finance','purchasing','warehouse','client'] as $role) {
    \App\Models\User::updateOrCreate(['email'=>$role.'-ui@example.test'],[
        'name'=>'QA '.$role,'role'=>$role,'password'=>\Illuminate\Support\Facades\Hash::make('QaTestOnly123!'),
        'company_name'=>'QA Synthetic Company','shipping_address'=>'QA Test Address','credit_status'=>'approved','email_verified_at'=>now()]);
}
$category=\App\Models\Category::firstOrCreate(['slug'=>'qa-ui'],['name'=>'QA UI Category']);
foreach(['QA Alpha Cable','QA Beta Switch','QA Gamma Socket'] as $i=>$name) {
    \App\Models\Product::updateOrCreate(['sku'=>'QA-UI-'.($i+1)],['name'=>$name,'brand'=>'QA','category_id'=>$category->id,
        'wholesale_price'=>'100.00','stock_quantity'=>100,'unit_of_measure'=>'pcs','is_active'=>true,'reorder_level'=>10]);
}
echo "Seeded synthetic browser fixtures in qa/results/ui.sqlite; no email or external HTTP enabled.\n";
// Dedicated, isolated fixture for the hosted-fix browser rehearsal.
$client = \App\Models\User::where('email', 'client-ui@example.test')->firstOrFail();
$product = \App\Models\Product::where('sku', 'QA-UI-1')->firstOrFail();
$quote = \App\Models\Quote::firstOrCreate(['client_notes' => 'QA FIX RETEST'], [
    'user_id' => $client->id, 'status' => 'approved', 'subtotal' => 125, 'vat_amount' => 15,
    'vat_rate' => 12, 'total_amount' => 140, 'shipping_address' => 'QA Test Address',
]);
$quote->items()->firstOrCreate(['product_id' => $product->id], ['quantity' => 1, 'quoted_price' => 125]);
$invoice = \App\Models\Invoice::firstOrCreate(['quote_id' => $quote->id], [
    'user_id' => $client->id, 'subtotal' => 125, 'vat_amount' => 15, 'vat_rate' => 12,
    'shipping_amount' => 164, 'total_amount' => 304, 'amount_paid' => 0, 'status' => 'unpaid',
    'due_date' => now()->addDays(30)->toDateString(), 'billing_address' => 'QA Test Address',
]);
$invoice->shipments()->firstOrCreate(['tracking_number' => 'QA-FIX-TRACK'], [
    'carrier' => 'Lalamove (API Simulated)', 'status' => 'processing', 'delivery_fee' => 164,
    'shipping_address' => 'QA Test Address', 'tracking_url' => url('/api/mock/tracking/QA-FIX-TRACK'),
]);
