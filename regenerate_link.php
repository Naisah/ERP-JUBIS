<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = App\Models\Invoice::find(3);
if ($invoice) {
    $paymongo = app(App\Services\PayMongoService::class);
    $link = $paymongo->createPaymentLink($invoice);
    if ($link['success']) {
        $invoice->update([
            'payment_url' => $link['checkout_url'],
            'payment_reference_id' => $link['reference_id']
        ]);
        echo "Successfully regenerated link with Cards!\n";
    } else {
        echo "Failed to generate link.\n";
    }
}
