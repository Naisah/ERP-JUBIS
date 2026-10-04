<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    purchaseOrders: Object,
    filters: Object,
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
</script>

<template>
    <AdminLayout>
        <Head title="Purchase Orders | Admin" />

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
                <p class="text-sm text-gray-500 mt-1">Manage vendor POs and automate inventory receiving.</p>
            </div>
            <Link :href="route('admin.purchase-orders.create')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-jubis-navy hover:bg-[#071126]">
                <PlusIcon class="h-5 w-5 mr-2" />
                Create PO
            </Link>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-300 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900">PO Number</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Supplier</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Total Cost</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Expected Delivery</th>
                        <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="po in purchaseOrders.data" :key="po.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 font-bold text-gray-900">
                            PO-{{ String(po.id).padStart(5, '0') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 font-medium">{{ po.supplier?.name }}</td>
                        <td class="whitespace-nowrap px-3 py-4">
                            <span :class="[getStatusColor(po.status), 'px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase tracking-wider']">
                                {{ po.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 font-bold">₱{{ Number(po.total_cost).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-500">{{ po.expected_delivery_date || 'Not Set' }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium">
                            <Link :href="route('admin.purchase-orders.show', po.id)" class="text-blue-600 hover:text-blue-900 font-bold">Review / Receive &rarr;</Link>
                        </td>
                    </tr>
                    <tr v-if="purchaseOrders.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-gray-500">No purchase orders found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
