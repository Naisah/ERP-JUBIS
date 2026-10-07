<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { LockClosedIcon, EnvelopeIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <PublicLayout>
        <Head title="Log In | Jubis Marketing" />

        <div class="min-h-[75vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-jubis-navy py-6 text-center">
                    <h2 class="text-2xl font-extrabold text-white tracking-wide">Log In</h2>
                    <p class="text-gray-300 text-sm mt-1">Sign in to request quotes and manage orders</p>
                </div>
                
                <div class="p-8">
                    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="purchasing@company.com" />
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-sm text-jubis-red">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <LockClosedIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" required autocomplete="current-password" class="focus:ring-jubis-navy focus:border-jubis-navy block w-full pl-10 pr-10 sm:text-sm border-gray-300 rounded-md py-3" placeholder="********" />
                                <button type="button" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <EyeIcon v-if="!showPassword" class="h-5 w-5" aria-hidden="true" />
                                    <EyeSlashIcon v-else class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-2 text-sm text-jubis-red">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember" type="checkbox" v-model="form.remember" class="h-4 w-4 text-jubis-navy focus:ring-jubis-navy border-gray-300 rounded cursor-pointer" />
                                <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer">Remember me</label>
                            </div>

                            <div class="text-sm" v-if="canResetPassword">
                                <Link :href="route('password.request')" class="font-semibold text-jubis-navy hover:text-jubis-gold transition">
                                    Forgot password?
                                </Link>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-jubis-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jubis-red transition duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                                Log In
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Footer link -->
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <Link :href="route('register')" class="font-bold text-jubis-navy hover:text-jubis-gold transition">Sign up here</Link>
                    </p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
