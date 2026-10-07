<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { EyeIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    quotes: Object,
    filters: Object,
});

const statusFilter = ref(props.filters.status || '');

watch(statusFilter, (value) => {
    router.get(route('admin.quotes.index'), { status: value }, {
        preserveState: true,
        replace: true,
    });
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
    <AdminLayout>
        <Head title="Quotation Manager | ERP" />
        
        <template #header>Quotation Manager</template>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-semibold text-gray-700">Filter by Status:</span>
                    <select v-model="statusFilter" class="rounded-lg border-gray-300 shadow-sm focus:ring-jubis-navy focus:border-jubis-navy text-sm font-medium">
                        <option value="">All Quotes</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Quote Ref</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Client / Company</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date Submitted</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="quotes.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-medium">
                                No quotes found matching the current criteria.
                            </td>
                        </tr>
                        <tr v-for="quote in quotes.data" :key="quote.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-jubis-navy">
                                #QTE-{{ String(quote.id).padStart(5, '0') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ quote.user.company_name }}</div>
                                <div class="text-xs text-gray-500">{{ quote.user.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(quote.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ quote.items.length }} items
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[getStatusColor(quote.status), 'px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full border uppercase tracking-wider']">
                                    {{ quote.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route('admin.quotes.show', quote.id)" class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors bg-blue-50 px-3 py-1.5 rounded-md">
                                    <EyeIcon class="w-4 h-4 mr-1.5 stroke-2" />
                                    Review
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-wrap gap-3 items-center justify-between" v-if="quotes.data.length > 0">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-900">{{ quotes.from }}</span> to <span class="font-medium text-gray-900">{{ quotes.to }}</span> of <span class="font-medium text-gray-900">{{ quotes.total }}</span> results
                </div>
                <div class="flex flex-wrap gap-1" v-if="quotes.links && quotes.links.length > 3">
                    <template v-for="(link, index) in quotes.links" :key="index">
                        <Link v-if="link.url" :href="link.url" class="px-3 py-1 border rounded text-sm font-medium transition-colors" :class="link.active ? 'bg-jubis-navy text-white border-jubis-navy' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300'" v-html="link.label" />
                        <span v-else class="px-3 py-1 border rounded text-sm font-medium bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
