<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ShoppingCartIcon, ClockIcon, CheckCircleIcon, DocumentTextIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    recentQuotes: Array
});

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'reviewed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'approved': return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};
</script>

<template>
    <PublicLayout>
        <Head title="Client Dashboard | Jubis Marketing" />

        <div class="bg-gray-50 min-h-[75vh] py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-extrabold text-jubis-navy">Welcome back, {{ $page.props.auth.user.name }}</h1>
                        <p class="text-gray-500 mt-1 flex items-center">
                            <BuildingOfficeIcon class="w-4 h-4 mr-1.5" />
                            {{ $page.props.auth.user.company_name }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center">
                        <span class="text-sm text-gray-500 mr-3">B2B Account Status:</span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 border border-green-200 rounded-full text-xs font-bold uppercase tracking-wider">
                            {{ $page.props.auth.user.credit_status || 'Active' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Quick Actions -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-jubis-navy mb-4 border-b border-gray-100 pb-3">Quick Links</h2>
                            <ul class="space-y-3">
                                <li>
                                    <Link :href="route('products.index')" class="flex items-center text-gray-600 hover:text-jubis-red transition-colors group">
                                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center mr-3 group-hover:bg-red-100 transition-colors">
                                            <ShoppingCartIcon class="w-4 h-4 text-jubis-red" />
                                        </div>
                                        <span class="font-medium">Browse Product Catalog</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('cart.index')" class="flex items-center text-gray-600 hover:text-jubis-navy transition-colors group">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3 group-hover:bg-blue-100 transition-colors">
                                            <DocumentTextIcon class="w-4 h-4 text-jubis-navy" />
                                        </div>
                                        <span class="font-medium">View Active Draft Cart</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Quotes -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-jubis-navy mb-4 border-b border-gray-100 pb-3 flex justify-between items-center">
                                Recent Quotation Requests
                            </h2>

                            <div v-if="recentQuotes.length === 0" class="text-center py-12">
                                <DocumentTextIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <h3 class="text-gray-900 font-bold mb-1">No requests yet</h3>
                                <p class="text-sm text-gray-500 mb-4">You haven't submitted any formal quote requests.</p>
                                <Link :href="route('products.index')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md shadow-sm text-white bg-jubis-navy hover:bg-[#071126] transition-colors">
                                    Start a New Request
                                </Link>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="quote in recentQuotes" :key="quote.id" class="border border-gray-100 rounded-xl p-5 hover:bg-gray-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="font-bold text-jubis-navy">#QTE-{{ String(quote.id).padStart(5, '0') }}</span>
                                            <span :class="[getStatusColor(quote.status), 'px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full border uppercase tracking-wider']">
                                                {{ quote.status }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500 flex items-center gap-4">
                                            <span class="flex items-center"><ClockIcon class="w-4 h-4 mr-1"/> {{ new Date(quote.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
                                            <span>{{ quote.items.length }} items requested</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <div class="text-xs text-gray-500 font-medium uppercase mb-0.5">Estimated Total</div>
                                        <div class="text-lg font-bold text-gray-900">₱{{ Number(quote.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</div>
                                        <a v-if="quote.invoice && quote.invoice.status !== 'paid' && quote.invoice.payment_url" :href="quote.invoice.payment_url" class="mt-2 text-xs font-bold bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-full transition shadow-sm">
                                            Pay via GCash/Card
                                        </a>
                                        <a v-if="quote.invoice" :href="'/invoices/' + quote.invoice.id + '/pdf'" target="_blank" class="mt-2 text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-full transition shadow-sm flex items-center justify-center">
                                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                              </svg>
                                              Download PDF Invoice
                                          </a>
                                          <span v-else-if="quote.invoice && quote.invoice.status === 'paid'" class="mt-2 text-xs font-bold text-green-600 flex items-center">
                                            <CheckCircleIcon class="w-4 h-4 mr-1" /> Payment Complete
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </PublicLayout>
</template>


