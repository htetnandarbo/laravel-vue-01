<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginationMeta = {
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
    last_page: number;
    links: PageLink[];
};

defineProps<{
    meta?: PaginationMeta | null;
}>();
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="flex flex-col gap-3 pt-2">
        <div class="text-xs text-muted-foreground">
            Showing {{ meta.from ?? 0 }}-{{ meta.to ?? 0 }} of {{ meta.total }}
        </div>

        <div class="flex flex-wrap gap-1">
            <template v-for="(link, index) in meta.links" :key="`${index}-${link.label}`">
                <span
                    v-if="!link.url"
                    class="rounded-md border px-3 py-1.5 text-sm text-muted-foreground"
                    :class="{ 'bg-muted font-medium text-foreground': link.active }"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="rounded-md border px-3 py-1.5 text-sm hover:bg-muted"
                    :class="{ 'bg-muted font-medium': link.active }"
                    preserve-scroll
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
