<script setup>
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    brands_carried: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    tax_id: ''
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (supplier) => {
    form.clearErrors();
    editingId.value = supplier.id;
    form.name = supplier.name;
    form.brands_carried = supplier.brands_carried;
    form.contact_person = supplier.contact_person;
    form.email = supplier.email;
    form.phone = supplier.phone;
    form.address = supplier.address;
    form.tax_id = supplier.tax_id;
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.suppliers.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.suppliers.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const destroy = (id) => {
    if (confirm('Are you sure you want to delete this supplier?')) {
        router.delete(route('admin.suppliers.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Suppliers | Admin" />

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Supplier Directory</h1>
                <p class="text-sm text-gray-500 mt-1">Manage vendor contacts and company information.</p>
            </div>
            <button @click="openCreate" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-jubis-navy hover:bg-[#071126]">
                <PlusIcon class="h-5 w-5 mr-2" />
                Add Supplier
            </button>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-300 rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900">Company Name</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Brands Carried</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Contact Person</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Contact Details</th>
                        <th class="px-3 py-3.5 text-left font-semibold text-gray-900">Tax ID</th>
                        <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900">{{ supplier.name }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-jubis-navy font-semibold">{{ supplier.brands_carried || '-' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-500">{{ supplier.contact_person || '-' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-500">
                            <div>{{ supplier.email || '-' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ supplier.phone || '' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-gray-500">{{ supplier.tax_id || '-' }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right font-medium">
                            <button @click="openEdit(supplier)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
                            <button @click="destroy(supplier.id)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="suppliers.data.length === 0">
                        <td colspan="5" class="py-8 text-center text-gray-500">No suppliers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-x-auto shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                            {{ editingId ? 'Edit Supplier' : 'Add New Supplier' }}
                        </h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <ul v-if="Object.keys(form.errors).length" role="alert" class="text-sm text-red-700 list-disc pl-5"><li v-for="(error, field) in form.errors" :key="field">{{ error }}</li></ul>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                <input type="text" v-model="form.name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Brands Carried (e.g. OMNI, Philips, GE)</label>
                                <input type="text" v-model="form.brands_carried" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm placeholder-gray-400" placeholder="Separate multiple brands with commas" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                    <input type="text" v-model="form.contact_person" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tax ID</label>
                                    <input type="text" v-model="form.tax_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" v-model="form.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" v-model="form.phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Physical Address</label>
                                <textarea v-model="form.address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                            </div>
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-jubis-navy text-base font-medium text-white hover:bg-[#071126] sm:col-start-2 sm:text-sm">
                                    Save
                                </button>
                                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <Pagination :links="suppliers.links" />
    </AdminLayout>
</template>
