<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ShieldCheckIcon, HandThumbUpIcon, GlobeAsiaAustraliaIcon } from '@heroicons/vue/24/outline';

const brands = [
    'AB Chance', 'ABB', 'AKARI', 'Allen Bradley', 'Andeli', 'Belden', 'Blackstone', 'Bosch', 'Bussman', 'Butterfly', 'Camsco', 'CEE', 'Chint', 'Colombia', 'Cooper', 'Daiden', 'DAYCO', 'DCA', 'Delco', 'Delton', 'Dewalt', 'Duraflex', 'EUROLUX', 'FAFNIR', 'FIREFLY', 'Fuji', 'Fuji Belt', 'FYH', 'GATES', 'GE', 'Gewiss', 'Gould Shawmut', 'Hitachi', 'Hossoni', 'Hoyoma', 'Hypertech', 'IKO', 'INA', 'Kasuga', 'Kawasaki', 'Koyo', 'Link Belt', 'Littel Fuse', 'LS', 'Makita', 'Mcgraw', 'Meiji', 'Mersen', 'Nachi', 'NBR', 'NSK', 'NTN', 'NXLED', 'Ohio Brass', 'OMNI', 'OMRON', 'OPPLE', 'Orion', 'OSRAM', 'Panther', 'PCE', 'Phelps Dodge', 'Philflex', 'PHILIPS', 'Powercom', 'Powerhouse', 'S&C', 'Schneider Electric', 'Siemens', 'Simonds', 'SKF', 'Skil', 'Stable Power', 'Stanley', 'Timken', 'Wixim', 'Zebra'
];
</script>

<template>
    <PublicLayout>
        <Head title="About Us | Jubis Marketing" />

        <!-- Header -->
        <div class="bg-jubis-navy text-white py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">About Jubis Marketing</h1>
                <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">
                    {{ $page.props.settings?.about_us_tagline || 'Your premier distributor of high-quality electrical supplies and industrial equipment in the Philippines.' }}
                </p>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-extrabold text-jubis-navy mb-6">Who We Are</h2>
                        <div class="space-y-4 text-gray-600 leading-relaxed whitespace-pre-wrap">
                            {{ $page.props.settings?.about_us_history || 'Jubis Marketing was established in 2006 by founder Juberth Bisnan, who created the company name by combining his first and last name.\n\nOver the past 20 years, it has evolved from a small retail venture into a trusted nationwide supplier of heavy-duty electrical equipment, servicing major Philippine industries, hotels, and commercial establishments.' }}
                        </div>
                    </div>
                    <div class="bg-gray-100 rounded-xl h-80 flex items-center justify-center p-8 text-center border-4 border-gray-50 border-t-jubis-gold shadow-inner">
                        <p class="text-gray-400 font-bold uppercase tracking-widest">[Warehouse / Office Image]</p>
                    </div>
                </div>

                <div class="mt-24">
                    <h2 class="text-center text-3xl font-extrabold text-jubis-navy mb-12">Our Commitment</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-100">
                            <ShieldCheckIcon class="w-12 h-12 mx-auto text-jubis-red mb-4" />
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Quality Assurance</h3>
                            <p class="text-gray-600 text-sm">We strictly distribute genuine, industry-compliant materials from trusted global and local brands to ensure your projects remain safe and operational.</p>
                        </div>
                        <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-100">
                            <GlobeAsiaAustraliaIcon class="w-12 h-12 mx-auto text-jubis-red mb-4" />
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Nationwide Reach</h3>
                            <p class="text-gray-600 text-sm">Logistics matter. We work with reliable shipping partners to get heavy industrial equipment and bulk supplies delivered anywhere in the Philippines.</p>
                        </div>
                        <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-100">
                            <HandThumbUpIcon class="w-12 h-12 mx-auto text-jubis-red mb-4" />
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Dedicated Support</h3>
                            <p class="text-gray-600 text-sm">Our B2B platform and sales representatives offer personalized quotation processing, flexible credit terms, and reliable post-sales support.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brands We Carry -->
        <div class="bg-gray-50 py-16 md:py-24 border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-extrabold text-jubis-navy mb-4">Brands We Carry</h2>
                <p class="text-gray-500 mb-12 max-w-2xl mx-auto">We partner with the world's leading manufacturers to bring you top-tier industrial equipment and electrical supplies.</p>
                
                <div class="flex flex-wrap justify-center gap-3">
                    <span v-for="brand in brands" :key="brand" class="bg-white border border-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-full shadow-sm text-sm hover:border-jubis-gold hover:text-jubis-navy transition-colors cursor-default">
                        {{ brand }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="bg-jubis-red text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col md:flex-row justify-between items-center">
                <h2 class="text-2xl font-bold mb-4 md:mb-0">Ready to source materials for your next project?</h2>
                <div class="space-x-4">
                    <a href="/register" class="inline-block bg-white text-jubis-red font-bold py-3 px-6 rounded shadow hover:bg-gray-100 transition">Apply for client account</a>
                    <a href="/contact" class="inline-block border-2 border-white text-white font-bold py-3 px-6 rounded hover:bg-white hover:text-jubis-red transition">Contact Us</a>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
