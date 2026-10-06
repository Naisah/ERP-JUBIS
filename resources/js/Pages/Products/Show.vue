<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ChevronRightIcon, ShoppingCartIcon, ShieldCheckIcon, DocumentTextIcon, TruckIcon, QueueListIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    relatedProducts: Array
});

const quantity = ref(1);
const loading = ref(false);

const variantQuantities = reactive({});
if (props.product.variants && props.product.variants.length > 0) {
    props.product.variants.forEach(v => {
        variantQuantities[v.id] = 1;
    });
}

const addToQuote = (productId, qty) => {
    if (!usePage().props.auth.user) {
        return router.get(route('login'));
    }

    router.post(route('cart.add'), { product_id: productId, quantity: qty }, {
        preserveScroll: true,
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false
    });
};


</script>

<template>
    <PublicLayout>
        <Head :title="`${product.name} | Jubis Marketing`" />

        <div class="bg-gray-50 py-8 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <nav class="flex text-sm text-gray-500 mb-8 space-x-2 items-center">
                    <Link :href="route('products.index')" class="hover:text-jubis-navy hover:underline">Catalog</Link>
                    <ChevronRightIcon class="w-4 h-4" />
                    <Link v-if="product.category" :href="route('products.index', { categories: product.category.id })" class="hover:text-jubis-navy hover:underline">{{ product.category.name }}</Link>
                    <ChevronRightIcon v-if="product.category" class="w-4 h-4" />
                    <span class="text-jubis-navy font-semibold">{{ product.name }}</span>
                </nav>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-12">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        
                        <!-- Left: Image Gallery -->
                        <div class="p-8 md:p-12 flex items-center justify-center bg-white border-b md:border-b-0 md:border-r border-gray-100 relative">
                            <span v-if="product.variants && product.variants.length > 0" class="absolute top-6 left-6 z-10 bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1.5 rounded-full border border-blue-200">Multiple Options</span>
                            <span v-else-if="!product.stock_quantity > 0" class="absolute top-6 left-6 z-10 bg-gray-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">Out of Stock</span>
                            <img :src="product.image_path || 'https://placehold.co/400x400/eeeeee/999999?text=No+Image'" :alt="product.name" class="w-full max-w-md h-auto object-contain drop-shadow-sm" />
                        </div>

                        <!-- Right: Product Info -->
                        <div class="p-8 md:p-12 flex flex-col">
                            <div class="text-sm text-jubis-gold font-extrabold uppercase tracking-wider mb-2">{{ product.brand }}</div>
                            <h1 class="text-3xl font-extrabold text-jubis-navy mb-4 leading-tight">{{ product.name }}</h1>
                            
                            <div class="flex items-center space-x-6 text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100">
                                <p>SKU: <span class="font-bold text-gray-700">{{ product.sku }}</span></p>
                                <p v-if="product.category">Category: <span class="font-bold text-gray-700">{{ product.category.name }}</span></p>
                            </div>

                            <!-- B2B Pricing Callout -->
                            <div v-if="!$page.props.auth.user" class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-8 flex items-start space-x-4">
                                <ShieldCheckIcon class="w-6 h-6 text-jubis-navy flex-shrink-0 mt-0.5" />
                                <div>
                                    <h3 class="font-bold text-jubis-navy text-sm">Wholesale Pricing Available</h3>
                                    <p class="text-sm text-gray-600 mt-1">Please <Link href="/login" class="text-jubis-red font-bold hover:underline">sign in to your client account</Link> to view exclusive corporate pricing and request a formal quotation.</p>
                                </div>
                            </div>
                            <div v-else class="mb-8">
                                <p v-if="product.variants && product.variants.length > 0" class="text-gray-500 text-sm mb-1">See variant table below for pricing</p>
                                <div v-else>
                                    <p v-if="Number(product.wholesale_price) > 0" class="text-3xl font-extrabold text-gray-900">₱{{ Number(product.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits: 2}) }} <span class="text-sm text-gray-500 font-normal">/ {{ product.unit_of_measure }}</span></p>
                                    <p v-else class="text-xl font-bold text-gray-500 italic">Volume Pricing</p>
                                    <p v-if="Number(product.wholesale_price) > 0" class="text-sm text-green-600 font-bold mt-1">Baseline Corporate Price</p>
                                    <p v-else class="text-sm text-gray-400 font-bold mt-1">Price available upon quote request</p>
                                </div>
                            </div>

                            <p class="text-gray-600 mb-8 leading-relaxed">
                                {{ product.description }}
                            </p>

                            <div v-if="!product.variants || product.variants.length === 0" class="mt-auto space-y-4">
                                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                                    <!-- Quantity Selector -->
                                    <div class="flex items-center border border-gray-300 rounded-md bg-white">
                                        <button @click="quantity > 1 ? quantity-- : null" class="px-4 py-3 text-gray-500 hover:text-jubis-navy hover:bg-gray-50 transition">-</button>
                                        <input type="number" v-model="quantity" min="1" :max="product.stock_quantity" @keypress="(e) => e.key === '-' || e.key === 'e' || e.key === '.' ? e.preventDefault() : null" class="w-16 text-center border-0 focus:ring-0 text-sm font-bold p-0" />
                                        <button @click="quantity < product.stock_quantity ? quantity++ : null" class="px-4 py-3 text-gray-500 hover:text-jubis-navy hover:bg-gray-50 transition">+</button>
                                    </div>
                                    
                                    <!-- Add to Quote Button -->
                                    <button :disabled="!product.stock_quantity > 0" @click="addToQuote(product.id, quantity)" class="w-full sm:flex-grow flex justify-center items-center py-3.5 px-6 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-jubis-red hover:bg-red-700 focus:outline-none transition-colors" :class="{ 'opacity-50 cursor-not-allowed bg-gray-400 hover:bg-gray-400': !product.stock_quantity > 0 }">
                                        <ShoppingCartIcon class="w-5 h-5 mr-2 stroke-2" />
                                        {{ loading ? 'Adding...' : (product.stock_quantity > 0 ? 'Add to Quote' : 'Unavailable') }}
                                    </button>
                                </div>
                                <p class="text-xs text-center text-gray-400 flex items-center justify-center">
                                    <TruckIcon class="w-4 h-4 mr-1" /> Nationwide delivery available
                                </p>
                            </div>
                            <div v-else class="mt-auto bg-blue-50 border border-blue-100 rounded-md p-4 text-center">
                                <p class="text-sm text-blue-800 font-bold flex items-center justify-center">
                                    <QueueListIcon class="w-5 h-5 mr-2" />
                                    Please select a specific model from the variant table below.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- B2B Variant Selection Table -->
                    <div v-if="product.variants && product.variants.length > 0" class="bg-gray-50 border-t border-gray-200 p-8 md:p-12">
                        <h2 class="text-xl font-bold text-jubis-navy mb-6 flex items-center">
                            <QueueListIcon class="w-6 h-6 mr-2" /> Available Models & Variants
                        </h2>
                        
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden overflow-x-auto shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-gray-800">Model / Specifications</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-800">SKU</th>
                                        <th class="px-4 py-3 text-right font-bold text-gray-800">Price</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-800">Stock</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-800 w-48">Order Quantity</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="variant in product.variants" :key="variant.id" class="hover:bg-blue-50 transition-colors">
                                        <td class="px-4 py-4">
                                            <p class="font-bold text-jubis-navy">{{ variant.name }}</p>
                                            <p v-if="variant.description" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ variant.description }}</p>
                                        </td>
                                        <td class="px-4 py-4 font-mono text-xs text-gray-600">{{ variant.sku }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-gray-900">
                                            <span v-if="$page.props.auth.user">₱{{ Number(variant.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits:2}) }}</span>
                                            <span v-else class="text-xs text-gray-400 italic">Login required</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span v-if="variant.stock_quantity > 10" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">In Stock</span>
                                            <span v-else-if="variant.stock_quantity > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Low Stock ({{ variant.stock_quantity }})</span>
                                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center border border-gray-300 rounded bg-white w-28 mx-auto" :class="{'opacity-50 cursor-not-allowed': !variant.stock_quantity > 0}">
                                                <button :disabled="!variant.stock_quantity > 0" @click="variantQuantities[variant.id] > 1 ? variantQuantities[variant.id]-- : null" class="px-2 py-1 text-gray-500 hover:text-jubis-navy hover:bg-gray-50">-</button>
                                                <input :disabled="!variant.stock_quantity > 0" type="number" v-model="variantQuantities[variant.id]" min="1" :max="variant.stock_quantity" class="w-12 text-center border-0 focus:ring-0 text-xs font-bold p-0" />
                                                <button :disabled="!variant.stock_quantity > 0" @click="variantQuantities[variant.id] < variant.stock_quantity ? variantQuantities[variant.id]++ : null" class="px-2 py-1 text-gray-500 hover:text-jubis-navy hover:bg-gray-50">+</button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button :disabled="!variant.stock_quantity > 0" @click="addToQuote(variant.id, variantQuantities[variant.id])" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-bold rounded shadow-sm text-white bg-jubis-navy hover:bg-[#071126] disabled:opacity-50 disabled:bg-gray-400 transition-colors">
                                                Add
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- General Specifications HTML / Old table -->
                    <div v-if="product.specifications || (product.specs && Object.keys(product.specs).length > 0)" class="bg-gray-50 border-t border-gray-100 p-8 md:p-12">
                        <h2 class="text-xl font-bold text-jubis-navy mb-6 flex items-center">
                            <DocumentTextIcon class="w-6 h-6 mr-2" /> General Specifications
                        </h2>
                        
                        <div v-if="product.specifications" class="bg-white border border-gray-200 rounded-lg overflow-hidden overflow-x-auto p-4" v-html="product.specifications"></div>
                        <div v-else class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="(value, key) in product.specs" :key="key" class="even:bg-gray-50 odd:bg-white">
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 w-1/3">{{ key }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ value }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Related Products (Stub) -->
                <div>
                    <h2 class="text-2xl font-bold text-jubis-navy mb-6">Frequently Bought Together</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                        <Link v-for="related in relatedProducts" :key="related.id" :href="`/products/${related.id}`" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group">
                            <div class="h-48 bg-white relative p-4 flex items-center justify-center border-b border-gray-100">
                                <img :src="related.image_path" :alt="related.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform" />
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <div class="text-xs text-jubis-gold font-bold uppercase tracking-wider mb-1">{{ related.brand }}</div>
                                <h3 class="font-bold text-jubis-navy text-sm mb-2 line-clamp-2">{{ related.name }}</h3>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>

