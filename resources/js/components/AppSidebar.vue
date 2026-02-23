<script setup lang="ts">
import { index as dashboard } from '@/actions/App/Http/Controllers/DashboardController';
import { index as planIndex } from '@/actions/App/Http/Controllers/PlanController';
import { index as questionIndex } from '@/actions/App/Http/Controllers/QuestionController';
import { index as userIndex } from '@/actions/App/Http/Controllers/UserController';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, List, Settings2, User } from 'lucide-vue-next';
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

const setup: any = [
    {
        title: 'Setups',
        icon: Settings2,
        isActive: [userIndex().url, planIndex().url, questionIndex().url].some((url) => currentUrl.startsWith(url)),
        items: [
            {
                title: 'Questions',
                href: questionIndex(),
                icon: '',
            },
            {
                title: 'Users',
                href: userIndex(),
                icon: User,
            },
            {
                title: 'Plans',
                href: planIndex(),
                icon: List,
            },
        ],
    },
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
            <NavMain :items="setup" :dashboardMenu="dashboardMenu" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
