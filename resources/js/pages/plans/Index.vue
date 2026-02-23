<script setup lang="ts">
import { create, edit , destroy } from '@/actions/App/Http/Controllers/PlanController';

import { Button } from '@/components/ui/button';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { Edit2, PlusIcon, Trash2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    plans: any;
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
                                    <TableHead class="h-fit py-3">Display Name</TableHead>
                                    <TableHead class="h-fit py-3">Detail</TableHead>
                                    <TableHead class="h-fit rounded-r-full py-3"> Action </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(plan, index) in plans" :key="plan.id">
                                    <TableCell class="h-fit rounded-l-full py-2"> {{ index + 1 }} </TableCell>
                                    <TableCell class="h-fit py-2">{{ plan.name }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ plan.display_name }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ plan.detail }}</TableCell>

                                    <TableCell class="h-fit rounded-r-full py-2">
                                        <div class="flex gap-2">
                                            <Link :href="edit(plan.id)">
                                                <Button
                                                    class="cursor-pointer rounded-full border-amber-400 bg-white text-amber-600 shadow-sm transition-all hover:bg-amber-50 hover:text-amber-700"
                                                >
                                                    <Edit2 class="size-4" />
                                                </Button>
                                            </Link>

                                          <Link :href="destroy(plan.id)">
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
