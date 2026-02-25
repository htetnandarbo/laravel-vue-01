<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from 'vue-sonner';
import { reactive, ref, watch } from 'vue';

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
const recentBatch = ref<BatchPayload | null>(null);

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

const submit = async () => {
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
            if (data.batch) recentBatch.value = data.batch;
            return;
        }

        recentBatch.value = data.batch;
        if (recentBatch.value?.download_available && recentBatch.value.download_url) {
            toast.success('Generating has finished');
            window.location.assign(recentBatch.value.download_url);
        } else {
            toast.success('Generating has finished');
        }
    } catch {
        apiError.value = 'Request failed. Please try again.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="m-5 grid max-w-4xl gap-5">
            <Card>
                <CardHeader>
                    <CardTitle>QR Batch Generator</CardTitle>
                    <CardDescription>Generate print-ready QR PDF and download it immediately.</CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="grid gap-5" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="base_url">Base URL</Label>
                            <Input id="base_url" v-model="form.base_url" type="text" placeholder="https://example.com/redeem/{token}" />
                            <p class="text-xs text-muted-foreground">Use <code>{token}</code> placeholder, or token will be appended.</p>
                            <InputError :message="fieldErrors.base_url?.[0]" />
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
                            <Button type="submit" :disabled="submitting" class="cursor-pointer bg-amber-500 text-white hover:bg-amber-600">
                                {{ submitting ? 'Generating...' : 'Generate QR' }}
                            </Button>
                            <span class="text-xs text-muted-foreground">Defaults: A4, margin 8mm, gap 4mm, 4x6 grid.</span>
                        </CardFooter>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="recentBatch && recentBatch.download_available && recentBatch.download_url">
                <CardHeader>
                    <CardTitle>Generating has finished</CardTitle>
                    <CardDescription>PDF has been downloaded automatically. You can download it again below.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3 text-sm">
                    <div class="grid grid-cols-2 gap-2">
                        <span class="text-muted-foreground">Generated ID</span>
                        <span class="font-medium">#{{ recentBatch.id }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <span class="text-muted-foreground">Layout</span>
                        <span class="font-medium">{{ recentBatch.page_format }} / {{ recentBatch.cols }}x{{ recentBatch.rows }} / {{ recentBatch.size_mm }}mm</span>
                    </div>
                    <div class="grid gap-2">
                        <span class="text-muted-foreground">Base URL</span>
                        <code class="rounded bg-muted px-2 py-1 text-xs break-all">{{ recentBatch.base_url }}</code>
                    </div>
                    <div class="pt-2">
                        <Button as-child class="bg-emerald-600 text-white hover:bg-emerald-700">
                            <a :href="recentBatch.download_url">Download PDF</a>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
