<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';

type Defaults = {
    page_format: 'A4' | 'LETTER';
    margin_mm: number;
    gap_mm: number;
    cols: number;
    rows: number;
    size_mode: 'preset' | 'custom';
    size_preset: 'S' | 'M' | 'L' | 'XL';
    size_mm: number;
};

type BatchPayload = {
    id: number;
    quantity: number;
    status: string;
    base_url: string;
    page_format: 'A4' | 'LETTER';
    margin_mm: number;
    gap_mm: number;
    cols: number;
    rows: number;
    size_mode: 'preset' | 'custom';
    size_mm: number;
    pdf_path: string | null;
    progress_current: number;
    progress_total: number;
    progress_percent: number;
    status_message: string | null;
    download_available: boolean;
    download_url: string | null;
    created_at: string | null;
    updated_at: string | null;
    started_at: string | null;
    finished_at: string | null;
};

const props = defineProps<{
    defaults: Defaults;
    sizePresets: Record<'S' | 'M' | 'L' | 'XL', number>;
    initialBatch: BatchPayload | null;
}>();

const form = reactive({
    quantity: 100,
    base_url: '',
    page_format: props.defaults.page_format,
    margin_mm: props.defaults.margin_mm,
    gap_mm: props.defaults.gap_mm,
    cols: props.defaults.cols,
    rows: props.defaults.rows,
    size_mode: props.defaults.size_mode,
    size_preset: props.defaults.size_preset,
    size_mm: props.defaults.size_mm,
});

const submitting = ref(false);
const apiError = ref<string | null>(null);
const fieldErrors = ref<Record<string, string[]>>({});
const batch = ref<BatchPayload | null>(props.initialBatch ?? null);
const pollHandle = ref<number | null>(null);
const STALE_BATCH_MS = 3 * 60 * 1000;

watch(
    () => [form.size_mode, form.size_preset] as const,
    ([mode, preset]) => {
        if (mode === 'preset') {
            form.size_mm = props.sizePresets[preset];
        }
    },
    { immediate: true },
);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const stopPolling = () => {
    if (pollHandle.value !== null) {
        window.clearInterval(pollHandle.value);
        pollHandle.value = null;
    }
};

const startPolling = () => {
    stopPolling();

    if (!batch.value || batch.value.status === 'completed' || batch.value.status === 'failed') {
        return;
    }

    pollHandle.value = window.setInterval(async () => {
        if (!batch.value) {
            stopPolling();
            return;
        }
        await refreshBatch(batch.value.id);
    }, 2000);
};

const refreshBatch = async (id: number) => {
    try {
        const response = await fetch(`/api/admin/qr-batches/${id}`, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();
        if (response.ok && data.batch) {
            batch.value = data.batch;
            if (batch.value.status === 'completed' || batch.value.status === 'failed') {
                stopPolling();
            }
        }
    } catch {
        // Keep the last visible status; manual refresh can recover.
    }
};

const submit = async () => {
    if (isBlockingRun()) {
        apiError.value = `Please wait until batch #${batch.value?.id} reaches 100% before starting another batch.`;
        return;
    }

    submitting.value = true;
    apiError.value = null;
    fieldErrors.value = {};

    try {
        const response = await fetch('/api/admin/qr-batches', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                ...form,
                quantity: Number(form.quantity),
                margin_mm: Number(form.margin_mm),
                gap_mm: Number(form.gap_mm),
                cols: Number(form.cols),
                rows: Number(form.rows),
                size_mm: Number(form.size_mm),
            }),
        });

        const data = await response.json().catch(() => ({}));

        if (response.status === 422) {
            fieldErrors.value = data.errors ?? {};
            apiError.value = data.message ?? 'Validation failed.';
            return;
        }

        if (!response.ok) {
            apiError.value = data.message ?? 'Failed to generate QR batch.';
            if (data.batch) {
                batch.value = data.batch;
                startPolling();
            }
            return;
        }

        batch.value = data.batch;
        startPolling();
    } catch {
        apiError.value = 'Request failed. Please try again.';
    } finally {
        submitting.value = false;
    }
};

const isRunning = () => {
    return batch.value ? ['pending', 'processing'].includes(batch.value.status) : false;
};

const isStaleRunningBatch = () => {
    if (!batch.value || !isRunning() || !batch.value.updated_at) {
        return false;
    }

    const updatedAt = new Date(batch.value.updated_at).getTime();

    if (Number.isNaN(updatedAt)) {
        return false;
    }

    return Date.now() - updatedAt > STALE_BATCH_MS;
};

const isBlockingRun = () => {
    return isRunning() && !isStaleRunningBatch();
};

onBeforeUnmount(stopPolling);
onMounted(() => {
    if (batch.value) {
        startPolling();
    }
});
</script>

<template>
    <AppLayout>
        <div class="m-5 grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <Card>
                <CardHeader>
                    <CardTitle>QR Batch Generator</CardTitle>
                    <CardDescription>Generate print-ready QR PDFs and download a ZIP package.</CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="grid gap-5" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="base_url">Base URL</Label>
                            <Input id="base_url" v-model="form.base_url" type="text" placeholder="https://example.com/redeem/{token}" />
                            <p class="text-xs text-muted-foreground">Use <code>{token}</code> placeholder, or token will be appended.</p>
                            <InputError :message="fieldErrors.base_url?.[0]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="quantity">Quantity</Label>
                            <Input id="quantity" v-model="form.quantity" type="number" min="1" step="1" />
                            <InputError :message="fieldErrors.quantity?.[0]" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Page Format</Label>
                                <Select v-model="form.page_format">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select page format" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="A4">A4</SelectItem>
                                        <SelectItem value="LETTER">LETTER</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="fieldErrors.page_format?.[0]" />
                            </div>

                            <div class="grid gap-2">
                                <Label>Size Mode</Label>
                                <RadioGroup v-model="form.size_mode" class="grid grid-cols-2 gap-2">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm">
                                        <RadioGroupItem id="size-mode-preset" value="preset" />
                                        <span>Preset</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm">
                                        <RadioGroupItem id="size-mode-custom" value="custom" />
                                        <span>Custom</span>
                                    </label>
                                </RadioGroup>
                                <InputError :message="fieldErrors.size_mode?.[0]" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="margin_mm">Margin (mm)</Label>
                                <Input id="margin_mm" v-model="form.margin_mm" type="number" min="0" step="0.5" />
                                <InputError :message="fieldErrors.margin_mm?.[0]" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="gap_mm">Gap (mm)</Label>
                                <Input id="gap_mm" v-model="form.gap_mm" type="number" min="0" step="0.5" />
                                <InputError :message="fieldErrors.gap_mm?.[0]" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="cols">Columns</Label>
                                <Input id="cols" v-model="form.cols" type="number" min="1" step="1" />
                                <InputError :message="fieldErrors.cols?.[0]" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="rows">Rows</Label>
                                <Input id="rows" v-model="form.rows" type="number" min="1" step="1" />
                                <InputError :message="fieldErrors.rows?.[0]" />
                            </div>
                        </div>

                        <div v-if="form.size_mode === 'preset'" class="grid gap-2">
                            <Label>Size Preset</Label>
                            <Select v-model="form.size_preset">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select size preset" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="S">S (25mm)</SelectItem>
                                    <SelectItem value="M">M (30mm)</SelectItem>
                                    <SelectItem value="L">L (40mm)</SelectItem>
                                    <SelectItem value="XL">XL (50mm)</SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="text-xs text-muted-foreground">Effective size: {{ form.size_mm }}mm</p>
                            <InputError :message="fieldErrors.size_preset?.[0]" />
                        </div>

                        <div v-else class="grid gap-2">
                            <Label for="size_mm">Custom Size (mm)</Label>
                            <Input id="size_mm" v-model="form.size_mm" type="number" min="18" max="80" step="0.5" />
                            <p class="text-xs text-muted-foreground">Allowed range: 18mm to 80mm.</p>
                            <InputError :message="fieldErrors.size_mm?.[0]" />
                        </div>

                        <div v-if="apiError" class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                            {{ apiError }}
                        </div>

                        <CardFooter class="mt-1 flex flex-col items-start gap-2 px-0 pb-0">
                            <Button type="submit" :disabled="submitting || isBlockingRun()" class="cursor-pointer bg-amber-500 text-white hover:bg-amber-600">
                                {{ submitting ? 'Queueing...' : 'Queue Batch Generation' }}
                            </Button>
                            <span class="text-xs text-muted-foreground">
                                {{
                                    isBlockingRun()
                                        ? 'Wait for the current batch to finish before queueing another.'
                                        : isStaleRunningBatch()
                                          ? 'Previous batch looks stuck (no updates for 3+ minutes). You can queue a new batch now.'
                                          : 'Defaults: A4, margin 8mm, gap 4mm, 4x6 grid.'
                                }}
                            </span>
                        </CardFooter>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-2 space-y-0">
                    <div>
                        <CardTitle>Batch Status</CardTitle>
                        <CardDescription>Track generation and download the ZIP package when ready.</CardDescription>
                    </div>
                    <Button v-if="batch" type="button" variant="outline" class="cursor-pointer" @click="refreshBatch(batch.id)">Refresh</Button>
                </CardHeader>

                <CardContent>
                    <div v-if="!batch" class="text-sm text-muted-foreground">No batch generated yet.</div>

                    <div v-else class="grid gap-3 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <span class="text-muted-foreground">Batch ID</span>
                            <span class="font-medium">{{ batch.id }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <span class="text-muted-foreground">Status</span>
                            <span class="font-medium uppercase">{{ batch.status }}</span>
                        </div>
                        <div v-if="isStaleRunningBatch()" class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            This batch appears stuck. If the queue worker crashed or is running old code, restart `php artisan queue:work`.
                        </div>
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-muted-foreground">Progress</span>
                                <span class="font-medium">{{ batch.progress_percent }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all duration-300"
                                    :style="{ width: `${Math.max(0, Math.min(100, batch.progress_percent))}%` }"
                                />
                            </div>
                            <div class="flex items-center justify-between gap-2 text-xs text-muted-foreground">
                                <span>{{ batch.status_message || (isRunning() ? 'Working...' : 'Idle') }}</span>
                                <span v-if="batch.progress_total > 0">{{ batch.progress_current }} / {{ batch.progress_total }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <span class="text-muted-foreground">Quantity</span>
                            <span class="font-medium">{{ batch.quantity }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <span class="text-muted-foreground">Layout</span>
                            <span class="font-medium">{{ batch.page_format }} / {{ batch.cols }}x{{ batch.rows }} / {{ batch.size_mm }}mm</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <span class="text-muted-foreground">Margins / Gap</span>
                            <span class="font-medium">{{ batch.margin_mm }}mm / {{ batch.gap_mm }}mm</span>
                        </div>
                        <div class="grid gap-2">
                            <span class="text-muted-foreground">Base URL</span>
                            <code class="rounded bg-muted px-2 py-1 text-xs break-all">{{ batch.base_url }}</code>
                        </div>

                        <div class="pt-2">
                            <a
                                v-if="batch.download_available && batch.download_url"
                                :href="batch.download_url"
                                class="inline-flex h-9 items-center rounded-md bg-emerald-600 px-4 text-sm font-medium text-white hover:bg-emerald-700"
                            >
                                Download ZIP
                            </a>
                            <div v-else class="text-xs text-muted-foreground">
                                {{ isRunning() ? 'Generation is running in queue. Keep this page open or refresh later.' : 'Download will appear when generation is complete.' }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
