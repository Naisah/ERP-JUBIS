<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ChartBarIcon, CurrencyDollarIcon, DocumentTextIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    metrics: Object,
    chartData: Array,
    topProducts: Array,
    recentQuotes: Array,
    recentInvoices: Array,
    reorderList: Array,
});

const role = usePage().props.auth.user.role;
const allowed = (roles) => ['admin', 'super_admin', ...roles].includes(role);

// Bar Chart Configuration
const barChartData = computed(() => ({
    labels: props.chartData.map(d => d.month),
    datasets: [{
        label: 'Monthly Revenue (PHP)',
        data: props.chartData.map(d => d.total),
        backgroundColor: '#1e3a8a', // jubis-navy
        borderRadius: 4,
    }]
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { beginAtZero: true }
    }
};

// Doughnut Chart Configuration
const doughnutChartData = computed(() => {
    const labels = props.topProducts ? props.topProducts.map(p => p.name) : [];
    const data = props.topProducts ? props.topProducts.map(p => p.total_sold) : [];
    return {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: ['#1e3a8a', '#3b82f6', '#93c5fd', '#f59e0b', '#10b981'],
            borderWidth: 1
        }]
    };
});

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            Dashboard
        </template>

        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <CurrencyDollarIcon class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">₱{{ Number(metrics.totalRevenue).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <ChartBarIcon class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Outstanding Receivables</p>
                    <p class="text-2xl font-bold text-gray-900">₱{{ Number(metrics.outstandingReceivables).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <DocumentTextIcon class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Quotes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ metrics.pendingQuotes }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                    <ExclamationTriangleIcon class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Items Low on Stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ metrics.lowStockCount }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Charts -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Advanced ChartJS Revenue Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Revenue Trend (Last 6 Months)</h2>
                    <div class="h-64">
                        <Bar :data="barChartData" :options="barChartOptions" />
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">Recent Invoices</h2>
                        <Link v-if="allowed(['finance'])" :href="route('admin.invoices.index')" class="text-sm text-blue-600 hover:underline">View All</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Invoice</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Client</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-900">Amount</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-900">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="invoice in recentInvoices" :key="invoice.id">
                                    <td class="px-6 py-4 font-medium text-gray-900">INV-{{ String(invoice.quote_id).padStart(5,'0') }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ invoice.user?.company_name || invoice.user?.name }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">₱{{ Number(invoice.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span :class="{'text-green-600': invoice.status === 'paid', 'text-yellow-600': invoice.status === 'partially_paid', 'text-gray-500': invoice.status === 'unpaid', 'text-purple-600': invoice.status === 'on_terms'}" class="font-bold uppercase text-xs tracking-wider">
                                            {{ invoice.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="recentInvoices.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No invoices found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Products & Lists -->
            <div class="space-y-8">
                
                <!-- ChartJS Top Selling Products -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Top Selling Products</h2>
                    <div class="h-64">
                        <Doughnut v-if="topProducts && topProducts.length > 0" :data="doughnutChartData" :options="doughnutChartOptions" />
                        <div v-else class="h-full flex items-center justify-center text-gray-500 text-sm">
                            Not enough sales data yet.
                        </div>
                    </div>
                </div>

                <!-- Pending Quotes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-900">Recent Quotes</h2>
                        <Link v-if="allowed(['sales', 'finance'])" :href="route('admin.quotes.index')" class="text-sm text-blue-600 hover:underline">View All</Link>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="quote in recentQuotes" :key="quote.id" class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-900">QTE-{{ String(quote.id).padStart(5,'0') }}</span>
                                <span class="text-sm font-medium" :class="quote.status === 'pending' ? 'text-yellow-600' : 'text-gray-500'">{{ quote.status.toUpperCase() }}</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ quote.user?.company_name || quote.user?.name }}</div>
                            <div class="mt-2 text-right">
                                <Link v-if="allowed(['sales', 'finance'])" :href="route('admin.quotes.show', quote.id)" class="text-xs text-blue-600 font-bold hover:underline">Review Quote &rarr;</Link>
                            </div>
                        </li>
                        <li v-if="recentQuotes.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">No quotes found.</li>
                    </ul>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b flex justify-between items-center bg-red-50">
                        <h2 class="text-lg font-bold text-red-900 flex items-center">
                            <ExclamationTriangleIcon class="w-5 h-5 mr-2" /> Action Required (Low Stock)
                        </h2>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="item in reorderList" :key="item.id" class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                            <div class="truncate pr-4">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ item.name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ item.sku }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ item.stock_quantity }} left
                                </span>
                            </div>
                        </li>
                        <li v-if="reorderList.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">Inventory levels are healthy.</li>
                    </ul>
                    <div class="bg-gray-50 px-6 py-3 border-t">
                        <Link v-if="allowed(['purchasing'])" :href="route('admin.purchase-orders.create')" class="text-sm font-medium text-jubis-navy hover:underline flex justify-center items-center">
                            Create Purchase Order &rarr;
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
