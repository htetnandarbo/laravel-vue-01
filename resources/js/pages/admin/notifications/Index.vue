<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

type NotificationItem = {
    id: string;
    type: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string | null;
};

const props = defineProps<{
    notifications: NotificationItem[];
}>();

const items = ref<NotificationItem[]>(props.notifications ?? []);
const loading = ref(false);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const markRead = async (id: string) => {
    try {
        const response = await fetch(`/api/admin/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        items.value = items.value.map((item) => (item.id === id ? { ...item, read_at: new Date().toISOString() } : item));
    } catch {
        // no-op
    }
};

const markAllRead = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const now = new Date().toISOString();
        items.value = items.value.map((item) => ({ ...item, read_at: item.read_at ?? now }));
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="m-5">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-3 space-y-0">
                    <div>
                        <CardTitle>Notifications</CardTitle>
                        <CardDescription>Queue completion alerts and system notices for admin users.</CardDescription>
                    </div>
                    <Button variant="outline" class="cursor-pointer" :disabled="loading" @click="markAllRead">Mark all read</Button>
                </CardHeader>

                <CardContent>
                    <div v-if="items.length === 0" class="text-sm text-muted-foreground">No notifications yet.</div>

                    <div v-else class="grid gap-3">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="rounded-lg border p-4"
                            :class="!item.read_at ? 'border-amber-300 bg-amber-50/60' : ''"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="grid gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold">{{ item.data?.title ?? item.type }}</span>
                                        <span v-if="!item.read_at" class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-semibold text-white">NEW</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">{{ item.data?.message ?? '-' }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                        <span v-if="item.created_at">{{ new Date(item.created_at).toLocaleString() }}</span>
                                        <span v-if="item.data?.qr_batch_id">Batch #{{ item.data.qr_batch_id }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a
                                        v-if="item.data?.download_url"
                                        :href="item.data.download_url"
                                        class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-xs font-medium text-white hover:bg-emerald-700"
                                    >
                                        Download ZIP
                                    </a>
                                    <Link
                                        v-if="item.data?.page_url"
                                        :href="item.data.page_url"
                                        class="inline-flex h-8 items-center rounded-md border px-3 text-xs font-medium hover:bg-muted"
                                    >
                                        Open
                                    </Link>
                                    <Button
                                        v-if="!item.read_at"
                                        size="sm"
                                        variant="outline"
                                        class="cursor-pointer"
                                        @click="markRead(item.id)"
                                    >
                                        Mark read
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
