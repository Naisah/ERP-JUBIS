<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('role', 'client')->first();
    auth()->login($user);
    $product = App\Models\Product::first();
    
    $request = new \Illuminate\Http\Request();
    $request->merge(['product_id' => $product->id, 'quantity' => 2]);
    $request->setMethod('POST');
    
    // Simulate validation environment
    app('validator')->validate($request->all(), [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1'
    ]);
    
    $controller = app(App\Http\Controllers\QuoteController::class);
    $response = $controller->add($request);
    
    $quote = App\Models\Quote::where('user_id', $user->id)->where('status', 'draft')->first();
    $total = $quote->total_amount;
    
    echo "SUCCESS: Added product to cart. New Quote Total Amount: " . $total . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
