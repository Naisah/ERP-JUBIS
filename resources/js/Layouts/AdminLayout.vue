<script setup>
import { ref, watch } from 'vue';
import FormFeedback from '@/Components/FormFeedback.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    HomeIcon, 
    CubeIcon, 
    UsersIcon, 
    DocumentTextIcon,
    ChartBarIcon,
    ArrowLeftOnRectangleIcon,
    Cog6ToothIcon,
    BuildingStorefrontIcon,
    BanknotesIcon,
    TruckIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const menuOpen = ref(false);
watch(() => page.url, () => { menuOpen.value = false; });
const isCurrent = (href) => {
    const path = new URL(href, window.location.origin).pathname;
    const current = page.url.split('?')[0];
    return current === path || (path !== '/admin/dashboard' && current.startsWith(path + '/'));
};
const user = page.props.auth.user;
const role = user?.role || 'client';
const isSuper = role === 'super_admin' || role === 'admin';

const allNavigation = [
    { name: 'Overview', href: route('admin.dashboard'), icon: HomeIcon, current: route().current('admin.dashboard'), roles: ['finance', 'purchasing', 'warehouse', 'sales'] },
    { name: 'Inventory System', href: route('admin.products.index'), icon: CubeIcon, current: route().current('admin.products.*'), roles: ['warehouse', 'purchasing'] },
    { name: 'Purchase Orders', href: route('admin.purchase-orders.index'), icon: BuildingStorefrontIcon, current: route().current('admin.purchase-orders.*'), roles: ['purchasing'] },
    { name: 'Suppliers', href: route('admin.suppliers.index'), icon: TruckIcon, current: route().current('admin.suppliers.*'), roles: ['purchasing'] },
    { name: 'Quotation Manager', href: route('admin.quotes.index'), icon: DocumentTextIcon, current: route().current('admin.quotes.*'), roles: ['sales', 'finance'] },
    { name: 'Invoicing & Finance', href: route('admin.invoices.index'), icon: BanknotesIcon, current: route().current('admin.invoices.*'), roles: ['finance'] },
    { name: 'Logistics & Dispatch', href: route('admin.shipments.index'), icon: TruckIcon, current: route().current('admin.shipments.*'), roles: ['warehouse', 'purchasing'] },
    { name: 'User Management', href: route('admin.users.index'), icon: UsersIcon, current: route().current('admin.users.*'), roles: [] },
    { name: 'Reports & Analytics', href: route('admin.reports.index'), icon: ChartBarIcon, current: route().current('admin.reports.*'), roles: ['finance'] },
    { name: 'Returns / RMAs', href: route('admin.rmas.index'), icon: DocumentTextIcon, current: route().current('admin.rmas.*'), roles: ['finance'] },
    { name: 'Settings', href: route('admin.settings'), icon: Cog6ToothIcon, current: route().current('admin.settings'), roles: [] },
];

const navigation = allNavigation.filter(item => isSuper || item.roles.includes(role));
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        
        <!-- Admin Sidebar -->
        <div v-if="menuOpen" class="fixed inset-0 bg-black/40 z-20 lg:hidden" @click="menuOpen = false"></div>
        <div id="admin-navigation" :class="menuOpen ? 'flex' : 'hidden lg:flex'" class="fixed inset-y-0 left-0 lg:static w-72 shrink-0 bg-jubis-navy text-white flex-col shadow-xl z-30" @keydown.esc="menuOpen = false">
            <button class="lg:hidden p-3 text-right" @click="menuOpen = false">Close menu</button>
            <!-- Branding -->
            <div class="h-20 flex items-center px-8 border-b border-white/10 bg-[#071126]">
                <img src="/images/logo_white.png" alt="Jubis Admin" class="h-10 w-auto" />
                <span class="ml-3 font-bold tracking-widest text-sm text-gray-300 border-l border-gray-600 pl-3">ERP</span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Core Systems</p>
                
                <Link v-for="item in navigation" :key="item.name" :href="item.href" 
                    :class="[isCurrent(item.href) ? 'bg-jubis-red text-white shadow-md' : 'text-gray-300 hover:bg-white/10 hover:text-white', 'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200']">
                    <component :is="item.icon" :class="[isCurrent(item.href) ? 'text-white' : 'text-gray-400 group-hover:text-white', 'mr-3 flex-shrink-0 h-5 w-5 transition-colors']" aria-hidden="true" />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Profile Bottom -->
            <div class="p-4 border-t border-white/10 bg-[#071126]">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-jubis-gold flex items-center justify-center text-jubis-navy font-bold text-lg shadow-inner">
                            {{ user?.name?.charAt(0) || 'A' }}
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white">{{ user?.name || 'Administrator' }}</p>
                        <p class="text-xs mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="{
                                'bg-red-500/20 text-red-300': isSuper,
                                'bg-green-500/20 text-green-300': role === 'finance',
                                'bg-blue-500/20 text-blue-300': role === 'purchasing',
                                'bg-amber-500/20 text-amber-300': role === 'warehouse',
                                'bg-purple-500/20 text-purple-300': role === 'sales',
                                'bg-gray-500/20 text-gray-300': !['super_admin','admin','finance','purchasing','warehouse','sales'].includes(role)
                            }">
                                {{ role?.replace('_', ' ') || 'Admin' }}
                            </span>
                        </p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="mt-4 w-full flex items-center justify-center px-4 py-2 border border-white/20 rounded-lg text-xs font-medium text-gray-300 hover:bg-white/10 hover:text-white transition-colors">
                    <ArrowLeftOnRectangleIcon class="w-4 h-4 mr-2" />
                    Secure Logout
                </Link>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-20 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between gap-3 px-4 sm:px-8 z-10">
                <button class="lg:hidden border rounded px-3 py-2 text-sm" :aria-expanded="menuOpen" aria-controls="admin-navigation" @click="menuOpen = !menuOpen">Menu</button>
                <h1 class="text-lg sm:text-2xl font-extrabold text-jubis-navy tracking-tight">
                    <slot name="header">Dashboard</slot>
                </h1>
                
                <div class="hidden sm:flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                        System Online
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-[#F4F7FB] p-4 sm:p-8">
                <FormFeedback />
                <slot />
            </main>
        </div>
    </div>
</template>
