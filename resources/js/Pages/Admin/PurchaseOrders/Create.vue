<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TrashIcon, PlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    suppliers: Array,
    products: Array,
});

const form = useForm({
    supplier_id: '',
    expected_delivery_date: '',
    notes: '',
    items: []
});

const selectedProductId = ref('');

const addItem = () => {
    if (!selectedProductId.value) return;
    
    const product = props.products.find(p => p.id === parseInt(selectedProductId.value));
    if (!product) return;

    // Check if already in list
    const existing = form.items.find(i => i.product_id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        form.items.push({
            product_id: product.id,
            name: product.name,
            sku: product.sku,
            quantity: 1,
            unit_cost: product.wholesale_price, // Defaulting to wholesale, they can edit
        });
    }
    selectedProductId.value = '';
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const totalCost = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0);
});

const submit = () => {
    form.post(route('admin.purchase-orders.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Purchase Order | Admin" />

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('admin.purchase-orders.index')" class="mr-4 text-gray-500 hover:text-gray-700">
                    <ArrowLeftIcon class="h-6 w-6" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Items Selection -->
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-300 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Line Items</h2>
                    
                    <div class="flex gap-4 mb-6">
                        <select v-model="selectedProductId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm">
                            <option value="" disabled>Select a product to order...</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                [{{ product.sku }}] {{ product.name }} (In Stock: {{ product.stock_quantity }})
                            </option>
                        </select>
                        <button type="button" @click="addItem" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <PlusIcon class="h-5 w-5 mr-1" /> Add
                        </button>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Unit Cost (₱)</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="py-3">
                                    <div class="font-bold text-sm">{{ item.name }}</div>
                                    <div class="text-xs text-gray-500">{{ item.sku }}</div>
                                </td>
                                <td class="py-3">
                                    <input type="number" step="0.01" v-model.number="item.unit_cost" class="w-24 rounded-md border-gray-300 sm:text-sm" />
                                </td>
                                <td class="py-3">
                                    <input type="number" min="1" v-model.number="item.quantity" class="w-20 rounded-md border-gray-300 sm:text-sm" />
                                </td>
                                <td class="py-3 font-medium text-sm">
                                    ₱{{ (item.quantity * item.unit_cost).toLocaleString('en-PH', {minimumFractionDigits: 2}) }}
                                </td>
                                <td class="py-3 text-right">
                                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700"><TrashIcon class="w-5 h-5" /></button>
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td colspan="5" class="py-6 text-center text-gray-500 text-sm">No items added to PO yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            
            <div class="lg:col-span-1">
                <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm ring-1 ring-gray-300 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Order Details</h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select v-model="form.supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm">
                            <option value="" disabled>Select Supplier...</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                        <div v-if="form.errors.supplier_id" class="text-red-500 text-xs mt-1">{{ form.errors.supplier_id }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expected Delivery</label>
                        <input type="date" v-model="form.expected_delivery_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600 font-bold">Total PO Value:</span>
                            <span class="text-xl font-bold text-gray-900">₱{{ totalCost.toLocaleString('en-PH', {minimumFractionDigits:2}) }}</span>
                        </div>
                        <button type="submit" :disabled="form.processing || form.items.length === 0" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-jubis-navy hover:bg-[#071126] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-navy disabled:opacity-50">
                            Create Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
