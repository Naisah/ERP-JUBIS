<?php

$file = 'app/Http/Controllers/Admin/QuoteController.php';
$content = file_get_contents($file);

$target = "'billing_address' => \$quote->user->billing_address ?? 'Not specified',\n                ]);\n            }";

$replacement = "'billing_address' => \$quote->user->billing_address ?? 'Not specified',
                ]);
            }
            
            // AUTOMATION: Generate PayMongo Checkout Link
            try {
                \$paymongo = app(\App\Services\PayMongoService::class);
                \$link = \$paymongo->createPaymentLink(\$existingInvoice ?? \App\Models\Invoice::where('quote_id', \$quote->id)->first());
                if (\$link && isset(\$link['checkout_url'])) {
                    \App\Models\Invoice::where('quote_id', \$quote->id)->update([
                        'payment_url' => \$link['checkout_url'],
                        'payment_reference_id' => \$link['id'] ?? null
                    ]);
                }
            } catch (\Exception \$e) {
                \Illuminate\Support\Facades\Log::error('PayMongo Auto-Generation Failed: ' . \$e->getMessage());
            }";

$newContent = str_replace($target, $replacement, $content);
file_put_contents($file, $newContent);

echo "Successfully injected logic.";
