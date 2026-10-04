<?php
$indexFile = "resources/js/Pages/Products/Index.vue";
$indexContent = file_get_contents($indexFile);
$indexContent = str_replace('?{{ Number(product.wholesale_price)', '₱{{ Number(product.wholesale_price)', $indexContent);
file_put_contents($indexFile, $indexContent);

$showFile = "resources/js/Pages/Products/Show.vue";
$showContent = file_get_contents($showFile);

$showOld = '<div class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex items-start gap-4 mb-6">
                                <ShieldCheckIcon class="w-6 h-6 text-jubis-navy flex-shrink-0 mt-0.5" />
                                <div>
                                    <h3 class="font-bold text-jubis-navy text-sm">Wholesale Pricing Available</h3>
                                    <p class="text-sm text-gray-600 mt-1">Please <Link href="/login" class="text-jubis-red font-bold hover:underline">sign in to your B2B account</Link> to view exclusive corporate pricing and request a formal quotation.</p>
                                </div>
                            </div>';

$showNew = '<div v-if="!$page.props.auth.user" class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex items-start gap-4 mb-6">
                                <ShieldCheckIcon class="w-6 h-6 text-jubis-navy flex-shrink-0 mt-0.5" />
                                <div>
                                    <h3 class="font-bold text-jubis-navy text-sm">Wholesale Pricing Available</h3>
                                    <p class="text-sm text-gray-600 mt-1">Please <Link href="/login" class="text-jubis-red font-bold hover:underline">sign in to your B2B account</Link> to view exclusive corporate pricing and request a formal quotation.</p>
                                </div>
                            </div>
                            <div v-else class="mb-6">
                                <p class="text-3xl font-extrabold text-gray-900">₱{{ Number(product.wholesale_price).toLocaleString(\'en-PH\', {minimumFractionDigits: 2}) }} <span class="text-sm text-gray-500 font-normal">/ {{ product.unit_of_measure }}</span></p>
                                <p class="text-sm text-green-600 font-bold mt-1">Baseline Corporate Price</p>
                            </div>';

$showContent = str_replace($showOld, $showNew, $showContent);
file_put_contents($showFile, $showContent);

echo "Done\n";
