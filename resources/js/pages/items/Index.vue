<script setup lang="ts">
import { create, destroy, edit } from '@/actions/App/Http/Controllers/ItemController';

import { Button } from '@/components/ui/button';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { Link } from '@inertiajs/vue3';
import { Edit2, PlusIcon, Trash2 } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    items: any;
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
                                    <TableHead class="h-fit py-3">Image</TableHead>
                                    <TableHead class="h-fit rounded-r-full py-3"> Action </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(item, index) in items" :key="item.id">
                                    <TableCell class="h-fit rounded-l-full py-2"> {{ index + 1 }} </TableCell>
                                    <TableCell class="h-fit py-2">{{ item.name }}</TableCell>
                                    <TableCell class="h-fit py-2">
                                        <img v-if="item.image" :src="`/storage/${item.image}`" alt="Item Image" class="h-10 w-10 object-cover" />
                                    </TableCell>

                                    <TableCell class="h-fit rounded-r-full py-2">
                                        <div class="flex gap-2">
                                            <Link :href="edit(item.id)">
                                                <Button
                                                    class="cursor-pointer rounded-full border-amber-400 bg-white text-amber-600 shadow-sm transition-all hover:bg-amber-50 hover:text-amber-700"
                                                >
                                                    <Edit2 class="size-4" />
                                                </Button>
                                            </Link>

                                            <Link :href="destroy(item.id)">
                                                <Button
                                                    class="cursor-pointer rounded-full border-red-400 bg-white text-red-600 shadow-sm transition-all hover:bg-red-50 hover:text-red-700"
                                                >
                                                    <Trash2 class="size-4" />
                                                </Button>
                                            </Link>
                                        </div>
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
