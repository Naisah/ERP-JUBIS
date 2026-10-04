<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { UserIcon, EnvelopeIcon, LockClosedIcon, BuildingOfficeIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    company_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const emailError = ref('');

const submit = () => {
    emailError.value = '';
    
    // Check if email has a proper domain suffix (e.g., .com)
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (!emailRegex.test(form.email)) {
        emailError.value = 'Please enter a complete corporate email address ending in a valid domain (e.g., .com, .ph).';
        return;
    }

    // Custom Frontend Validation for Corporate Emails
    const freeEmailDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com', 'icloud.com'];
    const emailDomain = form.email.split('@')[1];

    if (emailDomain && freeEmailDomains.includes(emailDomain.toLowerCase())) {
        emailError.value = 'Please use a valid corporate email address (e.g. purchasing@yourcompany.com). Free email providers are not allowed for B2B accounts.';
        return; // Stop submission
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <PublicLayout>
        <Head title="Create an Account | Jubis Marketing" />

        <div class="min-h-[80vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl w-full bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-jubis-navy py-6 px-8 text-center sm:text-left flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold text-white tracking-wide">Create an Account</h2>
                        <p class="text-gray-300 text-sm mt-1">Sign up to request quotes and manage your orders</p>
                    </div>
                </div>
                
                <div class="p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Full Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700">Full Name</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <UserIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    </div>
                                    <input id="name" type="text" v-model="form.name" required autofocus autocomplete="name" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="Juan Dela Cruz" />
                                </div>
                                <p v-if="form.errors.name" class="mt-2 text-sm text-jubis-red">{{ form.errors.name }}</p>
                            </div>

                            <!-- Company -->
                            <div>
                                <label for="company_name" class="block text-sm font-semibold text-gray-700">Company / Business Name</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <BuildingOfficeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    </div>
                                    <input id="company_name" type="text" v-model="form.company_name" required class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="Acme Corporation" />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Required for B2B approval</p>
                                <p v-if="form.errors.company_name" class="mt-2 text-sm text-jubis-red">{{ form.errors.company_name }}</p>
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700">Corporate Email Address</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input id="email" type="email" v-model="form.email" required autocomplete="username" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="purchasing@company.com" />
                            </div>
                            <p v-if="emailError" class="mt-2 text-sm text-jubis-red font-semibold">{{ emailError }}</p>
                            <p v-else-if="form.errors.email" class="mt-2 text-sm text-jubis-red">{{ form.errors.email }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Password Input -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <LockClosedIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    </div>
                                    <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" required autocomplete="new-password" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 pr-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="••••••••" />
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <EyeIcon v-if="!showPassword" class="h-5 w-5" aria-hidden="true" />
                                        <EyeSlashIcon v-else class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-2 text-sm text-jubis-red">{{ form.errors.password }}</p>
                            </div>

                            <!-- Confirm Password Input -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <LockClosedIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    </div>
                                    <input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" required autocomplete="new-password" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 pr-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="••••••••" />
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" aria-hidden="true" />
                                        <EyeSlashIcon v-else class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-jubis-red">{{ form.errors.password_confirmation }}</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-jubis-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-red transition duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Footer link -->
                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <Link :href="route('login')" class="font-bold text-jubis-navy hover:text-jubis-gold transition">Sign in here</Link>
                    </p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
