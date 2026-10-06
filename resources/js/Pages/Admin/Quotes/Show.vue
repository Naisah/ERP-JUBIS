<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, CheckCircleIcon, XCircleIcon, ClockIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    quote: Object
});

const form = useForm({
    status: props.quote.status,
});

const updateStatus = (newStatus) => {
    form.status = newStatus;
    form.put(route('admin.quotes.update', props.quote.id));
};

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
    <AdminLayout>
        <Head :title="`Quote #QTE-${String(quote.id).padStart(5, '0')} | ERP`" />
        
        <template #header>Review Quotation</template>

        <div class="mb-6 flex items-center justify-between">
            <Link :href="route('admin.quotes.index')" class="flex items-center text-sm font-medium text-gray-500 hover:text-jubis-navy transition-colors">
                <ArrowLeftIcon class="w-4 h-4 mr-1" /> Back to Quotes
            </Link>
            
            <div class="flex space-x-3">
                <button @click="updateStatus('rejected')" v-if="quote.status !== 'rejected'" class="inline-flex items-center px-4 py-2 border border-red-200 rounded-lg shadow-sm text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none transition-colors">
                    <XCircleIcon class="h-5 w-5 mr-1.5" /> Reject Quote
                </button>
                <button @click="updateStatus('approved')" v-if="quote.status !== 'approved'" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none transition-colors">
                    <CheckCircleIcon class="h-5 w-5 mr-1.5" /> Approve & Convert to Invoice
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Items Table -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6">
                    <h2 class="text-lg font-bold text-jubis-navy border-b border-gray-100 pb-3 mb-4">Requested Items</h2>
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Product</th>
                                <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Unit Price</th>
                                <th class="text-center text-xs font-bold text-gray-500 uppercase pb-3">Qty</th>
                                <th class="text-right text-xs font-bold text-gray-500 uppercase pb-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in quote.items" :key="item.id">
                                <td class="py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ item.product.name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ item.product.sku }}</div>
                                </td>
                                <td class="py-4 text-sm text-gray-600">
                                    ₱{{ Number(item.quoted_price).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                                <td class="py-4 text-sm font-bold text-gray-900 text-center">
                                    {{ item.quantity }}
                                </td>
                                <td class="py-4 text-sm font-bold text-jubis-navy text-right">
                                    ₱{{ Number(item.quoted_price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="pt-4 text-right text-sm font-bold text-gray-500">Subtotal (VAT-Exclusive):</td>
                                <td class="pt-4 text-right text-md font-bold text-gray-700">
                                    ₱{{ Number(quote.subtotal || (quote.total_amount/1.12)).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pt-2 text-right text-sm font-bold text-gray-500">12% VAT:</td>
                                <td class="pt-2 text-right text-md font-bold text-gray-700">
                                    ₱{{ Number(quote.vat_amount || (quote.total_amount - (quote.total_amount/1.12))).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pt-2 text-right text-sm font-bold text-gray-700">Total Requested Value:</td>
                                <td class="pt-2 text-right text-lg font-extrabold text-jubis-red">
                                    ₱{{ Number(quote.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Notes & Remarks -->
                <div v-if="quote.client_notes || quote.admin_notes" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2" /> Notes & Remarks
                    </h2>
                    
                    <div class="space-y-4">
                        <div v-if="quote.client_notes" class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <h3 class="text-sm font-bold text-blue-800 uppercase tracking-wider mb-2">Client Request / Remarks</h3>
                            <p class="text-gray-700 whitespace-pre-line text-sm">{{ quote.client_notes }}</p>
                        </div>
                        
                        <div v-if="quote.admin_notes" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Internal Admin Notes</h3>
                            <p class="text-gray-700 whitespace-pre-line text-sm">{{ quote.admin_notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Details -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Quote Status</h2>
                    <div class="flex items-center">
                        <span :class="[getStatusColor(quote.status), 'px-3 py-1.5 inline-flex text-sm leading-5 font-bold rounded-full border uppercase tracking-wider']">
                            {{ quote.status }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-4 flex items-center">
                        <ClockIcon class="w-4 h-4 mr-1" />
                        Submitted: {{ new Date(quote.created_at).toLocaleString('en-US') }}
                    </p>
                </div>

                <!-- Client Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Client Details</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Company</p>
                            <p class="text-sm font-bold text-jubis-navy">{{ quote.user.company_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Contact Person</p>
                            <p class="text-sm font-medium text-gray-900">{{ quote.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Email Address</p>
                            <a :href="'mailto:' + quote.user.email" class="text-sm font-medium text-blue-600 hover:underline">{{ quote.user.email }}</a>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Credit Status</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 border border-green-200 mt-1">
                                {{ quote.user.credit_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

