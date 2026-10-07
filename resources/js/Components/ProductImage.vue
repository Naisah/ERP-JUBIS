<script setup>
import { ref, watch } from 'vue';
const props = defineProps({ src: String, alt: { type: String, default: '' } });
const failed = ref(false);
watch(() => props.src, () => { failed.value = false; });
</script>
<template>
    <img :src="!failed && src ? src : '/images/product-placeholder.svg'" :alt="alt" @error="failed = true" />
</template>
