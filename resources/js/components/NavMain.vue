<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

defineProps<{
    items?: any;
    dashboardMenu: any;
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <SidebarMenuItem v-for="dashboardMenuItem in dashboardMenu" :key="dashboardMenuItem.title">
                <SidebarMenuButton as-child :is-active="urlIsActive(dashboardMenuItem.href, page.url)" :tooltip="dashboardMenuItem.title">
                    <Link :href="dashboardMenuItem.href">
                        <component :is="dashboardMenuItem.icon" />
                        <span>{{ dashboardMenuItem.title }}</span>
                        <span
                            v-if="dashboardMenuItem.badge"
                            class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-semibold text-white"
                        >
                            {{ dashboardMenuItem.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>

        <SidebarMenu>
            <Collapsible v-for="item in items" :key="item.title" as-child class="group/collapsible" :default-open="item.isActive">
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="item.title">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                            <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                <SidebarMenuSubButton as-child :is-active="urlIsActive(subItem.href, page.url)">
                                    <Link :href="subItem.href" preserve-scroll preserve-state>
                                        <span>{{ subItem.title }}</span>
                                        <span
                                            v-if="subItem.badge"
                                            class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-semibold text-white"
                                        >
                                            {{ subItem.badge }}
                                        </span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
