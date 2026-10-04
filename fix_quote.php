<?php
$f = "app/Http/Controllers/Admin/QuoteController.php";
$c = file_get_contents($f);

$old = <<<'EOD'
            if (!$existingInvoice) {
                \App\Models\Invoice::create([
                    'quote_id' => $quote->id,
                    'user_id' => $quote->user_id,
                    'total_amount' => $quote->total_amount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                    'billing_address' => $quote->shipping_address ?? $quote->user->billing_address,
                ]);
            }
EOD;

$new = <<<'EOD'
            if (!$existingInvoice) {
                $invoice = \App\Models\Invoice::create([
                    'quote_id' => $quote->id,
                    'user_id' => $quote->user_id,
                    'total_amount' => $quote->total_amount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                    'billing_address' => $quote->shipping_address ?? $quote->user->billing_address,
                ]);
                
                // AUTOMATION: Generate API Payment Link
                $paymentSvc = app(\App\Services\PayMongoService::class);
                $payment = $paymentSvc->createPaymentLink($invoice);
                
                if ($payment['success']) {
                    $invoice->update([
                        'payment_url' => $payment['checkout_url'],
                        'payment_reference_id' => $payment['reference_id']
                    ]);
                }
            }
EOD;

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Done";
