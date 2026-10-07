<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: { type: Array, default: () => [] } });
</script>
<template>
    <nav v-if="links.length > 3" aria-label="Pagination" class="my-4 flex flex-wrap justify-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <Link v-if="link.url" :href="link.url" :aria-current="link.active ? 'page' : undefined" class="rounded border px-3 py-2 text-sm" :class="link.active ? 'bg-jubis-navy text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" v-html="link.label" />
            <span v-else aria-disabled="true" class="rounded border bg-gray-50 px-3 py-2 text-sm text-gray-400" v-html="link.label"></span>
        </template>
    </nav>
</template>
