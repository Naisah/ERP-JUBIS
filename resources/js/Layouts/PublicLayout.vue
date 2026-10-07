<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FormFeedback from '@/Components/FormFeedback.vue';
import { TruckIcon, CurrencyDollarIcon, ShieldCheckIcon, PhoneIcon, EnvelopeIcon, MapPinIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
const page = usePage();
const mobileMenuOpen = ref(false);
const currentPath = computed(() => page.url.split('?')[0]);
const navLinks = [
    { href: '/', label: 'Home' }, { href: '/products', label: 'Products' },
    { href: '/about', label: 'About Us' }, { href: '/contact', label: 'Contact Us' },
];
const isActive = (href) => currentPath.value === href || (href === '/products' && currentPath.value.startsWith('/products/'));
watch(() => page.url, () => { mobileMenuOpen.value = false; });
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-gray-800 font-sans">
        <!-- Top Utility Bar -->
        <div class="bg-jubis-navy text-white text-xs md:text-sm py-2 px-6 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span>Your Trusted Electrical Supplies Distributor</span>
            </div>
            <div class="hidden md:flex space-x-6">
                <span class="flex items-center"><TruckIcon class="w-4 h-4 mr-1.5" /> Quick Delivery Nationwide</span>
                <span class="flex items-center"><CurrencyDollarIcon class="w-4 h-4 mr-1.5" /> Competitive Pricing</span>
                <span class="flex items-center"><ShieldCheckIcon class="w-4 h-4 mr-1.5" /> Trusted Brands</span>
            </div>
        </div>

        <!-- Main Navigation -->
        <header class="bg-white shadow-sm border-b sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-24 items-center">
                    <!-- Logo -->
                    <Link href="/" aria-label="Jubis Marketing home" class="flex-shrink-0 flex items-center">
                        <img src="/images/logo.png" alt="Jubis Marketing Logo" class="h-16 sm:h-20 max-w-[50vw] w-auto object-contain drop-shadow-sm" />
                    </Link>
                    
                    <!-- Desktop Menu -->
                    <nav class="hidden xl:flex space-x-8 items-center font-semibold text-gray-700">
                        <Link href="/" :class="isActive('/') ? 'text-jubis-red' : 'hover:text-jubis-red transition'">Home</Link>
                        
                        <!-- Products Link -->
                        <Link href="/products" :class="isActive('/products') ? 'text-jubis-red' : 'hover:text-jubis-red transition focus:outline-none flex items-center'">
                            Products
                            
                        </Link>
                        
                        <Link href="/about" :class="isActive('/about') ? 'text-jubis-red' : 'hover:text-jubis-red transition'">About Us</Link>
                        <Link href="/contact" :class="isActive('/contact') ? 'text-jubis-red' : 'hover:text-jubis-red transition'">Contact Us</Link>
                    </nav>

                    <!-- Right Side CTA -->
                    <div class="hidden xl:flex items-center space-x-6">
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('cart.index')" class="text-gray-600 hover:text-jubis-navy font-medium text-sm flex items-center transition relative">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                </svg>
                                My Quote Cart
                            </Link>
                            <Link :href="route('dashboard')" class="bg-jubis-navy hover:bg-[#071126] text-white px-5 py-2.5 rounded-md font-bold shadow transition duration-150 transform hover:-translate-y-0.5">
                                My Dashboard
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="text-gray-500 hover:text-jubis-red font-medium text-sm transition focus:outline-none">
                                Log Out
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="text-gray-600 hover:text-jubis-navy font-medium text-sm transition">Client Login</Link>
                            <Link :href="route('login')" class="bg-jubis-red hover:bg-red-700 text-white px-5 py-2.5 rounded-md font-bold shadow transition duration-150 transform hover:-translate-y-0.5">
                                Request a Quote
                            </Link>
                        </template>
                    </div>
                    <button type="button" class="xl:hidden rounded-md border px-4 py-2 font-semibold" :aria-expanded="mobileMenuOpen" aria-controls="public-mobile-navigation" @click="mobileMenuOpen = !mobileMenuOpen">{{ mobileMenuOpen ? 'Close menu' : 'Menu' }}</button>
                </div>
                <nav v-if="mobileMenuOpen" id="public-mobile-navigation" aria-label="Mobile navigation" class="xl:hidden flex flex-col gap-3 border-t py-4" @keydown.esc="mobileMenuOpen = false">
                    <Link v-for="link in navLinks" :key="link.href" :href="link.href" :aria-current="isActive(link.href) ? 'page' : undefined" :class="isActive(link.href) ? 'text-jubis-red font-bold' : 'text-gray-700'" @click="mobileMenuOpen = false">{{ link.label }}</Link>
                    <template v-if="page.props.auth.user">
                        <Link :href="route('cart.index')">My Quote Cart</Link>
                        <Link :href="route('dashboard')">My Dashboard</Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-left">Log Out</Link>
                    </template>
                    <Link v-else :href="route('login')">Client Login / Request a Quote</Link>
                </nav>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto px-4 pt-4"><FormFeedback /></div>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-jubis-navy text-gray-300 py-12 border-t-4 border-jubis-gold">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Branding -->
                <div>
                    <div class="mb-4">
                        <img src="/images/logo_white.png" alt="Jubis Marketing" class="h-20 w-auto object-contain" />
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400">
                        Powering projects with quality products and dependable service.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><Link href="/" class="hover:text-jubis-gold transition">Home</Link></li>
                        <li><Link href="/products" class="hover:text-jubis-gold transition">Products</Link></li>
                        <li><Link href="/about" class="hover:text-jubis-gold transition">About Us</Link></li>
                        <li><Link href="/login" class="hover:text-jubis-gold transition">Request a Quote</Link></li>
                        <li><Link href="/contact" class="hover:text-jubis-gold transition">Contact Us</Link></li>
                    </ul>
                </div>

                <!-- Product Categories -->
                <div>
                    <h3 class="text-white font-bold mb-4">Product Categories</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><Link :href="route('products.index', { categories: 3 })" class="hover:text-jubis-gold transition">Circuit Protection</Link></li>
                        <li><Link :href="route('products.index', { categories: 1 })" class="hover:text-jubis-gold transition">Cables & Wires</Link></li>
                        <li><Link :href="route('products.index', { categories: 4 })" class="hover:text-jubis-gold transition">Lighting</Link></li>
                        <li><Link :href="route('products.index', { categories: 7 })" class="hover:text-jubis-gold transition">Wiring Accessories</Link></li>
                        <li><Link :href="route('products.index', { categories: 5 })" class="hover:text-jubis-gold transition">Industrial Connectors</Link></li>
                    </ul>
                </div>

                <!-- Contact Us -->
                <div>
                    <h3 class="text-white font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-start">
                            <PhoneIcon class="w-5 h-5 mr-3 flex-shrink-0 text-gray-500" />
                            <span>(02) 8801-1688<br>Viber: 0968-7045013</span>
                        </li>
                        <li class="flex items-start">
                            <EnvelopeIcon class="w-5 h-5 mr-3 flex-shrink-0 text-gray-500" />
                            <span>Jubismarketing@gmail.com</span>
                        </li>
                        <li class="flex items-start leading-tight">
                            <MapPinIcon class="w-5 h-5 mr-3 flex-shrink-0 text-gray-500" />
                            <span>22 Gonzales Compound, Almanza I,<br>Las Pinas City 1750, Philippines</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-6 border-t border-gray-700/50 text-xs text-center text-gray-500">
                &copy; {{ new Date().getFullYear() }} JUBIS Electrical Supplies & Industrial Equipment Distributor. All rights reserved.
            </div>
        </footer>
    </div>
</template>
