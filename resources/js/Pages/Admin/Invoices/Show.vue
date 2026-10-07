<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, CurrencyDollarIcon, TruckIcon, CheckCircleIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    invoice: Object,
});

const canManageShipments = ['super_admin', 'admin', 'warehouse', 'purchasing'].includes(usePage().props.auth.user.role);
const paymentAmount = ref('');

const paymentForm = useForm({
    amount_paid: ''
});

const submitPayment = () => {
    paymentForm.amount_paid = paymentAmount.value;
    paymentForm.post(route('admin.invoices.payment', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            paymentAmount.value = '';
        }
    });
};

const shipmentForm = useForm({
    invoice_id: props.invoice.id,
    carrier: '',
    tracking_number: '',
    shipping_address: props.invoice.quote?.shipping_address || props.invoice.user?.shipping_address || '',
    delivery_notes: ''
});

const createShipment = () => {
    shipmentForm.post(route('admin.shipments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            shipmentForm.reset('carrier', 'tracking_number', 'delivery_notes');
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`INV-${String(invoice.id).padStart(5, '0')} | Admin`" />

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('admin.invoices.index')" class="mr-4 text-gray-500 hover:text-gray-700">
                    <ArrowLeftIcon class="h-6 w-6" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Invoice #INV-{{ String(invoice.quote_id).padStart(5, '0') }}
                    </h1>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Details & Items -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Billed To</h2>
                            <p class="font-medium mt-1">{{ invoice.user?.company_name || invoice.user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ invoice.user?.email }}</p>
                            <p class="text-sm text-gray-500 mt-2 whitespace-pre-line">{{ invoice.billing_address || 'No billing address provided.' }}</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-lg font-bold text-gray-900">Original Quote</h2>
                            <p class="text-sm text-gray-500 mt-1">#QTE-{{ String(invoice.quote_id).padStart(5, '0') }}</p>
                            <p class="text-sm text-gray-500 mt-2 font-bold">Due Date:</p>
                            <p class="text-sm text-gray-500">{{ new Date(invoice.due_date).toLocaleDateString() }}</p>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 mt-4 border-t">
                        <thead class="bg-gray-50 text-sm">
                            <tr>
                                <th class="py-3 text-left font-semibold text-gray-900">Description</th>
                                <th class="py-3 text-right font-semibold text-gray-900">Unit Price</th>
                                <th class="py-3 text-right font-semibold text-gray-900">Qty</th>
                                <th class="py-3 text-right font-semibold text-gray-900">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="item in invoice.quote?.items" :key="item.id">
                                <td class="py-3 text-sm text-gray-900">{{ item.product?.name }} <span class="text-xs text-gray-500 ml-2">{{ item.product?.sku }}</span></td>
                                <td class="py-3 text-sm text-gray-900 text-right">₱{{ Number(item.quoted_price).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                                <td class="py-3 text-sm text-gray-900 text-right font-medium">{{ item.quantity }}</td>
                                <td class="py-3 text-sm text-gray-900 text-right font-bold">₱{{ (item.quantity * item.quoted_price).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="py-3 text-right font-medium text-gray-700 border-t">Subtotal (VAT-Exclusive):</td>
                                <td class="py-3 text-right font-medium text-gray-900 border-t">₱{{ Number(invoice.subtotal || (invoice.total_amount/1.12)).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-3 text-right font-medium text-gray-700 border-t">12% VAT:</td>
                                <td class="py-3 text-right font-medium text-gray-900 border-t">₱{{ Number(invoice.vat_amount || (invoice.total_amount - (invoice.total_amount/1.12))).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-3 text-right font-bold text-gray-900 border-t">Total Invoice Amount:</td>
                                <td class="py-3 text-right font-bold text-xl text-jubis-navy border-t">₱{{ Number(invoice.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Notes & Remarks -->
                <div v-if="invoice.quote && (invoice.quote.client_notes || invoice.quote.admin_notes)" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2" /> Notes & Remarks
                    </h2>
                    
                    <div class="space-y-4">
                        <div v-if="invoice.quote.client_notes" class="bg-blue-50 p-4 rounded-md border border-blue-100">
                            <h3 class="text-sm font-bold text-blue-800 uppercase tracking-wider mb-2">Client Request / Remarks</h3>
                            <p class="text-gray-700 whitespace-pre-line text-sm">{{ invoice.quote.client_notes }}</p>
                        </div>
                        
                        <div v-if="invoice.quote.admin_notes" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Internal Admin Notes</h3>
                            <p class="text-gray-700 whitespace-pre-line text-sm">{{ invoice.quote.admin_notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Shipments List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center"><TruckIcon class="w-5 h-5 mr-2"/> Associated Shipments</h2>
                    <div v-if="canManageShipments && invoice.shipments.length === 0" class="text-sm text-gray-500 py-4">No shipments have been created for this invoice yet.</div>
                    <div v-else class="space-y-4">
                        <div v-for="shipment in invoice.shipments" :key="shipment.id" class="border rounded-md p-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold flex items-center">Tracking: <a v-if="shipment.tracking_url" :href="shipment.tracking_url" target="_blank" class="ml-2 text-blue-600 hover:underline flex items-center">{{ shipment.tracking_number }} <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-1"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a><span v-else class="ml-2">{{ shipment.tracking_number || 'N/A' }}</span> <span class="ml-2 text-gray-500 text-sm">({{ shipment.carrier }})</span></p>
                                <p class="text-sm text-gray-500 mt-1">Status: <span class="font-bold uppercase">{{ shipment.status }}</span></p>
                            </div>
                            <Link v-if="canManageShipments" :href="route('admin.shipments.index')" class="text-blue-600 text-sm font-bold hover:underline">Update Status</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions -->
            <div class="space-y-6">
                <!-- Payment Logging -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Payment Status</h2>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Paid:</span>
                            <span class="font-bold text-green-600">₱{{ Number(invoice.amount_paid).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Balance Due:</span>
                            <span class="font-bold text-red-600">₱{{ Number(invoice.total_amount - invoice.amount_paid).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</span>
                        </div>
                    </div>

                    <form @submit.prevent="submitPayment" class="mt-4 border-t pt-4" v-if="invoice.amount_paid < invoice.total_amount">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Log New Payment</label>
                        <div class="flex gap-2">
                            <div class="relative rounded-md shadow-sm flex-grow">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₱</span>
                                </div>
                                <input type="number" step="0.01" v-model="paymentAmount" required class="block w-full pl-7 rounded-md border-gray-300 focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm" placeholder="0.00">
                            </div>
                            <button type="submit" :disabled="paymentForm.processing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Log
                            </button>
                        </div>
                    </form>
                    <div v-else class="mt-4 border-t pt-4 text-center text-green-600 font-bold flex items-center justify-center">
                        <CheckCircleIcon class="w-5 h-5 mr-1" /> Fully Paid
                    </div>
                </div>

                <!-- Create Shipment -->
                <div v-if="invoice.shipments.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Create Dispatch / Shipment</h2>
                    <form @submit.prevent="createShipment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Carrier / Logistics Provider</label>
                            <input type="text" v-model="shipmentForm.carrier" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="e.g. LBC, In-house Fleet">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tracking Number (Optional)</label>
                            <input type="text" v-model="shipmentForm.tracking_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Shipping Address</label>
                            <textarea v-model="shipmentForm.shipping_address" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></textarea>
                        </div>
                        <button type="submit" :disabled="shipmentForm.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-jubis-navy hover:bg-[#071126]">
                            Generate Shipment Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>





