<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    shipments: Object,
    filters: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'processing': return 'bg-gray-100 text-gray-800';
        case 'picking_packing': return 'bg-purple-100 text-purple-800';
        case 'dispatched': return 'bg-blue-100 text-blue-800';
        case 'in_transit': return 'bg-yellow-100 text-yellow-800';
        case 'delivered': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const updateStatus = (id, newStatus) => {
    router.post(route('admin.shipments.status', id), { status: newStatus }, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Shipments | Admin" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Logistics & Dispatch</h1>
            <p class="text-sm text-gray-500 mt-1">Manage outbound shipments and update delivery statuses.</p>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-300 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900">Tracking / ID</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Invoice Ref</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Client / Destination</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Dates</th>
                        <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="shipment in shipments.data" :key="shipment.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-gray-900">
                            <div class="font-bold uppercase">{{ shipment.tracking_number || `SHP-${String(shipment.id).padStart(5,'0')}` }}</div>
                            <div class="text-xs text-gray-500">via {{ shipment.carrier }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900">
                            <div class="font-medium text-jubis-navy">INV-{{ String(shipment.invoice_id).padStart(5, '0') }}</div>
                        </td>
                        <td class="px-3 py-4 text-gray-900 max-w-xs truncate">
                            <div class="font-bold">{{ shipment.invoice?.user?.company_name || shipment.invoice?.user?.name }}</div>
                            <div class="text-xs text-gray-500 truncate mt-1" :title="shipment.shipping_address">{{ shipment.shipping_address }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            <span :class="[getStatusColor(shipment.status), 'px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase tracking-wider']">
                                {{ shipment.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-600">
                            <div v-if="shipment.shipped_at">Shipped: {{ new Date(shipment.shipped_at).toLocaleDateString() }}</div>
                            <div v-if="shipment.delivered_at" class="text-green-600 font-bold mt-1">Delivered: {{ new Date(shipment.delivered_at).toLocaleDateString() }}</div>
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium">
                            <select @change="updateStatus(shipment.id, $event.target.value)" :value="shipment.status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy text-xs py-1">
                                <option value="processing">Processing</option>
                                <option value="picking_packing">Picking & Packing</option>
                                <option value="dispatched">Dispatched</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </td>
                    </tr>
                    <tr v-if="shipments.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-gray-500">No shipments generated yet. (Go to an Invoice to create a shipment).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
