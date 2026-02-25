<script setup lang="ts">
import { index as dashboard } from '@/actions/App/Http/Controllers/DashboardController';
import { index as userIndex } from '@/actions/App/Http/Controllers/UserController';

import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, QrCode, Settings2, User } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<any>();
const currentUrl = page.url;
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
        isActive: [userIndex().url, '/admin/qrs', '/admin/qr-batches'].some((url) =>
            currentUrl.startsWith(url),
        ),
        items: [
            {
                title: 'Users',
                href: userIndex(),
                icon: User,
            },
            {
                title: 'QR Manager',
                href: '/admin/qrs',
                icon: QrCode,
            },
            {
                title: 'QR Batches',
                href: '/admin/qr-batches',
                icon: QrCode,
            },
            {
                title: 'Generated QRs',
                href: '/admin/qr-batches/history',
                icon: QrCode,
            },
        ],
    },
]);
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
</template>
