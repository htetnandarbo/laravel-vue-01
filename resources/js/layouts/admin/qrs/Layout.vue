<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Link } from '@inertiajs/vue3';

defineProps<{
    qr: {
        id: number;
        name: string | null;
        token: string;
        status: string;
    };
    section: 'questions' | 'items' | 'stock' | 'responses' | 'wishes' | 'pins' | 'notification';
}>();

const navItems = [
    { key: 'questions', label: 'Questions' },
    { key: 'items', label: 'Items' },
    { key: 'stock', label: 'Stock Transactions' },
    { key: 'responses', label: 'Responses' },
    { key: 'wishes', label: 'Wishes' },
    { key: 'pins', label: 'Pins' },
    { key: 'notification', label: 'Notification' },
] as const;
</script>

<template>
    <div class="px-4 py-6">
        
        <Heading :title="qr.name || `QR #${qr.id}`" :description="`Token: ${qr.token} | Status: ${qr.status}`" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button variant="ghost" class="mb-2 w-full justify-start text-amber-700" as-child>
                        <Link href="/admin/qrs">Back to QR list</Link>
                    </Button>
                    <Button
                        v-for="item in navItems"
                        :key="item.key"
                        variant="ghost"
                        class="w-full justify-start"
                        :class="{ 'bg-muted': section === item.key }"
                        as-child
                    >
                        <Link :href="`/admin/qrs/${qr.id}/${item.key}`">
                            {{ item.label }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-4xl">
                <section class="space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
