<script setup lang="ts">
import { create, edit , destroy } from '@/actions/App/Http/Controllers/UserController';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { EllipsisVerticalIcon, PlusIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';



defineProps<{
    users: any;
}>();
</script>

<template>
    <AppLayout>
        <div class="m-5">
            <div class="mb-5">
                <div class="flex justify-end gap-2">
                    <Link :href="create()">
                        <Button
                            class="cursor-pointer rounded-2xl bg-amber-500 text-white shadow-sm shadow-amber-50 transition-all hover:bg-amber-600 hover:shadow-sm hover:shadow-amber-300 sm:w-auto"
                            ><PlusIcon></PlusIcon>Add New
                        </Button>
                    </Link>
                </div>
            </div>
            <div class="grid gap-5 rounded-xl border p-5">
                <div>
                    <ScrollArea class="grid w-full grid-cols-1 overflow-auto">
                        <Table>
                            <TableHeader class="border-none bg-gray-100">
                                <TableRow class="border-none">
                                    <TableHead class="h-fit rounded-l-full py-3"> No </TableHead>
                                    <TableHead class="h-fit py-3">Name</TableHead>
                                    <TableHead class="h-fit py-3">Email</TableHead>
                                    <TableHead class="h-fit rounded-r-full py-3"> Action </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(user, index) in users" :key="user.id">
                                    <TableCell class="h-fit rounded-l-full py-2"> {{ index + 1 }} </TableCell>
                                    <TableCell class="h-fit py-2">{{ user.name }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ user.email }}</TableCell>

                                    <TableCell class="h-fit rounded-r-full py-2">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger>
                                                <EllipsisVerticalIcon class="size-4 cursor-pointer" />
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem>
                                                    <Link :href="edit(user.id)">Edit</Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem>
                                                    <Link :href="destroy(user.id)">Delete</Link>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <ScrollBar orientation="horizontal" />
                    </ScrollArea>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss" scoped></style>
