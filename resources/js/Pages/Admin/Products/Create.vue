<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    existingBrands: Array
});

const form = useForm({
    name: '',
    sku: '',
    brand: '',
    category_id: '',
    wholesale_price: '',
    stock_quantity: 0, unit_of_measure: 'pcs',
    description: '',
    image: null
});

const imagePreview = ref(null);

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

onBeforeUnmount(() => { if (imagePreview.value) URL.revokeObjectURL(imagePreview.value); });

const submit = () => {
    form.post(route('admin.products.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Product | ERP" />
        
        <template #header>Create New Product</template>

        <div class="mb-6 flex items-center">
            <Link :href="route('admin.products.index')" class="flex items-center text-sm font-medium text-gray-500 hover:text-jubis-navy transition-colors">
                <ArrowLeftIcon class="w-4 h-4 mr-1" /> Back to Inventory
            </Link>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-lg font-bold text-jubis-navy border-b border-gray-100 pb-3">Basic Information</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Product Name -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.name" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" placeholder="e.g. THHN / THWN Stranded Wire" />
                        <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.sku" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5 font-mono uppercase" placeholder="e.g. PD-35-THHN" />
                        <div v-if="form.errors.sku" class="text-sm text-red-600 mt-1">{{ form.errors.sku }}</div>
                    </div>

                    <!-- Wholesale Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Wholesale Price (PHP) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₱</span>
                            </div>
                            <input type="number" step="0.01" v-model="form.wholesale_price" required class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" placeholder="0.00" />
                        </div>
                        <div v-if="form.errors.wholesale_price" class="text-sm text-red-600 mt-1">{{ form.errors.wholesale_price }}</div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select v-model="form.category_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5 text-gray-700">
                            <option value="" disabled>Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                        <div v-if="form.errors.category_id" class="text-sm text-red-600 mt-1">{{ form.errors.category_id }}</div>
                    </div>

                    <!-- Brand (Datalist) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                        <input type="text" list="brands-list" v-model="form.brand" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" placeholder="Start typing brand..." />
                        <datalist id="brands-list">
                            <option v-for="brand in existingBrands" :key="brand" :value="brand" />
                        </datalist>
                        <div v-if="form.errors.brand" class="text-sm text-red-600 mt-1">{{ form.errors.brand }}</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Product Description</label>
                    <textarea v-model="form.description" rows="5" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" placeholder="Detailed product specifications..."></textarea>
                    <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
                </div>
            </div>

            <!-- Sidebar Controls -->
            <div class="space-y-6">
                <!-- Status & Visibility -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Availability</h2>
                    
                    <div class="flex items-center">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Stock Quantity</label>
                        <input type="number" v-model="form.stock_quantity" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" />
                        <div v-if="form.errors.stock_quantity" class="text-sm text-red-600 mt-1">{{ form.errors.stock_quantity }}</div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Unit of Measure</label>
                        <input type="text" v-model="form.unit_of_measure" placeholder="e.g. pcs, meters" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-jubis-navy focus:ring-jubis-navy sm:text-sm py-2.5" />
                        <div v-if="form.errors.unit_of_measure" class="text-sm text-red-600 mt-1">{{ form.errors.unit_of_measure }}</div>
                    </div>
                </div>

                <!-- Product Image -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Product Image</h2>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative" role="button" tabindex="0" aria-label="Choose product image" @keydown.enter.prevent="$refs.fileInput.click()" @keydown.space.prevent="$refs.fileInput.click()" @click="$refs.fileInput.click()">
                        <div class="space-y-1 text-center" v-if="!form.image">
                            <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <div class="flex text-sm text-gray-600 justify-center">
                                <span class="relative cursor-pointer rounded-md font-medium text-jubis-navy hover:text-jubis-gold focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-jubis-navy">
                                    Upload a file
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                        <div v-else class="text-center">
                            <img v-if="imagePreview" :src="imagePreview" alt="Selected product image" class="h-32 mx-auto mb-3 object-contain" />
                            <span class="text-sm text-green-600 font-bold mb-2 block">Image Selected!</span>
                            <span class="text-xs text-gray-500">{{ form.image.name }}</span>
                        </div>
                        <input type="file" ref="fileInput" @click.stop @change="handleImageUpload" class="sr-only" accept="image/*" />
                    </div>
                    <div v-if="form.errors.image" class="text-sm text-red-600 mt-2">{{ form.errors.image }}</div>
                </div>

                <!-- Submit Action -->
                <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-jubis-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-red transition-all duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                    {{ form.processing ? 'Saving Product...' : 'Save New Product' }}
                </button>
            </div>
            
        </form>
    </AdminLayout>
</template>

