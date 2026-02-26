<script setup lang="ts">
import BasicSearch from '@/components/BasicSearch.vue';
import Paginator from '@/components/Paginator.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

type BatchRow = {
    id: number;
    status: string;
    base_url: string;
    size_mode: 'preset' | 'custom';
    size_mm: number;
    download_available: boolean;
    download_url: string | null;
    created_at: string | null;
};

type PageLink = { url: string | null; label: string; active: boolean };
type PaginationMeta = {
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
    last_page: number;
    links: PageLink[];
};

defineProps<{
    batches: {
        data: BatchRow[];
        meta: PaginationMeta;
    };
    search?: string;
}>();
</script>

<template>
    <AppLayout>
        <div class="m-5 grid gap-5">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-3 space-y-0">
                    <div>
                        <CardTitle>Generated QRs</CardTitle>
                        <CardDescription>Previously generated QR PDFs. Download again anytime.</CardDescription>
                    </div>
                    <Button as-child variant="outline">
                        <Link href="/admin/qr-batches">Back to Generator</Link>
                    </Button>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <BasicSearch url="/admin/qr-batches/history" :q="search ?? ''" placeholder="Search generated QRs..." />
                    <Table>
                        <TableHeader class="border-none bg-gray-100">
                            <TableRow class="border-none">
                                <TableHead class="h-fit rounded-l-full py-3">No.</TableHead>
                                <TableHead class="h-fit py-3">Status</TableHead>
                                <TableHead class="h-fit py-3">QR Size</TableHead>
                                <TableHead class="h-fit py-3">Base URL</TableHead>
                                <TableHead class="h-fit py-3">Created</TableHead>
                                <TableHead class="h-fit rounded-r-full py-3">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(batch, index) in batches.data" :key="batch.id">
                                <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                <TableCell class="h-fit py-2 uppercase">{{ batch.status }}</TableCell>
                                <TableCell class="h-fit py-2 whitespace-nowrap">
                                    {{ batch.size_mm }}mm
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <code class="block max-w-[320px] truncate rounded bg-muted px-2 py-1 text-xs">
                                        {{ batch.base_url }}
                                    </code>
                                </TableCell>
                                <TableCell class="h-fit py-2 whitespace-nowrap">{{ batch.created_at || '-' }}</TableCell>
                                <TableCell class="h-fit rounded-r-full py-2">
                                    <Button v-if="batch.download_available && batch.download_url" as-child size="sm" class="bg-emerald-600 text-white hover:bg-emerald-700">
                                        <a :href="batch.download_url">Download PDF</a>
                                    </Button>
                                    <span v-else class="text-xs text-muted-foreground">Unavailable</span>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="batches.data.length === 0">
                                <TableCell colspan="6" class="py-6 text-center text-muted-foreground">No generated QRs yet.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <Paginator :meta="batches.meta" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
