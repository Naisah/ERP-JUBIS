<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, CheckCircleIcon, TruckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    purchaseOrder: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'draft': return 'bg-gray-100 text-gray-800';
        case 'ordered': return 'bg-blue-100 text-blue-800';
        case 'partially_received': return 'bg-yellow-100 text-yellow-800';
        case 'received': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const updateStatus = (newStatus) => {
    if (newStatus === 'received') {
        if (!confirm('Marking this PO as Received will automatically add all items to your main Inventory stock quantity. This cannot be undone automatically. Proceed?')) {
            return;
        }
    }
    router.post(route('admin.purchase-orders.status', props.purchaseOrder.id), { status: newStatus });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`PO-${String(purchaseOrder.id).padStart(5, '0')} | Admin`" />

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('admin.purchase-orders.index')" class="mr-4 text-gray-500 hover:text-gray-700">
                    <ArrowLeftIcon class="h-6 w-6" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        PO-{{ String(purchaseOrder.id).padStart(5, '0') }}
                        <span :class="[getStatusColor(purchaseOrder.status), 'px-3 py-1 rounded-full text-xs uppercase tracking-wider font-bold border']">
                            {{ purchaseOrder.status.replace('_', ' ') }}
                        </span>
                    </h1>
                </div>
            </div>
            <div class="flex gap-2" v-if="purchaseOrder.status !== 'received'">
                <button v-if="purchaseOrder.status === 'draft'" @click="updateStatus('ordered')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <TruckIcon class="w-4 h-4 mr-2" /> Mark as Ordered
                </button>
                <button @click="updateStatus('received')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <CheckCircleIcon class="w-4 h-4 mr-2" /> Receive Items (Update Inventory)
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Supplier</h3>
                <p class="font-bold text-lg text-gray-900">{{ purchaseOrder.supplier?.name }}</p>
                <p class="text-sm text-gray-600">{{ purchaseOrder.supplier?.email || 'No email' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Delivery Details</h3>
                <p class="font-medium text-gray-900">Expected: {{ purchaseOrder.expected_delivery_date || 'Not specified' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ purchaseOrder.notes || 'No notes' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Total PO Value</h3>
                <p class="font-extrabold text-3xl text-jubis-navy">₱{{ Number(purchaseOrder.total_cost).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-300 rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Line Items</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 text-sm">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900">Product</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">SKU</th>
                        <th class="px-3 py-3.5 text-right font-semibold text-gray-900">Unit Cost</th>
                        <th class="px-3 py-3.5 text-right font-semibold text-gray-900">Qty Ordered</th>
                        <th class="px-3 py-3.5 text-right font-semibold text-gray-900">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="item in purchaseOrder.items" :key="item.id">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900">{{ item.product?.name }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-500 font-mono">{{ item.product?.sku }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 text-right">₱{{ Number(item.unit_cost).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 text-right font-bold">{{ item.quantity }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 text-right font-medium">₱{{ (item.quantity * item.unit_cost).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
