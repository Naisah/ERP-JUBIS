<script setup>
import ProductImage from '@/Components/ProductImage.vue';
import { ref, watch, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';
import { 
    PlusIcon, DocumentTextIcon, 
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    ExclamationTriangleIcon,
    ArrowDownTrayIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    products: Object,
    filters: Object,
    totalInventoryValue: Number,
});

const search = ref(props.filters.search || '');
const stockFilter = ref(props.filters.stock || 'all');

const updateSearch = debounce((value) => {
    router.get(route('admin.products.index'), { search: value, stock: stockFilter.value }, {
        preserveState: true,
        replace: true,
    });
}, 300);
watch(search, updateSearch);
onBeforeUnmount(() => updateSearch.cancel());

const applyStockFilter = (filter) => {
    updateSearch.cancel();
    stockFilter.value = filter;
    router.get(route('admin.products.index'), { search: search.value, stock: filter }, {
        preserveState: true,
        replace: true,
    });
};

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to archive this product? It will be soft-deleted and can be restored later.')) {
        router.delete(route('admin.products.destroy', id));
    }
};

const getStockBadge = (product) => {
    if (product.variants_count > 0) return { label: 'Master Container', classes: 'bg-purple-100 text-purple-800 border-purple-200' };
    if (product.stock_quantity <= 0) {
        return { label: 'Out of Stock', classes: 'bg-red-100 text-red-800 border-red-200' };
    } else if (product.stock_quantity <= (product.reorder_level || 10)) {
        return { label: 'Low Stock', classes: 'bg-amber-100 text-amber-800 border-amber-200' };
    }
    return { label: 'In Stock', classes: 'bg-green-100 text-green-800 border-green-200' };
};


</script>

<template>
    <AdminLayout>
        <Head title="Inventory Management | ERP" />
        
        <template #header>Inventory Management</template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Products</p>
                <p class="text-2xl font-extrabold text-jubis-navy mt-1">{{ products.total }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:ring-2 hover:ring-amber-300 transition" @click="applyStockFilter('low')">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Low Stock Alerts</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">
                    <ExclamationTriangleIcon class="w-5 h-5 inline -mt-1 mr-1" />
                    {{ products.data?.filter(p => !p.variants_count && p.stock_quantity > 0 && p.stock_quantity <= (p.reorder_level || 10)).length || 0 }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 cursor-pointer hover:ring-2 hover:ring-red-300 transition" @click="applyStockFilter('out')">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Out of Stock</p>
                <p class="text-2xl font-extrabold text-red-600 mt-1">{{ products.data?.filter(p => !p.variants_count && p.stock_quantity <= 0).length || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Inventory Value</p>
                <p class="text-2xl font-extrabold text-green-700 mt-1">₱{{ Number(totalInventoryValue || 0).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input type="text" v-model="search" placeholder="Search products by name, SKU, or brand..." class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm" />
                    </div>
                    
                    <!-- Stock Filter Pills -->
                    <div class="hidden sm:flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                        <button @click="applyStockFilter('all')" :class="[stockFilter === 'all' ? 'bg-white shadow-sm text-jubis-navy font-bold' : 'text-gray-500 hover:text-gray-700', 'px-3 py-1.5 text-xs rounded-md transition-all']">All</button>
                        <button @click="applyStockFilter('low')" :class="[stockFilter === 'low' ? 'bg-amber-100 shadow-sm text-amber-800 font-bold' : 'text-gray-500 hover:text-gray-700', 'px-3 py-1.5 text-xs rounded-md transition-all']">Low Stock</button>
                        <button @click="applyStockFilter('out')" :class="[stockFilter === 'out' ? 'bg-red-100 shadow-sm text-red-800 font-bold' : 'text-gray-500 hover:text-gray-700', 'px-3 py-1.5 text-xs rounded-md transition-all']">Out of Stock</button>
                    </div>
                </div>
                
                <a :href="route('admin.products.export')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 mr-3 transition-colors"><DocumentTextIcon class="h-5 w-5 mr-2 text-gray-400" />Export CSV</a>
                <Link :href="route('admin.products.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-jubis-navy hover:bg-[#071126] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-navy transition-colors">
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Add New Product
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">SKU & Brand</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Price (PHP)</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stock Qty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-lg flex items-center justify-center p-1 border border-gray-200">
                                        <ProductImage :src="product.image_path" alt="" class="h-full w-full object-contain rounded-md" />
                                    </div>
                                    <div class="ml-4 max-w-[250px]">
                                        <div class="text-sm font-bold text-jubis-navy truncate">{{ product.name }}</div>
                                        <div class="text-xs text-gray-400">{{ product.unit_of_measure }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">{{ product.sku }}</div>
                                <div class="text-xs text-gray-500">{{ product.brand }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ product.category?.name || 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                ₱{{ Number(product.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div v-if="product.variants_count > 0" class="text-sm font-bold text-gray-400 italic">N/A</div>
                                <div v-else class="text-sm font-bold" :class="product.stock_quantity <= 0 ? 'text-red-600' : product.stock_quantity <= (product.reorder_level || 10) ? 'text-amber-600' : 'text-gray-900'">
                                    {{ product.stock_quantity }}
                                </div>
                                <div v-if="product.stock_quantity > 0 && product.stock_quantity <= (product.reorder_level || 10)" class="text-[10px] text-amber-500 font-medium mt-0.5">
                                    <ExclamationTriangleIcon class="w-3 h-3 inline -mt-0.5" /> Reorder
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[getStockBadge(product).classes, 'px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full border']">
                                    {{ getStockBadge(product).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <Link :href="route('admin.products.edit', product.id)" class="text-blue-600 hover:text-blue-900 transition-colors p-1" title="Edit Product">
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>
                                    <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900 transition-colors p-1" title="Archive Product">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data?.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-lg font-medium">No products found.</p>
                                <p class="text-sm mt-1">Try adjusting your search or filter criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-wrap gap-3 items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-900">{{ products.from || 0 }}</span> to <span class="font-medium text-gray-900">{{ products.to || 0 }}</span> of <span class="font-medium text-gray-900">{{ products.total }}</span> results
                </div>
                <div class="flex flex-wrap gap-1" v-if="products.links && products.links.length > 3">
                    <template v-for="(link, index) in products.links" :key="index">
                        <Link v-if="link.url" :href="link.url" class="px-3 py-1 border rounded text-sm font-medium transition-colors" :class="link.active ? 'bg-jubis-navy text-white border-jubis-navy' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300'" v-html="link.label" />
                        <span v-else class="px-3 py-1 border rounded text-sm font-medium bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
