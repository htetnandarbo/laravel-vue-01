<script setup lang="ts">
import BasicSearch from '@/components/BasicSearch.vue';
import InputError from '@/components/InputError.vue';
import Paginator from '@/components/Paginator.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{
    qr: any;
    questionTypes: string[];
    section: 'questions' | 'items' | 'stock' | 'responses' | 'wishes' | 'pins';
}>();

const currentTab = props.section;
const pagedRows = (value: any) => (value && Array.isArray(value.data) ? value.data : []);
const sectionSearchUrl = computed(() => (typeof window !== 'undefined' ? window.location.pathname : ''));

const qForm = useForm({ label: '', type: 'text', is_required: false, sort_order: 0, options_text: '' });
const iForm = useForm({ name: '', balance_stock: '0' });
const pinForm = useForm({ count: '10' });
const qEdit = reactive<Record<number, any>>({});
const iEdit = reactive<Record<number, any>>({});
const questionDeleteId = ref<number | null>(null);
const itemDeleteId = ref<number | null>(null);

const syncQuestionEdits = (questions: any[]) => {
    const nextIds = new Set((questions || []).map((q) => Number(q.id)));

    for (const key of Object.keys(qEdit)) {
        if (!nextIds.has(Number(key))) {
            delete qEdit[Number(key)];
        }
    }

    for (const q of questions || []) {
        qEdit[q.id] = {
            label: qEdit[q.id]?.label ?? q.label,
            type: qEdit[q.id]?.type ?? q.type,
            is_required: qEdit[q.id]?.is_required ?? q.is_required,
            sort_order: qEdit[q.id]?.sort_order ?? q.sort_order,
            options_text: qEdit[q.id]?.options_text ?? (q.options || []).join(', '),
        };
    }
};

const syncItemEdits = (items: any[]) => {
    const nextIds = new Set((items || []).map((i) => Number(i.id)));

    for (const key of Object.keys(iEdit)) {
        if (!nextIds.has(Number(key))) {
            delete iEdit[Number(key)];
        }
    }

    for (const i of items || []) {
        iEdit[i.id] = {
            name: iEdit[i.id]?.name ?? i.name,
            sku: iEdit[i.id]?.sku ?? (i.sku || ''),
            balance_stock: iEdit[i.id]?.balance_stock ?? String(i.balance_stock ?? 0),
        };
    }
};

watch(
    () => props.qr.questions,
    (questions) => syncQuestionEdits(questions || []),
    { immediate: true },
);

watch(
    () => props.qr.items,
    (items) => syncItemEdits(items || []),
    { immediate: true },
);

const parseOptions = (raw: string) => raw.split(/\r?\n|,/).map((v) => v.trim()).filter(Boolean);
const prettyValue = (value: string | null) => {
    if (!value) return '-';
    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed.join(', ') : String(parsed);
    } catch {
        return value;
    }
};

const setChecked = (setter: (value: boolean) => void) => (value: boolean | 'indeterminate') => setter(value === true);

const searchPlaceholderBySection: Record<string, string> = {
    questions: 'Search questions...',
    items: 'Search items...',
    responses: 'Search responses...',
    wishes: 'Search wishes...',
    pins: 'Search pins...',
};

const createQuestion = () =>
    qForm
        .transform((d) => ({
            label: d.label,
            type: d.type,
            is_required: d.is_required,
            sort_order: Number(d.sort_order || 0),
            options: ['select', 'checkbox'].includes(d.type) ? parseOptions(d.options_text) : [],
        }))
        .post(`/admin/qrs/${props.qr.id}/questions`, { preserveScroll: true, onSuccess: () => qForm.reset('label', 'options_text') });

const saveQuestion = (id: number) => {
    const q = qEdit[id];
    router.patch(
        `/admin/questions/${id}`,
        {
            label: q.label,
            type: q.type,
            is_required: q.is_required,
            sort_order: Number(q.sort_order || 0),
            options: ['select', 'checkbox'].includes(q.type) ? parseOptions(q.options_text || '') : [],
        },
        { preserveScroll: true },
    );
};
const openDeleteQuestion = (id: number) => {
    questionDeleteId.value = id;
};
const closeDeleteQuestion = () => {
    questionDeleteId.value = null;
};
const confirmDeleteQuestion = () => {
    if (!questionDeleteId.value) return;
    router.delete(`/admin/questions/${questionDeleteId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            questionDeleteId.value = null;
        },
    });
};

const createItem = () =>
    iForm
        .transform((d) => ({
            ...d,
            balance_stock: d.balance_stock === '' ? null : Number(d.balance_stock),
        }))
        .post(`/admin/qrs/${props.qr.id}/items`, { preserveScroll: true, onSuccess: () => iForm.reset('name') });

const saveItem = (id: number) =>
    router.patch(
        `/admin/items/${id}`,
        {
            ...iEdit[id],
            balance_stock: iEdit[id].balance_stock === '' ? null : Number(iEdit[id].balance_stock),
        },
        { preserveScroll: true },
    );
const openDeleteItem = (id: number) => {
    itemDeleteId.value = id;
};
const closeDeleteItem = () => {
    itemDeleteId.value = null;
};
const confirmDeleteItem = () => {
    if (!itemDeleteId.value) return;
    router.delete(`/admin/items/${itemDeleteId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            itemDeleteId.value = null;
        },
    });
};
const generatePins = () =>
    pinForm
        .transform((d) => ({ count: Number(d.count) }))
        .post(`/admin/qrs/${props.qr.id}/pins`, { preserveScroll: true });

const setResponseStatus = (id: number, status: string) => router.patch(`/admin/responses/${id}`, { status }, { preserveScroll: true });
const setWishStatus = (id: number, status: string) => router.patch(`/admin/wishes/${id}`, { status }, { preserveScroll: true });
</script>

<template>
    <div class="min-w-0 flex-1">
                <div v-if="currentTab === 'questions'" class="grid gap-4">
                    <form class="grid gap-3 rounded-lg border p-4" @submit.prevent="createQuestion">
                        <h2 class="font-semibold">Add Question</h2>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="grid gap-1">
                                <Label>Label</Label>
                                <Input v-model="qForm.label" />
                                <InputError :message="qForm.errors.label" />
                            </div>
                            <div class="grid gap-1">
                                <Label>Type</Label>
                                <Select v-model="qForm.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in questionTypes" :key="type" :value="type">{{ type }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="qForm.errors.type" />
                            </div>
                            <div class="grid gap-1">
                                <Label>Sort Order</Label>
                                <Input v-model="qForm.sort_order" type="number" min="0" />
                                <InputError :message="qForm.errors.sort_order" />
                            </div>
                            <div class="mt-6 flex items-center gap-2 text-sm">
                                <Checkbox :checked="qForm.is_required" @update:checked="setChecked((v) => (qForm.is_required = v))" />
                                <Label class="text-sm font-normal">Required</Label>
                            </div>
                        </div>
                        <div v-if="['select', 'checkbox'].includes(qForm.type)" class="grid gap-1">
                            <Label>Options (comma or newline)</Label>
                            <Textarea v-model="qForm.options_text" class="min-h-20" />
                            <InputError :message="qForm.errors.options" />
                        </div>
                        <div><Button type="submit" class="bg-amber-500 text-white hover:bg-amber-600">Create</Button></div>
                    </form>

                    <BasicSearch :url="sectionSearchUrl" :q="String(qr.search ?? '')" :placeholder="searchPlaceholderBySection[currentTab]" />                    

                    <Table>
                        <TableHeader class="border-none bg-gray-100">
                            <TableRow class="border-none">
                                <TableHead class="h-fit rounded-l-full py-3"> No </TableHead>
                                <TableHead class="h-fit py-3">Label</TableHead>
                                <TableHead class="h-fit py-3">Type</TableHead>
                                <TableHead class="h-fit py-3">Required</TableHead>
                                <TableHead class="h-fit py-3 whitespace-nowrap">Sort Order</TableHead>
                                <TableHead class="h-fit py-3">Options</TableHead>
                                <TableHead class="h-fit rounded-r-full py-3"> Action </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(q, index) in qr.questions" :key="q.id">
                                <TableCell class="h-fit rounded-l-full py-2">
                                    {{ Number(index) + 1 }}
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <Input v-model="qEdit[q.id].label" />
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <Select v-model="qEdit[q.id].type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="type in questionTypes" :key="type" :value="type">{{ type }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <div class="flex items-center gap-2 text-sm">
                                        <Checkbox :checked="qEdit[q.id].is_required" @update:checked="setChecked((v) => (qEdit[q.id].is_required = v))" />
                                    </div>
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <Input v-model="qEdit[q.id].sort_order" type="number" min="0" />
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <Textarea
                                        v-if="['select', 'checkbox'].includes(qEdit[q.id].type)"
                                        v-model="qEdit[q.id].options_text"
                                        class="mt-3 min-h-16 w-full"
                                    />
                                    <span v-else>--</span>
                                </TableCell>

                                <TableCell class="h-fit rounded-r-full py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="outline" size="sm" @click="saveQuestion(q.id)">Save</Button>
                                        <Button type="button" variant="destructive" size="sm" @click="openDeleteQuestion(q.id)">Delete</Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>



                    <div v-if="qr.questions.length === 0" class="text-sm text-muted-foreground">No questions yet.</div>
                </div>

                <div v-else-if="currentTab === 'items'" class="grid gap-4">
                    <form class="grid gap-3 rounded-lg border p-4 md:grid-cols-3" @submit.prevent="createItem">
                        <div class="grid gap-1">
                            <Label>Name</Label>
                            <Input v-model="iForm.name" />
                            <InputError :message="iForm.errors.name" />
                        </div>
                        <div class="grid gap-1">
                            <Label>Initial Stock</Label>
                            <Input v-model="iForm.balance_stock" type="number" step="0.0001" min="0" />
                            <InputError :message="iForm.errors.balance_stock" />
                        </div>
                        <div class="flex items-end">
                            <Button type="submit" class="bg-amber-500 text-white hover:bg-amber-600">Create</Button>
                        </div>
                    </form>

                    <BasicSearch :url="sectionSearchUrl" :q="String(qr.search ?? '')" :placeholder="searchPlaceholderBySection[currentTab]" />

                    <Table>
                        <TableHeader class="border-none bg-gray-100">
                            <TableRow class="border-none">
                                <TableHead class="h-fit rounded-l-full py-3">No</TableHead>
                                <TableHead class="h-fit py-3">Name</TableHead>
                                <TableHead class="h-fit py-3 whitespace-nowrap">Balance Stock</TableHead>
                                <TableHead class="h-fit py-3 whitespace-nowrap">Current</TableHead>
                                <TableHead class="h-fit rounded-r-full py-3">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(item, index) in qr.items" :key="item.id">
                                <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                <TableCell class="h-fit py-2">
                                    <Input v-model="iEdit[item.id].name" />
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <Input v-model="iEdit[item.id].balance_stock" type="number" step="0.0001" min="0" />
                                </TableCell>
                                <TableCell class="h-fit py-2">
                                    <div class="rounded-md border bg-muted px-3 py-2 text-sm">Current: {{ item.balance_stock }}</div>
                                </TableCell>
                                <TableCell class="h-fit rounded-r-full py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="outline" size="sm" @click="saveItem(item.id)">Save</Button>
                                        <Button type="button" variant="destructive" size="sm" @click="openDeleteItem(item.id)">Delete</Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <div v-if="qr.items.length === 0" class="text-sm text-muted-foreground">No items yet.</div>
                </div>

                <div v-else-if="currentTab === 'stock'" class="grid gap-4">
                    <div class="text-sm text-muted-foreground">Read-only transaction history. Add stock changes from your item workflow.</div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader class="border-none bg-gray-100">
                                <TableRow class="border-none">
                                    <TableHead class="h-fit rounded-l-full py-3">No.</TableHead>
                                    <TableHead class="h-fit py-3">Item</TableHead>
                                    <TableHead class="h-fit py-3">Type</TableHead>
                                    <TableHead class="h-fit py-3">Qty</TableHead>
                                    <TableHead class="h-fit py-3">Note</TableHead>
                                    <TableHead class="h-fit rounded-r-full py-3">Created</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(tx, index) in pagedRows(qr.stock_transactions)" :key="tx.id">
                                    <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ tx.item_name || '-' }}</TableCell>
                                    <TableCell class="h-fit py-2 uppercase">{{ tx.type }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ tx.quantity }}</TableCell>
                                    <TableCell class="h-fit py-2">{{ tx.note || '-' }}</TableCell>
                                    <TableCell class="h-fit rounded-r-full py-2">{{ tx.created_at || '-' }}</TableCell>
                                </TableRow>
                                <TableRow v-if="pagedRows(qr.stock_transactions).length === 0">
                                    <TableCell colspan="6" class="py-6 text-center text-muted-foreground">No stock transactions.</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <Paginator :meta="qr.stock_transactions?.meta" />
                </div>

                <div v-else-if="currentTab === 'responses'" class="grid gap-3">
                    <BasicSearch :url="sectionSearchUrl" :q="String(qr.search ?? '')" :placeholder="searchPlaceholderBySection[currentTab]" />
                    <Table>
                        <TableHeader class="border-none bg-gray-100">
                            <TableRow class="border-none">
                                <TableHead class="h-fit rounded-l-full py-3">No.</TableHead>
                                <TableHead class="h-fit py-3">Status</TableHead>
                                <TableHead class="h-fit py-3">User</TableHead>
                                <TableHead class="h-fit py-3">Preview</TableHead>
                                <TableHead class="h-fit py-3">Created</TableHead>
                                <TableHead class="h-fit rounded-r-full py-3">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(r, index) in pagedRows(qr.responses)" :key="r.id">
                                <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                <TableCell class="h-fit py-2">{{ r.status }}</TableCell>
                                <TableCell class="h-fit py-2">{{ r.user_identifier || '-' }}</TableCell>
                                <TableCell class="h-fit py-2">
                                    <div class="max-w-md space-y-1 text-xs text-muted-foreground">
                                        <div v-for="(a, idx) in r.answers_preview.slice(0, 2)" :key="`${r.id}-${idx}`">
                                            <span class="font-medium text-foreground">{{ a.question || 'Question' }}:</span> {{ prettyValue(a.value) }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="h-fit py-2 whitespace-nowrap">{{ r.created_at || '-' }}</TableCell>
                                <TableCell class="h-fit rounded-r-full py-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <Link :href="`/admin/responses/${r.id}`" class="text-sm text-amber-600 hover:underline">View</Link>
                                        <Button type="button" variant="outline" size="sm" class="h-7 text-xs" @click="setResponseStatus(r.id, 'accepted')">Accept</Button>
                                        <Button type="button" variant="outline" size="sm" class="h-7 text-xs" @click="setResponseStatus(r.id, 'rejected')">Reject</Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="pagedRows(qr.responses).length === 0">
                                <TableCell colspan="6" class="py-6 text-center text-muted-foreground">No responses yet.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <Paginator :meta="qr.responses?.meta" />
                </div>

                <div v-else-if="currentTab === 'wishes'" class="grid gap-3">
                    <BasicSearch :url="sectionSearchUrl" :q="String(qr.search ?? '')" :placeholder="searchPlaceholderBySection[currentTab]" />
                    <Table>
                        <TableHeader class="border-none bg-gray-100">
                            <TableRow class="border-none">
                                <TableHead class="h-fit rounded-l-full py-3">No.</TableHead>
                                <TableHead class="h-fit py-3">Status</TableHead>
                                <TableHead class="h-fit py-3">Message</TableHead>
                                <TableHead class="h-fit py-3">Created</TableHead>
                                <TableHead class="h-fit rounded-r-full py-3">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(wish, index) in pagedRows(qr.wishes)" :key="wish.id">
                                <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                <TableCell class="h-fit py-2">{{ wish.status }}</TableCell>
                                <TableCell class="h-fit py-2">
                                    <p class="max-w-xl whitespace-pre-wrap text-sm">{{ wish.message }}</p>
                                </TableCell>
                                <TableCell class="h-fit py-2 whitespace-nowrap">{{ wish.created_at || '-' }}</TableCell>
                                <TableCell class="h-fit rounded-r-full py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <Button type="button" variant="outline" size="sm" class="h-7 text-xs" @click="setWishStatus(wish.id, 'seen')">Seen</Button>
                                        <Button type="button" variant="outline" size="sm" class="h-7 text-xs" @click="setWishStatus(wish.id, 'done')">Done</Button>
                                        <Button type="button" variant="outline" size="sm" class="h-7 text-xs" @click="setWishStatus(wish.id, 'new')">Reset</Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="pagedRows(qr.wishes).length === 0">
                                <TableCell colspan="5" class="py-6 text-center text-muted-foreground">No wishes yet.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <Paginator :meta="qr.wishes?.meta" />
                </div>

                <div v-else-if="currentTab === 'pins'" class="grid gap-4">
                    <form class="grid gap-3 rounded-lg border p-4 md:grid-cols-3" @submit.prevent="generatePins">
                        <div class="grid gap-1">
                            <Label>Count</Label>
                            <Input v-model="pinForm.count" type="number" min="1" max="1000" step="1" />
                            <InputError :message="pinForm.errors.count" />
                        </div>
                        <div class="flex items-end">
                            <Button type="submit" class="bg-amber-500 text-white hover:bg-amber-600">Generate Pins</Button>
                        </div>
                        <div class="flex items-end text-xs text-muted-foreground">Generate unique 6-digit pins for this QR.</div>
                    </form>
                    <div class="flex justify-between">
                        <BasicSearch :url="sectionSearchUrl" :q="String(qr.search ?? '')" :placeholder="searchPlaceholderBySection[currentTab]" />

                        <Button as-child variant="outline">
                            <a :href="`/admin/qrs/${props.qr.id}/pins/export`">Export Excel (CSV)</a>
                        </Button>
                    </div>

                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader class="border-none bg-gray-100">
                                <TableRow class="border-none">
                                    <TableHead class="h-fit rounded-l-full py-3">No.</TableHead>
                                    <TableHead class="h-fit py-3">PIN</TableHead>
                                    <TableHead class="h-fit py-3">Used</TableHead>
                                    <TableHead class="h-fit rounded-r-full py-3">Created</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(pin, index) in pagedRows(qr.pins)" :key="pin.id">
                                    <TableCell class="h-fit rounded-l-full py-2">{{ Number(index) + 1 }}</TableCell>
                                    <TableCell class="h-fit py-2 font-mono">{{ pin.pin_number }}</TableCell>
                                    <TableCell class="h-fit py-2">
                                        <span :class="pin.is_used ? 'text-red-600' : 'text-emerald-600'">
                                            {{ pin.is_used ? 'Used' : 'Unused' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="h-fit rounded-r-full py-2">{{ pin.created_at || '-' }}</TableCell>
                                </TableRow>
                                <TableRow v-if="pagedRows(qr.pins).length === 0">
                                    <TableCell colspan="4" class="py-6 text-center text-muted-foreground">No pins generated yet.</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <Paginator :meta="qr.pins?.meta" />
                </div>
    </div>
    <Dialog :open="questionDeleteId !== null" @update:open="(value) => !value && closeDeleteQuestion()">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete question?</DialogTitle>
                <DialogDescription>
                    This action cannot be undone. The question will be removed from this QR form schema.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button type="button" variant="outline" @click="closeDeleteQuestion">Cancel</Button>
                <Button type="button" variant="destructive" @click="confirmDeleteQuestion">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
    <Dialog :open="itemDeleteId !== null" @update:open="(value) => !value && closeDeleteItem()">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete item?</DialogTitle>
                <DialogDescription>
                    This action cannot be undone. The item and its related stock transactions may no longer be available.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button type="button" variant="outline" @click="closeDeleteItem">Cancel</Button>
                <Button type="button" variant="destructive" @click="confirmDeleteItem">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
