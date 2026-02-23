<script setup lang="ts">
import { index as dashboard } from '@/actions/App/Http/Controllers/DashboardController';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: any[] = [
    {
        title: "Welcome back! Here's your system overview",
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs"> dashboard </AppLayout>
</template>
