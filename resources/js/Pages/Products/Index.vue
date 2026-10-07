<script setup>
import ProductImage from '@/Components/ProductImage.vue';
import { ref, watch, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { MagnifyingGlassIcon, FunnelIcon, ShoppingCartIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    categories: Array,
    brands: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const loadingId = ref(null);
const selectedCategories = ref(props.filters?.categories ? props.filters.categories.split(',').map(Number) : []);
const selectedBrands = ref(props.filters?.brands ? props.filters.brands.split(',') : []);
const sortOrder = ref(props.filters?.sort || 'relevant');

const updateFilters = debounce(([search, categories, brands, sort]) => {
        if (search === (props.filters?.search || '') && categories.join(',') === (props.filters?.categories || '') && brands.join(',') === (props.filters?.brands || '') && sort === (props.filters?.sort || 'relevant')) return;
        router.get('/products', {
            search: search,
            categories: categories.join(','),
            brands: brands.join(','),
            sort: sort,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
watch([searchQuery, selectedCategories, selectedBrands, sortOrder], values => updateFilters(values), { deep: true });
watch(() => props.filters, filters => {
    updateFilters.cancel();
    searchQuery.value = filters?.search || '';
    selectedCategories.value = filters?.categories ? filters.categories.split(',').map(Number) : [];
    selectedBrands.value = filters?.brands ? filters.brands.split(',') : [];
    sortOrder.value = filters?.sort || 'relevant';
}, { flush: 'sync' });
onBeforeUnmount(() => updateFilters.cancel());

const addToQuote = (productId) => {
    if (loadingId.value !== null) return;
    if (!usePage().props.auth.user) {
        return router.get(route('login'));
    }

    router.post(route('cart.add'), { product_id: productId, quantity: 1 }, {
        preserveScroll: true,
        onStart: () => loadingId.value = productId,
        onFinish: () => loadingId.value = null
    });
};
</script>

<template>
    <PublicLayout>
        <Head title="Product Catalog | Jubis Marketing" />

        <div class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-8">
                    <div>
                        <h1 class="text-3xl font-extrabold text-jubis-navy">Product Catalog</h1>
                        <p class="text-gray-500 mt-2">Browse our extensive inventory of industrial electrical supplies.</p>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="mt-4 md:mt-0 w-full md:w-96 relative shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input type="text" v-model="searchQuery" class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm transition-shadow" placeholder="Search by product name, brand, or SKU..." />
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Sidebar Filters -->
                    <div class="w-full md:w-64 flex-shrink-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-28">
                            <div class="flex items-center mb-4 text-jubis-navy font-extrabold text-lg pb-3 border-b border-gray-100">
                                <FunnelIcon class="w-5 h-5 mr-2 stroke-2" />
                                Filters
                            </div>
                            
                            <h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider">Categories</h3>
                            <ul class="space-y-2.5 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                                <li v-for="category in categories" :key="category.id">
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" :value="category.id" v-model="selectedCategories" class="form-checkbox h-4 w-4 text-jubis-red border-gray-300 rounded focus:ring-jubis-red" />
                                        <span class="text-sm text-gray-600 group-hover:text-jubis-navy font-medium transition-colors">{{ category.name }}</span>
                                    </label>
                                </li>
                            </ul>
                            
                            <h3 class="font-bold text-gray-800 mt-8 mb-3 text-sm uppercase tracking-wider">Top Brands</h3>
                            <ul class="space-y-2.5 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                                <li v-for="brand in brands" :key="brand">
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" :value="brand" v-model="selectedBrands" class="form-checkbox h-4 w-4 text-jubis-red border-gray-300 rounded focus:ring-jubis-red" />
                                        <span class="text-sm text-gray-600 group-hover:text-jubis-navy font-medium transition-colors">{{ brand }}</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div class="flex-grow min-w-0">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-3 justify-between items-center text-sm">
                            <span class="text-gray-500">Showing <span class="font-bold text-gray-800">{{ products.total }}</span> results</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 font-medium">Sort by:</span>
                                <select v-model="sortOrder" class="border-gray-200 rounded-lg text-sm focus:ring-jubis-navy focus:border-jubis-navy py-2 pl-3 pr-8 font-medium text-gray-700">
                                    <option value="relevant">Most Relevant</option><option value="name">Name (A-Z)</option><option value="brand">Brand</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Product Card -->
                            <article v-for="product in products.data" :key="product.id" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group relative">
                                
                                <!-- Out of Stock Overlay -->
                                <div v-if="!product.variants?.length && Number(product.stock_quantity) <= 0" class="absolute top-3 right-3 z-10 bg-gray-800/90 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm backdrop-blur-sm">
                                    Out of Stock
                                </div>
                                
                                <!-- Image Display -->
                                <Link :href="`/products/${product.id}`" class="h-56 bg-white relative p-4 flex items-center justify-center border-b border-gray-100 group-hover:bg-gray-50 transition-colors overflow-hidden">
                                    <ProductImage :src="product.image_path" :alt="product.name" class="w-full h-full object-contain drop-shadow-sm transform group-hover:scale-105 transition-transform duration-300" />
                                </Link>
                                
                                <div class="p-5 flex-grow flex flex-col">
                                    <div class="text-xs text-jubis-gold font-bold uppercase tracking-wider mb-1">{{ product.brand }}</div>
                                    <h3 class="font-bold text-jubis-navy text-[15px] mb-2 line-clamp-2 leading-snug group-hover:text-jubis-red transition-colors cursor-pointer">
                                        <Link :href="`/products/${product.id}`">{{ product.name }}</Link>
                                    </h3>
                                    <p class="text-xs text-gray-500 mb-5">SKU: {{ product.sku }}</p>
                                    
                                    <div class="mt-auto">
                                        <!-- B2B Pricing Shield -->
                                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                                            <span v-if="!$page.props.auth.user" class="text-xs italic text-gray-500 flex items-center">
                                                <LockClosedIcon class="w-3 h-3 mr-1" />
                                                Sign in for pricing
                                            </span>
                                            <span v-else-if="Number(product.wholesale_price) > 0" class="text-lg font-bold text-jubis-navy flex items-center">
                                                ₱{{ Number(product.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits:2}) }}
                                            </span>
                                            <span v-else class="text-sm font-bold text-gray-500 italic flex items-center">
                                                Volume Pricing
                                            </span>
                                        </div>
                                        
                                        <div v-if="product.variants && product.variants.length > 0">
                                            <Link :href="'/products/' + product.id" class="w-full flex items-center justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" /></svg>
                                                Select Options
                                            </Link>
                                        </div>
                                        <div v-else>
                                            <button :disabled="Number(product.stock_quantity) <= 0 || loadingId !== null" @click.prevent="product.stock_quantity > 0 ? addToQuote(product.id) : null" class="w-full flex items-center justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-jubis-navy hover:bg-[#071126] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-navy transition-colors" :class="{ 'opacity-50 cursor-not-allowed bg-gray-400 hover:bg-gray-400': Number(product.stock_quantity) <= 0 }">
                                                <ShoppingCartIcon class="w-4 h-4 mr-2 stroke-2" />
                                                {{ loadingId === product.id ? 'Adding...' : (product.stock_quantity > 0 ? 'Add to Quote' : 'Unavailable') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-if="products.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-100 mt-6">
                            <p class="text-gray-500 font-medium">No products found matching your search.</p>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-12 flex justify-center" v-if="products.links && products.links.length > 3">
                            <nav class="relative z-0 inline-flex flex-wrap justify-center rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, pIndex) in products.links" :key="pIndex">
                                    <div v-if="link.url === null" class="relative inline-flex items-center px-4 py-2 border border-gray-200 bg-gray-50 text-sm font-medium text-gray-400 cursor-not-allowed" v-html="link.label"></div>
                                    <Link v-else :href="link.url" preserve-scroll preserve-state class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors" :class="link.active ? 'z-10 bg-jubis-navy text-white border-jubis-navy' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'" v-html="link.label" />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f8fafc; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}
</style>










