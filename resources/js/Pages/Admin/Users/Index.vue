<script setup>
import Pagination from '@/Components/Pagination.vue';
import { ref, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { UserIcon, ShieldCheckIcon, PencilSquareIcon, TrashIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');
const typeFilter = ref(props.filters.type || 'client');

const updateSearch = debounce(() => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
        type: typeFilter.value
    }, { preserveState: true, replace: true });
}, 300);

const setType = (type) => {
    typeFilter.value = type;
    roleFilter.value = 'all'; // reset role filter when switching tabs
    updateSearch();
};
onBeforeUnmount(() => updateSearch.cancel());

const showCreateModal = ref(false);
const showEditModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'client',
    company_name: '',
});

const editForm = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    role: '',
    company_name: '',
    credit_status: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (user) => {
    editForm.reset();
    editForm.clearErrors();
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.company_name = user.company_name || '';
    editForm.credit_status = user.credit_status || 'pending';
    showEditModal.value = true;
};

const submitCreate = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => showCreateModal.value = false,
    });
};

const submitEdit = () => {
    editForm.put(route('admin.users.update', editForm.id), {
        onSuccess: () => showEditModal.value = false,
    });
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id), {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const roles = [
    { value: 'client', label: 'Client (B2B Buyer)', color: 'bg-gray-100 text-gray-800' },
    { value: 'sales', label: 'Sales Staff', color: 'bg-purple-100 text-purple-800' },
    { value: 'purchasing', label: 'Purchasing Staff', color: 'bg-blue-100 text-blue-800' },
    { value: 'warehouse', label: 'Warehouse Staff', color: 'bg-amber-100 text-amber-800' },
    { value: 'finance', label: 'Finance Staff', color: 'bg-green-100 text-green-800' },
    { value: 'admin', label: 'Admin', color: 'bg-red-100 text-red-800' },
    { value: 'super_admin', label: 'Super Admin', color: 'bg-red-200 text-red-900 font-bold' },
];

const getRoleBadge = (role) => {
    const found = roles.find(r => r.value === role);
    return found ? found.color : 'bg-gray-100 text-gray-800';
};
const getRoleLabel = (role) => {
    const found = roles.find(r => r.value === role);
    return found ? found.label : role;
};
</script>

<template>
    <AdminLayout>
        <Head title="User Management | Admin" />
        <template #header>User Management</template>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="setType('client')" :class="[typeFilter === 'client' ? 'border-jubis-navy text-jubis-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors']">
                    B2B Clients
                </button>
                <button @click="setType('staff')" :class="[typeFilter === 'staff' ? 'border-jubis-navy text-jubis-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors']">
                    Internal Staff & Admins
                </button>
            </nav>
        </div>

        <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex flex-1 gap-4 w-full md:w-auto">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input v-model="search" @input="updateSearch" type="text" placeholder="Search users, emails, companies..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm" />
                </div>
                <select v-if="typeFilter === 'staff'" v-model="roleFilter" @change="updateSearch" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-jubis-navy focus:border-jubis-navy sm:text-sm rounded-md">
                    <option value="all">All Roles</option>
                    <option v-for="r in roles.filter(x => x.value !== 'client')" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
            </div>
            
            <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-jubis-navy border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#071126] focus:bg-[#071126] active:bg-[#071126] focus:outline-none focus:ring-2 focus:ring-jubis-navy focus:ring-offset-2 transition ease-in-out duration-150">
                <UserIcon class="w-4 h-4 mr-2" />
                Add {{ typeFilter === 'staff' ? 'Staff' : 'Client' }}
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name / Company</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">System Role</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Account Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ user.name }}</div>
                                <div class="text-sm text-gray-500">{{ user.company_name || '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
                            
<td class="px-6 py-4">
    <span :class="[getRoleBadge(user.role), 'px-2.5 py-0.5 rounded-full text-xs font-medium uppercase']">
        {{ getRoleLabel(user.role) }}
    </span>
</td>
<td class="px-6 py-4">
    <span v-if="user.role === 'client'" :class="[user.credit_status === 'approved' ? 'bg-green-100 text-green-800' : (user.credit_status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'), 'px-2.5 py-0.5 rounded-full text-xs font-medium uppercase']">
        {{ user.credit_status }}
    </span>
    <span v-else class="text-gray-400 text-xs">�</span>
</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900 mr-4" title="Edit User">
                                    <PencilSquareIcon class="w-5 h-5 inline" />
                                </button>
                                <button v-if="$page.props.auth.user.id !== user.id" @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900" title="Delete User">
                                    <TrashIcon class="w-5 h-5 inline" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">No users found matching your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <Pagination :links="users.links" />
        </div>

        <!-- Create User Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <UserIcon class="w-5 h-5 mr-2 text-jubis-navy" /> Add New User
                </h2>
                
                <form @submit.prevent="submitCreate">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Full Name" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="company_name" value="Company Name (Optional)" />
                            <TextInput id="company_name" v-model="form.company_name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.company_name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="role" value="System Role" />
                            <select id="role" v-model="form.role" class="mt-1 block w-full border-gray-300 focus:border-jubis-navy focus:ring-jubis-navy rounded-md shadow-sm">
                                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Determines which sections of the Admin Panel this user can access.</p>
                            <InputError :message="form.errors.role" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showCreateModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create User</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <PencilSquareIcon class="w-5 h-5 mr-2 text-jubis-navy" /> Edit User
                </h2>
                
                <form @submit.prevent="submitEdit">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="edit_name" value="Full Name" />
                            <TextInput id="edit_name" v-model="editForm.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="editForm.errors.name" class="mt-2" />
                        </div>
                        
                        <div>
                            <InputLabel for="edit_email" value="Email Address" />
                            <TextInput id="edit_email" v-model="editForm.email" type="email" class="mt-1 block w-full" required />
                            <InputError :message="editForm.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_company_name" value="Company Name (Optional)" />
                            <TextInput id="edit_company_name" v-model="editForm.company_name" type="text" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.company_name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_role" value="System Role" />
                            <select id="edit_role" v-model="editForm.role" class="mt-1 block w-full border-gray-300 focus:border-jubis-navy focus:ring-jubis-navy rounded-md shadow-sm">
                                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                            <InputError :message="editForm.errors.role" class="mt-2" />
                        </div>

                        <div v-if="editForm.role === 'client'">
                            <InputLabel for="edit_credit_status" value="Account Status (B2B Approval)" />
                            <select id="edit_credit_status" v-model="editForm.credit_status" class="mt-1 block w-full border-gray-300 focus:border-jubis-navy focus:ring-jubis-navy rounded-md shadow-sm">
                                <option value="pending">Pending Approval</option>
                                <option value="approved">Approved</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <InputError :message="editForm.errors.credit_status" class="mt-2" />
                        </div>
                        
                        <hr class="my-4" />
                        
                        <div>
                            <InputLabel for="edit_password" value="New Password (Leave blank to keep current)" />
                            <TextInput id="edit_password" v-model="editForm.password" type="password" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.password" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showEditModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">Save Changes</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

