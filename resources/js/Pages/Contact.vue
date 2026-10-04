<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { MapPinIcon, PhoneIcon, EnvelopeIcon, ClockIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <PublicLayout>
        <Head title="Contact Us | Jubis Marketing" />

        <!-- Header -->
        <div class="bg-jubis-navy text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">Contact Us</h1>
                <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">
                    Get in touch with our sales team for quotations, product inquiries, and account support.
                </p>
            </div>
        </div>

        <div class="bg-white py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                    
                    <!-- Contact Form -->
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-100">
                        <h2 class="text-2xl font-bold text-jubis-navy mb-6">Send us a Message</h2>
                        <form class="space-y-6" @submit.prevent="">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">First Name</label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Last Name</label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Company Name</label>
                                <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                                <input type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Message / Inquiry</label>
                                <textarea rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-jubis-navy focus:border-jubis-navy"></textarea>
                            </div>

                            <button type="button" class="w-full bg-jubis-red text-white font-bold py-3 px-4 rounded-md shadow hover:bg-red-700 transition">
                                Send Message
                            </button>
                        </form>
                    </div>

                    <!-- Contact Details -->
                    <div class="flex flex-col justify-center">
                        <h2 class="text-2xl font-bold text-jubis-navy mb-8">Direct Contact Information</h2>
                        
                        <div class="space-y-8">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                    <MapPinIcon class="w-6 h-6 text-jubis-red" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Head Office</h3>
                                    <p class="text-gray-600 mt-1">
                                        JUBIS MARKETING<br>
                                        22 Gonzales Compound, Almanza I,<br>
                                        Las Pinas City 1750, Philippines
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                    <PhoneIcon class="w-6 h-6 text-jubis-red" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Phone & Viber</h3>
                                    <p class="text-gray-600 mt-1 flex flex-col space-y-1">
                                        <span><span class="font-semibold text-gray-700">Tel:</span> (02) 8801-1688</span>
                                        <span><span class="font-semibold text-gray-700">Viber:</span> 0968-7045013</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                    <EnvelopeIcon class="w-6 h-6 text-jubis-red" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Email</h3>
                                    <p class="text-gray-600 mt-1">
                                        <a href="mailto:Jubismarketing@gmail.com" class="hover:text-jubis-red transition">Jubismarketing@gmail.com</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                    <ClockIcon class="w-6 h-6 text-jubis-red" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Business Hours</h3>
                                    <p class="text-gray-600 mt-1">
                                        Monday - Friday: 8:00 AM - 5:00 PM<br>
                                        Saturday & Sunday: Closed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </PublicLayout>
</template>
