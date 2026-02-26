<script setup lang="ts">
import { index as dashboard } from '@/actions/App/Http/Controllers/DashboardController';
import { index as userIndex } from '@/actions/App/Http/Controllers/UserController';

import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, QrCode, User } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const dashboardMenu = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: userIndex(),
        icon: User,
    },
    {
        title: 'QR',
        href: '/admin/qrs',
        icon: QrCode,
    },
    {
        title: 'QR Generator',
        href: '/admin/qr-batches/history',
        icon: QrCode,
    }
];

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
            <NavMain :dashboardMenu="dashboardMenu" />
        </SidebarContent>

    <SidebarFooter>
        <NavUser />
    </SidebarFooter>
</Sidebar>
<slot />
</template>
