<script setup>
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    invoices: Object,
    filters: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'unpaid': return 'bg-gray-100 text-gray-800';
        case 'partially_paid': return 'bg-yellow-100 text-yellow-800';
        case 'paid': return 'bg-green-100 text-green-800';
        case 'on_terms': return 'bg-purple-100 text-purple-800';
        case 'overdue': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Invoices | Admin" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Invoicing & Finance</h1>
            <p class="text-sm text-gray-500 mt-1">Manage client billing, payments, and account receivables.</p>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-300 rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900">Invoice Number</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Client / Company</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-3 py-3.5 text-right font-semibold text-gray-900">Total Billed</th>
                        <th class="px-3 py-3.5 text-right font-semibold text-gray-900">Amount Paid</th>
                        <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 font-bold text-gray-900">
                            INV-{{ String(invoice.quote_id).padStart(5, '0') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900">
                            <div class="font-medium">{{ invoice.user?.company_name || invoice.user?.name }}</div>
                            <div class="text-xs text-gray-500">{{ invoice.user?.email }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            <span :class="[getStatusColor(invoice.status), 'px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase tracking-wider border border-opacity-20 border-current']">
                                {{ invoice.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 font-bold text-right">₱{{ Number(invoice.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-900 font-bold text-right">₱{{ Number(invoice.amount_paid).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium">
                            <Link :href="route('admin.invoices.show', invoice.id)" class="text-jubis-navy hover:text-blue-900 font-bold">Manage &rarr;</Link>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="6" class="py-12 text-center text-gray-500">No invoices generated yet. (Approve a Quote to generate an Invoice).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <Pagination :links="invoices.links" />
    </AdminLayout>
</template>
