<script setup lang="ts">
// Shadcn ui imports
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { reactive } from 'vue';
import { EllipsisVerticalIcon } from 'lucide-vue-next';

type QrRow = {
    id: number;
    token: string;
    public_url: string;
    name: string | null;
    status: 'active' | 'inactive' | 'archived';
    questions_count: number;
    items_count: number;
    responses_count: number;
    wishes_count: number;
    created_at: string | null;
};

const props = defineProps<{ qrs: QrRow[] }>();

const form = useForm({
    name: '',
    status: 'active',
});
const qEdit = reactive<Record<number, { name: string; status: QrRow['status'] }>>(
    Object.fromEntries(
        props.qrs.map((qr) => [
            qr.id,
            {
                name: qr.name ?? '',
                status: qr.status,
            },
        ]),
    ),
);

const submit = () => form.post('/admin/qrs');
const saveQr = (id: number) =>
    router.patch(
        `/admin/qrs/${id}`,
        {
            name: qEdit[id]?.name ?? '',
            status: qEdit[id]?.status ?? 'active',
        },
        {
            preserveScroll: true,
            onSuccess: () => toast.success('QR updated'),
        },
    );

const removeQr = (id: number) => {
    if (!window.confirm('Delete this QR?')) return;

    router.delete(`/admin/qrs/${id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('QR deleted'),
    });
};

const copyUrl = async (url: string) => {
    try {
        await navigator.clipboard.writeText(url);
    } catch {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }
    toast.success('URL copied');
};
</script>

<template>
    <AppLayout>
        <div class="m-5 grid gap-6">
            <Card>
                <CardHeader>
                    <CardTitle>QR Admin Panel</CardTitle>
                    <CardDescription>Create a QR and manage its questions, items, stock, responses, and wishes.</CardDescription>
                </CardHeader>
                <CardContent>
                <form class="grid gap-4 md:grid-cols-3" @submit.prevent="submit">
                    <div class="grid gap-1">
                        <Label>Name</Label>
                        <Input v-model="form.name" placeholder="Optional QR name" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-1">
                        <Label>Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">active</SelectItem>
                                <SelectItem value="inactive">inactive</SelectItem>
                                <SelectItem value="archived">archived</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" :disabled="form.processing" class="bg-amber-500 text-white hover:bg-amber-600">
                            {{ form.processing ? 'Creating...' : 'Create QR' }}
                        </Button>
                    </div>
                </form>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="pt-6">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">QR List</h2>
                    <span class="text-sm text-muted-foreground">{{ qrs.length }} total</span>
                </div>
                <Table>
                    <TableHeader class="border-none bg-gray-100">
                        <TableRow class="border-none">
                            <TableHead class="h-fit rounded-l-full py-3">ID</TableHead>
                            <TableHead class="h-fit py-3">Name</TableHead>
                            <TableHead class="h-fit py-3">Token</TableHead>
                            <TableHead class="h-fit py-3">Status</TableHead>
                            <TableHead class="h-fit py-3">Counts</TableHead>
                            <TableHead class="h-fit py-3">Created</TableHead>
                            <TableHead class="h-fit rounded-r-full py-3">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="qr in qrs" :key="qr.id">
                                <TableCell class="h-fit rounded-l-full py-2">{{ qr.id }}</TableCell>
                                <TableCell class="h-fit py-2">
                                    <Input v-model="qEdit[qr.id].name" placeholder="Optional QR name" />
                                </TableCell>
                                <TableCell class="h-fit py-2 font-mono text-xs">{{ qr.token }}</TableCell>
                                <TableCell class="h-fit py-2">
                                    <Select v-model="qEdit[qr.id].status">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">active</SelectItem>
                                            <SelectItem value="inactive">inactive</SelectItem>
                                            <SelectItem value="archived">archived</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </TableCell>
                                <TableCell class="h-fit py-2 text-xs text-muted-foreground">
                                    Q: {{ qr.questions_count }} / Items: {{ qr.items_count }} / Resp: {{ qr.responses_count }} / Wishes: {{ qr.wishes_count }}
                                </TableCell>
                                <TableCell class="h-fit py-2">{{ qr.created_at || '-' }}</TableCell>
                                <TableCell class="h-fit rounded-r-full py-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger>
                                            <EllipsisVerticalIcon class="size-4 cursor-pointer" />
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent>

                                            <DropdownMenuItem>
                                                <Link :href="`/admin/qrs/${qr.id}`">Manage</Link>
                                            </DropdownMenuItem>

                                            <DropdownMenuItem @click="copyUrl(qr.public_url)">
                                                Copy URL
                                            </DropdownMenuItem>

                                            <DropdownMenuItem @click="saveQr(qr.id)">
                                                Save
                                            </DropdownMenuItem>

                                            <DropdownMenuItem @click="removeQr(qr.id)">
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="qrs.length === 0">
                                <TableCell colspan="7" class="py-6 text-center text-muted-foreground">No QRs created yet.</TableCell>
                            </TableRow>
                    </TableBody>
                </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
