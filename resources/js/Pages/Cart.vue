<script setup>
import ProductImage from '@/Components/ProductImage.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { TrashIcon, ArrowRightIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const props = defineProps({
    cart: Object
});

const form = useForm({
    shipping_address: page.props.auth.user.shipping_address || page.props.auth.user.billing_address || '',
    client_notes: ''
});

const removeItem = (itemId) => {
    if (confirm('Remove this item from your quote cart?')) {
        form.delete(route('cart.remove', itemId), {
            preserveScroll: true
        });
    }
};

const submitQuote = () => {
    if (confirm('Are you ready to submit this quote request for review?')) {
        form.post(route('cart.submit'));
    }
};
</script>

<template>
    <PublicLayout>
        <Head title="Your Quote Cart | Jubis Marketing" />

        <div class="bg-gray-50 min-h-[75vh] py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <h1 class="text-3xl font-extrabold text-jubis-navy mb-8">Your Quote Request</h1>

                <div v-if="!cart || !cart.items || cart.items.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <ShoppingCartIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Your cart is empty.</h2>
                    <p class="text-gray-500 mb-6">Browse our catalog to add industrial products to your quote request.</p>
                    <Link :href="route('products.index')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-md shadow-sm text-white bg-jubis-red hover:bg-red-700 transition-colors">
                        Browse Catalog
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left: Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                <li v-for="item in cart.items" :key="item.id" class="p-6 flex flex-col sm:flex-row items-start sm:items-center">
                                    <div class="flex-shrink-0 h-24 w-24 bg-gray-50 rounded-lg border border-gray-200 p-2 flex items-center justify-center">
                                        <ProductImage :src="item.product?.image_path" :alt="item.product?.name || 'Unavailable product'" class="h-full w-full object-contain rounded" />
                                    </div>
                                    <div class="ml-0 sm:ml-6 mt-4 sm:mt-0 flex-1">
                                        <div class="flex flex-wrap gap-3 justify-between">
                                            <div>
                                                <h3 class="text-lg font-bold text-jubis-navy">
                                                    <Link v-if="item.product" :href="route('products.show', item.product.id)" class="hover:underline">{{ item.product.name }}</Link>
                                                    <span v-else>Product no longer available — remove it to continue.</span>
                                                </h3>
                                                <p class="text-sm text-gray-500 mt-1">SKU: <span class="font-mono text-gray-700">{{ item.product?.sku || 'Unavailable' }}</span></p>
                                                <p class="text-sm text-gray-500">Brand: <span class="font-medium">{{ item.product?.brand || 'Unavailable' }}</span></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-gray-900">₱{{ Number(item.quoted_price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</p>
                                                <p class="text-sm text-gray-500 mt-1">{{ item.quantity }} units x ₱{{ Number(item.quoted_price).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex justify-end">
                                            <button :disabled="form.processing" @click="removeItem(item.id)" class="text-sm font-bold text-red-600 hover:text-red-800 transition-colors inline-flex items-center">
                                                <TrashIcon class="w-4 h-4 mr-1" /> Remove
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right: Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                            <h2 class="text-lg font-bold text-jubis-navy border-b border-gray-100 pb-4 mb-4">Request Summary</h2>
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Total Items</span>
                                <span class="font-bold text-gray-900">{{ cart.items.length }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Subtotal (VAT-Exclusive)</span>
                                <span class="font-medium text-gray-900">₱{{ Number(cart.subtotal || (cart.total_amount/1.12)).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</span>
                            </div>

                            <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-100">
                                <span class="text-gray-600">12% VAT</span>
                                <span class="font-medium text-gray-900">₱{{ Number(cart.vat_amount || (cart.total_amount - (cart.total_amount/1.12))).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-lg font-bold text-gray-900">Total Amount Due</span>
                                <span class="text-xl font-extrabold text-jubis-red">₱{{ Number(cart.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}</span>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Shipping Address</label>
                                <textarea v-model="form.shipping_address" rows="2" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-jubis-navy focus:ring-jubis-navy" placeholder="Enter delivery location..."></textarea>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Order Notes (Optional)</label>
                                <textarea v-model="form.client_notes" rows="2" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-jubis-navy focus:ring-jubis-navy" placeholder="Special instructions..."></textarea>
                            </div>

                            <div class="bg-blue-50 text-blue-800 text-xs p-4 rounded-lg mb-6 border border-blue-100">
                                <strong class="font-bold block mb-1">B2B Notice:</strong>
                                By submitting this quote request, you are asking our sales team for formal review. No payment will be captured today. We will get back to you with the final approved PO and logistics details.
                            </div>

                            <button @click="submitQuote" :disabled="form.processing || cart.items.some(item => !item.product)" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-jubis-navy hover:bg-[#071126] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-navy disabled:opacity-50">
                                Submit Quote Request
                                <ArrowRightIcon class="w-4 h-4 ml-2" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>
