<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    CubeIcon, 
    TruckIcon, 
    ShieldCheckIcon, 
    UserGroupIcon,
    BoltIcon,
    WrenchIcon,
    LightBulbIcon,
    CpuChipIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Home | Jubis Marketing" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative bg-gray-900 text-white h-[550px] flex items-center overflow-hidden">
            <!-- Background Image Placeholder (Warehouse) -->
            <div class="absolute inset-0 z-0 opacity-40 bg-[url('https://images.unsplash.com/photo-1586528116311-ad8ed7c663be?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center"></div>
            <!-- Dark Gradient Overlay (Left to Right) -->
            <div class="absolute inset-0 z-10 bg-gradient-to-r from-jubis-navy via-jubis-navy/80 to-transparent"></div>
            
            <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-2xl">
                    <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight leading-tight mb-6 text-white">
                        Powering Projects.<br />Connecting Possibilities.
                    </h1>
                    <p class="text-lg md:text-xl text-gray-300 mb-10 leading-relaxed border-l-4 border-jubis-red pl-4">
                        Jubis is your reliable partner for quality electrical supplies and exceptional service. We distribute a comprehensive range of industrial and commercial equipment.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <Link href="/products" class="bg-jubis-red hover:bg-red-700 text-white font-bold py-3.5 px-8 rounded transition duration-200 text-center shadow-lg inline-flex items-center justify-center">
                            Explore Products <ArrowRightIcon class="w-5 h-5 ml-2" stroke-width="2.5" />
                        </Link>
                        <Link href="/login" class="bg-transparent border border-white hover:bg-white hover:text-jubis-navy text-white font-bold py-3.5 px-8 rounded transition duration-200 text-center inline-flex items-center justify-center">
                            Request a Quote <ArrowRightIcon class="w-5 h-5 ml-2" stroke-width="2.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Value Proposition Bar -->
        <section class="bg-jubis-navy text-white py-8 border-b-4 border-jubis-red">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-2 md:space-y-0 md:space-x-4">
                        <div class="text-jubis-red p-1 bg-white/5 rounded-lg"><CubeIcon class="w-10 h-10" stroke-width="1.5" /></div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Wide Product Range</h3>
                            <p class="text-gray-400 text-sm">Everything you need under one roof.</p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-2 md:space-y-0 md:space-x-4">
                        <div class="text-jubis-red p-1 bg-white/5 rounded-lg"><TruckIcon class="w-10 h-10" stroke-width="1.5" /></div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Fast & Reliable Delivery</h3>
                            <p class="text-gray-400 text-sm">On-time delivery, every time.</p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-2 md:space-y-0 md:space-x-4">
                        <div class="text-jubis-red p-1 bg-white/5 rounded-lg"><ShieldCheckIcon class="w-10 h-10" stroke-width="1.5" /></div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Quality You Can Trust</h3>
                            <p class="text-gray-400 text-sm">Genuine products from trusted brands.</p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-2 md:space-y-0 md:space-x-4">
                        <div class="text-jubis-red p-1 bg-white/5 rounded-lg"><UserGroupIcon class="w-10 h-10" stroke-width="1.5" /></div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Dedicated Support</h3>
                            <p class="text-gray-400 text-sm">Our team is here to help you succeed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Section -->
        <section class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <p class="text-jubis-red font-bold uppercase tracking-widest text-sm mb-3">Our Products</p>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-jubis-navy mb-4">High-Quality Electrical Supplies for Every Need</h2>
                    <p class="text-gray-500 text-lg">
                        From wiring accessories to industrial equipment, we supply a comprehensive range of electrical products for residential, commercial, and industrial projects.
                    </p>
                </div>

                <!-- 5 Category Cards (Based on Mockup) -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    
                    <!-- Card 1 -->
                    <Link :href="route('products.index', { categories: 1 })" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group cursor-pointer">
                        <div class="h-48 bg-gray-100 p-4 flex items-center justify-center relative overflow-hidden text-gray-400 group-hover:text-jubis-navy transition-colors duration-300">
                            <BoltIcon class="w-16 h-16 transform group-hover:scale-110 transition-transform duration-300" stroke-width="1" />
                        </div>
                        <div class="p-6 flex-grow relative bg-white">
                            <h3 class="font-bold text-jubis-navy text-lg mb-2">Cables & Wires</h3>
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">High-quality cables for every installation.</p>
                            <div class="absolute bottom-6 right-6 text-jubis-red font-bold transform group-hover:translate-x-2 transition-transform">
                                <ArrowRightIcon class="w-5 h-5" stroke-width="2.5" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 2 -->
                    <Link :href="route('products.index', { categories: 3 })" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group cursor-pointer">
                        <div class="h-48 bg-gray-100 p-4 flex items-center justify-center relative overflow-hidden text-gray-400 group-hover:text-jubis-navy transition-colors duration-300">
                            <ShieldCheckIcon class="w-16 h-16 transform group-hover:scale-110 transition-transform duration-300" stroke-width="1" />
                        </div>
                        <div class="p-6 flex-grow relative bg-white">
                            <h3 class="font-bold text-jubis-navy text-lg mb-2">Circuit Protection</h3>
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">MCBs, MCCBs, RCCBs & protection devices.</p>
                            <div class="absolute bottom-6 right-6 text-jubis-red font-bold transform group-hover:translate-x-2 transition-transform">
                                <ArrowRightIcon class="w-5 h-5" stroke-width="2.5" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 3 -->
                    <Link :href="route('products.index', { categories: 7 })" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group cursor-pointer">
                        <div class="h-48 bg-gray-100 p-4 flex items-center justify-center relative overflow-hidden text-gray-400 group-hover:text-jubis-navy transition-colors duration-300">
                            <WrenchIcon class="w-16 h-16 transform group-hover:scale-110 transition-transform duration-300" stroke-width="1" />
                        </div>
                        <div class="p-6 flex-grow relative bg-white">
                            <h3 class="font-bold text-jubis-navy text-lg mb-2">Wiring Accessories</h3>
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">Switches, sockets, plates & wiring essentials.</p>
                            <div class="absolute bottom-6 right-6 text-jubis-red font-bold transform group-hover:translate-x-2 transition-transform">
                                <ArrowRightIcon class="w-5 h-5" stroke-width="2.5" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 4 -->
                    <Link :href="route('products.index', { categories: 4 })" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group cursor-pointer">
                        <div class="h-48 bg-gray-100 p-4 flex items-center justify-center relative overflow-hidden text-gray-400 group-hover:text-jubis-navy transition-colors duration-300">
                            <LightBulbIcon class="w-16 h-16 transform group-hover:scale-110 transition-transform duration-300" stroke-width="1" />
                        </div>
                        <div class="p-6 flex-grow relative bg-white">
                            <h3 class="font-bold text-jubis-navy text-lg mb-2">Lighting Solutions</h3>
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">Indoor, outdoor & industrial lighting solutions.</p>
                            <div class="absolute bottom-6 right-6 text-jubis-red font-bold transform group-hover:translate-x-2 transition-transform">
                                <ArrowRightIcon class="w-5 h-5" stroke-width="2.5" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 5 -->
                    <Link :href="route('products.index', { categories: 2 })" class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col group cursor-pointer">
                        <div class="h-48 bg-gray-100 p-4 flex items-center justify-center relative overflow-hidden text-gray-400 group-hover:text-jubis-navy transition-colors duration-300">
                            <CpuChipIcon class="w-16 h-16 transform group-hover:scale-110 transition-transform duration-300" stroke-width="1" />
                        </div>
                        <div class="p-6 flex-grow relative bg-white">
                            <h3 class="font-bold text-jubis-navy text-lg mb-2">Electrical Control</h3>
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">Contactors, relays, timers & control components.</p>
                            <div class="absolute bottom-6 right-6 text-jubis-red font-bold transform group-hover:translate-x-2 transition-transform">
                                <ArrowRightIcon class="w-5 h-5" stroke-width="2.5" />
                            </div>
                        </div>
                    </Link>

                </div>
            </div>
        </section>

        <!-- Stats Bar -->
        <section class="bg-white py-16 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-gray-100">
                    <div class="flex flex-col items-center">
                        <span class="text-4xl font-extrabold text-jubis-red mb-2">10,000+</span>
                        <span class="text-gray-500 font-semibold uppercase tracking-wider text-xs">Products</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-4xl font-extrabold text-jubis-red mb-2">500+</span>
                        <span class="text-gray-500 font-semibold uppercase tracking-wider text-xs">Trusted Brands</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-4xl font-extrabold text-jubis-red mb-2">2,000+</span>
                        <span class="text-gray-500 font-semibold uppercase tracking-wider text-xs">Satisfied Customers</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-4xl font-extrabold text-jubis-red mb-2">Fast</span>
                        <span class="text-gray-500 font-semibold uppercase tracking-wider text-xs">Nationwide Delivery</span>
                    </div>
                </div>
            </div>
        </section>

    </PublicLayout>
</template>
