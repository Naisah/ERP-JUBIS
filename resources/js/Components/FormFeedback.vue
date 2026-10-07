<script setup>
import { usePage } from '@inertiajs/vue3';
const page = usePage();
</script>

<template>
    <div v-if="page.props.flash?.success" role="status" class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
        {{ page.props.flash.success }}
    </div>
    <div v-if="page.props.flash?.error" role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        {{ page.props.flash.error }}
    </div>
    <div v-if="Object.keys(page.props.errors || {}).length" role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-semibold">Please check the following:</p>
        <ul class="list-disc pl-5"><li v-for="(error, field) in page.props.errors" :key="field">{{ error }}</li></ul>
    </div>
</template>
