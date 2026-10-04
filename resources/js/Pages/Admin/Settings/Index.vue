<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Cog6ToothIcon, BuildingOfficeIcon, ReceiptPercentIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    company_name: props.settings?.company_name || 'Jubis Marketing',
    company_address: props.settings?.company_address || '123 Business Road, Metro Manila, Philippines',
    contact_email: props.settings?.contact_email || 'contact@jubismarketing.com',
    contact_phone: props.settings?.contact_phone || '+63 912 345 6789',
    vat_rate: props.settings?.vat_rate || 12,
    about_us_tagline: props.settings?.about_us_tagline || 'Your premier distributor of high-quality electrical supplies and industrial equipment in the Philippines.',
    about_us_history: props.settings?.about_us_history || 'Jubis Marketing was established in 2006 by founder Juberth Bisnan...',
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Settings saved successfully!')
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Global Settings | Admin" />
        <template #header>Global Configuration</template>

        <div class="max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Company Profile -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                        <BuildingOfficeIcon class="w-5 h-5 mr-2 text-gray-500" />
                        <h3 class="text-base font-bold text-gray-900">Company Profile</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="company_name" value="Company Name" />
                                <TextInput id="company_name" v-model="form.company_name" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel for="contact_email" value="Contact Email" />
                                <TextInput id="contact_email" v-model="form.contact_email" type="email" class="mt-1 block w-full" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="company_address" value="Official Address" />
                            <TextInput id="company_address" v-model="form.company_address" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="contact_phone" value="Contact Phone" />
                            <TextInput id="contact_phone" v-model="form.contact_phone" type="text" class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>

                <!-- Financial Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                        <ReceiptPercentIcon class="w-5 h-5 mr-2 text-gray-500" />
                        <h3 class="text-base font-bold text-gray-900">Financial Rules</h3>
                    </div>
                    <div class="p-6">
                        <div class="max-w-xs">
                            <InputLabel for="vat_rate" value="Default VAT Rate (%)" />
                            <TextInput id="vat_rate" v-model="form.vat_rate" type="number" min="0" max="100" class="mt-1 block w-full" />
                            <p class="mt-2 text-xs text-gray-500">This rate is automatically applied to all B2B Quotes and Invoices.</p>
                        </div>
                    </div>
                </div>

                <!-- Website Content (CMS) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2 text-gray-500" />
                        <h3 class="text-base font-bold text-gray-900">Website Content (About Us)</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <InputLabel for="about_us_tagline" value="Homepage Tagline" />
                            <TextInput id="about_us_tagline" v-model="form.about_us_tagline" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="about_us_history" value="Company History / Mission Statement" />
                            <textarea id="about_us_history" v-model="form.about_us_history" rows="5" class="mt-1 block w-full border-gray-300 focus:border-jubis-navy focus:ring-jubis-navy rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save Configuration</PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
