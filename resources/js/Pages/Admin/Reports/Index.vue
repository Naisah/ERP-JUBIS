<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import {
    ArrowTrendingUpIcon,
    BanknotesIcon,
    CubeIcon,
    ShoppingCartIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    metrics: Object,
    revenueChart: Array,
    procurementChart: Array,
    topProducts: Array,
    lowStockProducts: Array,
    inventoryByBrand: Array,
    recentMovements: Array,
    recentSales: Array,
    quoteFunnel: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatCurrencyFull = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(amount);
};

// Revenue chart bar heights
const maxRevenue = computed(() => Math.max(...props.revenueChart.map(r => r.revenue), 1));
const maxSpend = computed(() => Math.max(...props.procurementChart.map(r => r.spend), 1));

// Inventory by brand max
const maxBrandValue = computed(() => Math.max(...(props.inventoryByBrand || []).map(b => b.total_value), 1));

const brandColors = ['bg-blue-500', 'bg-green-500', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-teal-500', 'bg-pink-500', 'bg-indigo-500'];

const getMovementBadge = (type) => {
    switch(type) {
        case 'sale': return { label: 'Sale', classes: 'bg-red-100 text-red-700' };
        case 'purchase': return { label: 'Purchase', classes: 'bg-green-100 text-green-700' };
        case 'adjustment': return { label: 'Adjustment', classes: 'bg-amber-100 text-amber-700' };
        case 'return': return { label: 'Return', classes: 'bg-blue-100 text-blue-700' };
        default: return { label: type, classes: 'bg-gray-100 text-gray-700' };
    }
};

const funnelConversion = computed(() => {
    if (!props.quoteFunnel || props.quoteFunnel.submitted === 0) return 0;
    return ((props.quoteFunnel.paid / props.quoteFunnel.submitted) * 100).toFixed(1);
});
</script>

<template>
    <AdminLayout>
        <Head title="Reports & Analytics | Admin" />

        <template #header>Reports & Analytics</template>

        <!-- Key Metrics Cards - Row 1 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Revenue</p>
                        <h3 class="text-2xl font-extrabold text-green-700 mt-1">{{ formatCurrency(metrics.totalRevenue) }}</h3>
                    </div>
                    <div class="p-3 rounded-full bg-green-100">
                        <ArrowTrendingUpIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending Receivables</p>
                        <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ formatCurrency(metrics.pendingReceivables) }}</h3>
                    </div>
                    <div class="p-3 rounded-full bg-amber-100">
                        <BanknotesIcon class="w-6 h-6 text-amber-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventory Value</p>
                        <h3 class="text-2xl font-extrabold text-blue-700 mt-1">{{ formatCurrency(metrics.inventoryValue) }}</h3>
                    </div>
                    <div class="p-3 rounded-full bg-blue-100">
                        <CubeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Procurement Spend</p>
                        <h3 class="text-2xl font-extrabold text-purple-700 mt-1">{{ formatCurrency(metrics.totalProcurement) }}</h3>
                    </div>
                    <div class="p-3 rounded-full bg-purple-100">
                        <ShoppingCartIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards - Row 2 -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase">Products</p>
                <p class="text-xl font-extrabold text-jubis-navy mt-1">{{ metrics.totalProducts }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase">Suppliers</p>
                <p class="text-xl font-extrabold text-jubis-navy mt-1">{{ metrics.totalSuppliers }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase">Quote Requests</p>
                <p class="text-xl font-extrabold text-jubis-navy mt-1">{{ metrics.totalQuotes }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase">Invoices</p>
                <p class="text-xl font-extrabold text-jubis-navy mt-1">{{ metrics.totalInvoices }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center cursor-pointer hover:ring-2 hover:ring-red-300 transition">
                <p class="text-xs font-semibold text-gray-500 uppercase">Out of Stock</p>
                <p class="text-xl font-extrabold text-red-600 mt-1">{{ metrics.outOfStockCount }}</p>
            </div>
        </div>

        <!-- Revenue & Procurement Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Trend -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Revenue Trend (6 Months)</h3>
                <div class="flex items-end justify-between gap-2 h-48">
                    <div v-for="(item, idx) in revenueChart" :key="idx" class="flex-1 flex flex-col items-center justify-end h-full">
                        <div class="text-[10px] font-bold text-green-700 mb-1" v-if="item.revenue > 0">{{ formatCurrency(item.revenue) }}</div>
                        <div class="w-full bg-green-500 rounded-t-md transition-all duration-500 min-h-[4px]" 
                            :style="{ height: (item.revenue / maxRevenue * 100) + '%' }">
                        </div>
                        <div class="text-[10px] text-gray-500 mt-2 text-center leading-tight">{{ item.month }}</div>
                        <div class="text-[9px] text-gray-400">{{ item.count }} inv</div>
                    </div>
                </div>
            </div>

            <!-- Procurement Trend -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Procurement Spend (6 Months)</h3>
                <div class="flex items-end justify-between gap-2 h-48">
                    <div v-for="(item, idx) in procurementChart" :key="idx" class="flex-1 flex flex-col items-center justify-end h-full">
                        <div class="text-[10px] font-bold text-purple-700 mb-1" v-if="item.spend > 0">{{ formatCurrency(item.spend) }}</div>
                        <div class="w-full bg-purple-500 rounded-t-md transition-all duration-500 min-h-[4px]"
                            :style="{ height: (item.spend / maxSpend * 100) + '%' }">
                        </div>
                        <div class="text-[10px] text-gray-500 mt-2 text-center leading-tight">{{ item.month }}</div>
                        <div class="text-[9px] text-gray-400">{{ item.count }} POs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quote-to-Cash Funnel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">Quote-to-Cash Conversion Funnel</h3>
                <span class="text-sm font-bold" :class="funnelConversion > 0 ? 'text-green-600' : 'text-gray-400'">{{ funnelConversion }}% conversion rate</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="flex-1 text-center">
                    <div class="bg-blue-500 text-white rounded-lg py-3 px-2 font-bold text-lg">{{ quoteFunnel.submitted }}</div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase mt-1.5">Submitted</p>
                </div>
                <div class="text-gray-300 text-xl font-light">→</div>
                <div class="flex-1 text-center">
                    <div class="bg-indigo-500 text-white rounded-lg py-3 px-2 font-bold text-lg">{{ quoteFunnel.reviewed }}</div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase mt-1.5">Reviewed</p>
                </div>
                <div class="text-gray-300 text-xl font-light">→</div>
                <div class="flex-1 text-center">
                    <div class="bg-purple-500 text-white rounded-lg py-3 px-2 font-bold text-lg">{{ quoteFunnel.approved }}</div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase mt-1.5">Approved</p>
                </div>
                <div class="text-gray-300 text-xl font-light">→</div>
                <div class="flex-1 text-center">
                    <div class="bg-amber-500 text-white rounded-lg py-3 px-2 font-bold text-lg">{{ quoteFunnel.invoiced }}</div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase mt-1.5">Invoiced</p>
                </div>
                <div class="text-gray-300 text-xl font-light">→</div>
                <div class="flex-1 text-center">
                    <div class="bg-green-500 text-white rounded-lg py-3 px-2 font-bold text-lg">{{ quoteFunnel.paid }}</div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase mt-1.5">Paid</p>
                </div>
            </div>
        </div>

        <!-- Middle Row: Top Products + Inventory by Brand -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-base font-bold text-gray-900">Top Selling Products</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">#</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Product</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 text-xs uppercase">Sold</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 text-xs uppercase">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(product, idx) in topProducts" :key="product.sku" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-400 font-bold">{{ idx + 1 }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900 truncate max-w-[200px]">{{ product.name }}</div>
                                    <div class="text-xs text-gray-400">{{ product.sku }} · {{ product.brand }}</div>
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900">{{ Number(product.total_sold).toLocaleString() }}</td>
                                <td class="px-6 py-3 text-right font-bold text-green-700">{{ formatCurrencyFull(product.total_revenue) }}</td>
                            </tr>
                            <tr v-if="topProducts.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">No sales data yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory by Brand -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Inventory Value by Brand</h3>
                <div class="space-y-3">
                    <div v-for="(brand, idx) in inventoryByBrand" :key="brand.brand" class="flex items-center gap-3">
                        <div class="w-24 text-sm font-medium text-gray-700 truncate flex-shrink-0">{{ brand.brand }}</div>
                        <div class="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                            <div :class="[brandColors[idx % brandColors.length], 'h-full rounded-full transition-all duration-700']"
                                :style="{ width: (brand.total_value / maxBrandValue * 100) + '%' }">
                            </div>
                            <span class="absolute inset-y-0 right-2 flex items-center text-[10px] font-bold text-gray-600">
                                {{ formatCurrency(brand.total_value) }}
                            </span>
                        </div>
                        <div class="w-16 text-right text-xs text-gray-500 flex-shrink-0">{{ Number(brand.total_units).toLocaleString() }} pcs</div>
                    </div>
                    <div v-if="!inventoryByBrand || inventoryByBrand.length === 0" class="text-center text-gray-400 py-8">No inventory data.</div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Low Stock Alerts + Stock Movements -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-amber-50 flex items-center gap-2">
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-600" />
                    <h3 class="text-base font-bold text-amber-900">Low Stock Alerts</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Product</th>
                                <th class="px-6 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Current</th>
                                <th class="px-6 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Reorder At</th>
                                <th class="px-6 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Urgency</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="product in lowStockProducts" :key="product.id" class="hover:bg-amber-50/50">
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900 truncate max-w-[200px]">{{ product.name }}</div>
                                    <div class="text-xs text-gray-400">{{ product.sku }} · {{ product.brand }}</div>
                                </td>
                                <td class="px-6 py-3 text-center font-bold text-red-600">{{ product.stock_quantity }}</td>
                                <td class="px-6 py-3 text-center text-gray-500">{{ product.reorder_level }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span v-if="product.stock_quantity <= 3" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 uppercase">Critical</span>
                                    <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Low</span>
                                </td>
                            </tr>
                            <tr v-if="lowStockProducts.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-green-600 font-medium">✓ All products are above reorder levels.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stock Movement Audit Trail -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <ArrowPathIcon class="w-5 h-5 text-gray-600" />
                    <h3 class="text-base font-bold text-gray-900">Stock Movement Ledger</h3>
                </div>
                <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Product</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Type</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs uppercase">Qty</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Ref</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs uppercase">By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="mv in recentMovements" :key="mv.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    <div class="font-medium text-gray-900 truncate max-w-[140px]">{{ mv.product?.name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ mv.product?.sku }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span :class="[getMovementBadge(mv.type).classes, 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase']">
                                        {{ getMovementBadge(mv.type).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center font-bold" :class="mv.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ mv.quantity > 0 ? '+' : '' }}{{ mv.quantity }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 font-mono">{{ mv.reference_id || '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ mv.user?.name || 'System' }}</td>
                            </tr>
                            <tr v-if="recentMovements.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">No stock movements recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Paid Invoices -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-900">Recent Paid Invoices</h3>
                <Link :href="route('admin.invoices.index')" class="text-sm font-medium text-jubis-navy hover:text-blue-800">View All →</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Invoice #</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Client</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Date</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 text-xs uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="invoice in recentSales" :key="invoice.id" class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-bold text-jubis-navy">INV-{{ String(invoice.id).padStart(5, '0') }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ invoice.user?.company_name || invoice.user?.name }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ new Date(invoice.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) }}</td>
                            <td class="px-6 py-3 text-right font-bold text-green-700">{{ formatCurrencyFull(invoice.total_amount) }}</td>
                        </tr>
                        <tr v-if="recentSales.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No paid invoices found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
