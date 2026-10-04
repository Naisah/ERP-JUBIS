<script setup>
import { Link } from '@inertiajs/vue3';
import { TruckIcon, CurrencyDollarIcon, ShieldCheckIcon, PhoneIcon, EnvelopeIcon, MapPinIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
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
                    <div class="flex-shrink-0 flex items-center cursor-pointer">
                        <img src="/images/logo.png" alt="Jubis Marketing Logo" class="h-20 w-auto object-contain drop-shadow-sm" />
                    </div>
                    
                    <!-- Desktop Menu -->
                    <nav class="hidden md:flex space-x-8 items-center font-semibold text-gray-700">
                        <Link href="/" class="text-jubis-red hover:text-red-800 transition">Home</Link>
                        
                        <!-- Products Link -->
                        <Link href="/products" class="hover:text-jubis-red flex items-center transition focus:outline-none">
                            Products
                            <ChevronDownIcon class="w-4 h-4 ml-1" stroke-width="2" />
                        </Link>
                        
                        <Link href="/about" class="hover:text-jubis-red transition">About Us</Link>
                        <Link href="/contact" class="hover:text-jubis-red transition">Contact Us</Link>
                    </nav>

                    <!-- Right Side CTA -->
                    <div class="hidden md:flex items-center space-x-6">
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
                </div>
            </div>
        </header>

        <!-- Flash Message Toast -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="$page.props.flash?.success" class="fixed bottom-4 right-4 z-50">
                <div class="bg-green-600 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
            </div>
        </Transition>
        
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="$page.props.flash?.error" class="fixed bottom-4 right-4 z-50">
                <div class="bg-red-600 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>
            </div>
        </Transition>

        <!-- Page Content -->
        <main class="flex-grow">
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
