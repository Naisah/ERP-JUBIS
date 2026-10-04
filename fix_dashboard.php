<?php
$f = "resources/js/Pages/Dashboard.vue";
$c = file_get_contents($f);

$old = <<<EOD
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 font-medium uppercase mb-0.5">Estimated Total</div>
                                        <div class="text-lg font-bold text-gray-900">₱{{ Number(quote.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</div>
                                    </div>
EOD;

$new = <<<EOD
                                    <div class="text-right flex flex-col items-end">
                                        <div class="text-xs text-gray-500 font-medium uppercase mb-0.5">Estimated Total</div>
                                        <div class="text-lg font-bold text-gray-900">₱{{ Number(quote.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</div>
                                        <a v-if="quote.invoice && quote.invoice.status !== 'paid' && quote.invoice.payment_url" :href="quote.invoice.payment_url" class="mt-2 text-xs font-bold bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-full transition shadow-sm">
                                            Pay via GCash/Card
                                        </a>
                                        <span v-else-if="quote.invoice && quote.invoice.status === 'paid'" class="mt-2 text-xs font-bold text-green-600 flex items-center">
                                            <CheckCircleIcon class="w-4 h-4 mr-1" /> Payment Complete
                                        </span>
                                    </div>
EOD;

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Done";
