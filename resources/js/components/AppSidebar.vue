<script setup lang="ts">
import { index as dashboard } from '@/actions/App/Http/Controllers/DashboardController';
import { index as itemIndex } from '@/actions/App/Http/Controllers/ItemController';
import { index as questionIndex } from '@/actions/App/Http/Controllers/QuestionController';
import { index as userIndex } from '@/actions/App/Http/Controllers/UserController';

import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, LayoutGrid, QrCode, Settings2, User, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<any>();
const currentUrl = page.url;
const unreadCount = ref<number>(Number(page.props?.notifications?.unread_count ?? 0));
const pollHandle = ref<number | null>(null);
const initializedPoll = ref(false);
const seenIds = ref<Set<string>>(new Set());
const toasts = ref<Array<{ id: string; title: string; message: string; href?: string }>>([]);
const toastTimeouts = new Map<string, number>();
let audioContext: AudioContext | null = null;

watch(
    () => page.props?.notifications?.unread_count,
    (value) => {
        unreadCount.value = Number(value ?? 0);
    },
);
const dashboardMenu = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const setup = computed(() => [
    {
        title: 'Setups',
        icon: Settings2,
        isActive: [userIndex().url, questionIndex().url, itemIndex().url, '/admin/qr-batches', '/admin/notifications'].some((url) =>
            currentUrl.startsWith(url),
        ),
        items: [
            {
                title: 'Questions',
                href: questionIndex(),
                icon: '',
            },
            {
                title: 'Prizes',
                href: itemIndex(),
                icon: '',
            },
            {
                title: 'Users',
                href: userIndex(),
                icon: User,
            },
            {
                title: 'QR Batches',
                href: '/admin/qr-batches',
                icon: QrCode,
            },
            {
                title: 'Notifications',
                href: '/admin/notifications',
                icon: Bell,
                badge: unreadCount.value > 0 ? unreadCount.value : undefined,
            },
        ],
    },
]);

const syncNotifications = async () => {
    try {
        const response = await fetch('/api/admin/notifications/poll', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const data = await response.json();
        unreadCount.value = Number(data.unread_count ?? 0);

        const notifications = Array.isArray(data.notifications) ? data.notifications : [];
        const ids = notifications.map((notification: any) => String(notification.id));

        if (!initializedPoll.value) {
            seenIds.value = new Set(ids);
            initializedPoll.value = true;
            return;
        }

        const fresh = notifications.filter((notification: any) => !seenIds.value.has(String(notification.id)));
        ids.forEach((id: string) => seenIds.value.add(id));

        const readyAlert = fresh.find((notification: any) => notification?.data?.kind === 'qr_batch_ready');

        if (readyAlert) {
            pushToast({
                id: String(readyAlert.id),
                title: readyAlert.data?.title ?? 'QR Batch Ready',
                message: readyAlert.data?.message ?? 'A QR batch is ready to download.',
                href: readyAlert.data?.page_url,
            });
        }
    } catch {
        // Silent polling failure; next poll can recover.
    }
};

const dismissToast = (id: string) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);

    const timeout = toastTimeouts.get(id);
    if (timeout !== undefined) {
        window.clearTimeout(timeout);
        toastTimeouts.delete(id);
    }
};

const pushToast = (toast: { id: string; title: string; message: string; href?: string }) => {
    if (toasts.value.some((item) => item.id === toast.id)) {
        return;
    }

    toasts.value = [toast, ...toasts.value].slice(0, 4);
    playNotificationSound();

    const timeout = window.setTimeout(() => dismissToast(toast.id), 6000);
    toastTimeouts.set(toast.id, timeout);
};

const playNotificationSound = () => {
    try {
        const AudioCtx = window.AudioContext || (window as any).webkitAudioContext;
        if (!AudioCtx) return;

        audioContext ??= new AudioCtx();

        if (audioContext.state === 'suspended') {
            void audioContext.resume();
        }

        const now = audioContext.currentTime;
        const master = audioContext.createGain();
        master.gain.setValueAtTime(0.5, now);
        master.connect(audioContext.destination);

        // Sharp, noticeable 3-pulse alert (notification-style).
        const pulses = [
            { at: 0, freqA: 1244, freqB: 932, dur: 0.09 },
            { at: 0.14, freqA: 1244, freqB: 932, dur: 0.09 },
            { at: 0.33, freqA: 1568, freqB: 1174, dur: 0.15 },
        ];

        for (const pulse of pulses) {
            const start = now + pulse.at;
            const end = start + pulse.dur;

            const gain = audioContext.createGain();
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.exponentialRampToValueAtTime(0.75, start + 0.008);
            gain.gain.exponentialRampToValueAtTime(0.0001, end);
            gain.connect(master);

            const oscA = audioContext.createOscillator();
            oscA.type = 'square';
            oscA.frequency.setValueAtTime(pulse.freqA, start);
            oscA.connect(gain);
            oscA.start(start);
            oscA.stop(end);

            const oscB = audioContext.createOscillator();
            oscB.type = 'triangle';
            oscB.frequency.setValueAtTime(pulse.freqB, start);
            oscB.connect(gain);
            oscB.start(start);
            oscB.stop(end);
        }
    } catch {
        // Autoplay restrictions or unsupported browser; toast still shows.
    }
};

onMounted(() => {
    void syncNotifications();
    pollHandle.value = window.setInterval(() => {
        void syncNotifications();
    }, 5000);
});

onBeforeUnmount(() => {
    if (pollHandle.value !== null) {
        window.clearInterval(pollHandle.value);
    }

    toastTimeouts.forEach((timeout) => window.clearTimeout(timeout));
    toastTimeouts.clear();
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="setup" :dashboardMenu="dashboardMenu" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />

    <div class="pointer-events-none fixed top-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2">
        <div v-for="toast in toasts" :key="toast.id" class="pointer-events-auto rounded-lg border bg-background p-3 shadow-lg">
            <div class="flex items-start gap-3">
                <Bell class="mt-0.5 size-4 text-amber-500" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold">{{ toast.title }}</div>
                    <div class="text-sm text-muted-foreground">{{ toast.message }}</div>
                    <Link
                        v-if="toast.href"
                        :href="toast.href"
                        class="mt-2 inline-flex text-xs font-medium text-amber-600 hover:text-amber-700"
                        @click="dismissToast(toast.id)"
                    >
                        Open notifications
                    </Link>
                </div>
                <button
                    type="button"
                    class="rounded p-1 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="dismissToast(toast.id)"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>
    </div>
</template>
