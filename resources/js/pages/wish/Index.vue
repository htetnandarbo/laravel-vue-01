<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import html2canvas from 'html2canvas';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { Gift, Sparkles, Star } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const isWishDialogOpen = ref(false);
const isDesktop = ref(false);
const wishText = ref('');
const draftWish = ref('');
const wishCardRef = ref<HTMLElement | null>(null);
const wishCardCaptureRef = ref<HTMLElement | null>(null);
const props = defineProps<{ qrToken?: string | null }>();
const wishForm = useForm({
    message: '',
    image: ''
});

const wishPreview = computed(() => {
    return wishText.value.trim() || 'Wish လေးကို ဖြည့်ပါ';
});

const hasWish = computed(() => Boolean(wishText.value.trim()));
const draftCount = computed(() => draftWish.value.trim().length);

const openWishDialog = () => {
    draftWish.value = wishText.value;
    isWishDialogOpen.value = true;
};

const saveWish = () => {
    const cleanWish = draftWish.value.trim();
    if (!cleanWish) return;

    if (!props.qrToken) {
        wishText.value = cleanWish;
        isWishDialogOpen.value = false;
        return;
    }

    wishForm.message = cleanWish;
    wishText.value = cleanWish;
    isWishDialogOpen.value = false;
    // console.log(cleanWish, hasWish.value);
   
};

const nextStep = () => {
    console.log('work');
    

    if (!hasWish.value){
        toast.error('Please add your wish to begin.');
        return;
    };

    const captureTarget = wishCardCaptureRef.value ?? wishCardRef.value;


    html2canvas(captureTarget).then(canvas => {
        const image = canvas.toDataURL('image/png');
        if (!props.qrToken || !captureTarget) {
            return;
        }
        wishForm.image = image;

        wishForm.post(`/qr/${props.qrToken}/wish`, {
        preserveScroll: true,
        onSuccess: () => {
            wishText.value = wishForm.message;
            isWishDialogOpen.value = false;
        },
    });
    })
};

const updateViewportMode = () => {
    isDesktop.value = window.innerWidth >= 640;
};

onMounted(() => {
    updateViewportMode();
    window.addEventListener('resize', updateViewportMode);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateViewportMode);
});
</script>

<template>
    <Head title="Wish & Luck Demo" />

    <div class="flex min-h-screen items-center justify-center bg-linear-to-b from-amber-100 via-orange-50 to-rose-50 px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto w-full max-w-4xl">
            <div class="mx-auto max-w-2xl">
                <Card class="rounded-3xl border-0 bg-white/85 backdrop-blur">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-center gap-2 text-slate-900">
                            <Gift class="size-5 text-amber-600" />
                            <span>Your Wish Card</span>
                            <Gift class="size-5 text-amber-600" />
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <button
                            ref="wishCardRef"
                            type="button"
                            class="group relative w-full rounded-3xl border border-dashed border-amber-300 bg-[linear-gradient(135deg,_rgba(255,250,220,0.95),_rgba(255,240,200,0.75)_55%,_rgba(255,255,255,0.98))] p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:outline-none sm:p-6"
                            @click="openWishDialog"
                        >
                            <div
                                class="absolute top-3 right-3 rounded-full border border-white/60 bg-white/70 px-2.5 py-1 text-[11px] font-medium text-amber-700 shadow-sm"
                            >
                                Tap to {{ hasWish ? 'edit' : 'fill' }}
                            </div>

                            <div class="mb-4 flex items-center gap-2 text-amber-700">
                                <Sparkles class="size-4" />
                                <span class="text-xs font-semibold tracking-[0.16em] uppercase">Wish Card</span>
                            </div>

                            <p
                                class="min-h-20 text-base leading-7 sm:min-h-24 sm:text-lg"
                                :class="hasWish ? 'font-medium text-slate-900' : 'text-slate-500 italic'"
                            >
                                {{ wishPreview }}
                            </p>

                            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                                <Star class="size-3.5 text-yellow-500" />
                                <span>{{ hasWish ? 'Wish saved successfully. Tap again anytime to edit it.' : 'Add your wish to begin.' }}</span>
                            </div>
                        </button>

                        <Dialog v-if="isDesktop" v-model:open="isWishDialogOpen">
                            <DialogContent class="sm:max-w-lg">
                                <DialogHeader class="space-y-2">
                                    <DialogTitle class="flex items-center gap-2">
                                        <Sparkles class="size-4 text-amber-600" />
                                        Wish လေးကို ဖြည့်ပါ
                                    </DialogTitle>
                                    <DialogDescription> ကိုယ့်အတွက် (သို့) ကိုယ်ချစ်ခင်ရတဲ့ သူတွေအတွက် Wish လေးရေးပေးနော်။ </DialogDescription>
                                </DialogHeader>

                                <div class="space-y-3">
                                    <Textarea id="wish-text-desktop" v-model="draftWish" rows="5" maxlength="220" />
                                    <InputError :message="wishForm.errors.message" />

                                    <div class="flex items-center justify-between text-xs">
                                        <p class="text-slate-500">Tip: Specific wishes feel more meaningful in the demo.</p>
                                        <p :class="draftCount > 220 ? 'text-red-500' : 'text-slate-500'">{{ draftCount }}/220</p>
                                    </div>
                                </div>

                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button type="button" variant="outline" class="w-full sm:w-auto">Cancel</Button>
                                    </DialogClose>
                                    <!-- <DialogClose as-child>
                                        <Button
                                            type="button"
                                            class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                                            :disabled="!draftWish.trim()"
                                            @click.stop="saveWish"
                                        >
                                            Save Wish
                                        </Button>
                                    </DialogClose> -->
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                                            :disabled="!draftWish.trim()"
                                            @click="saveWish()"
                                        >
                                            Save Wish
                                        </Button>
                                    </DialogClose>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <Sheet v-else v-model:open="isWishDialogOpen">
                            <SheetContent side="top" class="rounded-b-3xl">
                                <SheetHeader class="space-y-2 pr-8">
                                    <SheetTitle class="flex items-center gap-2">
                                        <Sparkles class="size-4 text-amber-600" />
                                        Wish လေးကို ဖြည့်ပါ
                                    </SheetTitle>
                                    <SheetDescription> ကိုယ့်အတွက် (သို့) ကိုယ်ချစ်ခင်ရတဲ့ သူတွေအတွက် Wish လေးရေးပေးနော်။ </SheetDescription>
                                </SheetHeader>

                                <div class="space-y-3 px-4">
                                    <Textarea id="wish-text-mobile" v-model="draftWish" rows="5" maxlength="220" />
                                    <InputError :message="wishForm.errors.message" />
                                </div>

                                <SheetFooter class="gap-2 px-4 pb-4">
                                    <SheetClose as-child>
                                        <Button type="button" variant="outline" class="w-full">Cancel</Button>
                                    </SheetClose>
                                    <Button
                                        type="button"
                                        class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600"
                                        :disabled="!draftWish.trim() || wishForm.processing"
                                        @click="saveWish"
                                    >
                                        {{ wishForm.processing ? 'Saving...' : 'Save Wish' }}
                                    </Button>
                                </SheetFooter>
                            </SheetContent>
                        </Sheet>

                        <div class="flex justify-end border-t border-slate-100 pt-2">
                            <Button
                                type="button"
                                class="w-full cursor-pointer rounded-xl bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                                :disabled="!hasWish || wishForm.processing"
                                @click="nextStep"
                            >
                                Next
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div
            aria-hidden="true"
            style="position: fixed; left: -10000px; top: 0; width: 720px; pointer-events: none; opacity: 1; z-index: -1;"
        >
            <div
                 ref="wishCardCaptureRef"
                style="position: relative; width: 100%; border: 2px dashed #fcd34d; border-radius: 24px; padding: 20px; background: linear-gradient(135deg, rgba(255,250,220,0.95), rgba(255,240,200,0.75) 55%, rgba(255,255,255,0.98)); box-shadow: 0 4px 14px rgba(0,0,0,0.06);"
            >
            

                <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 8px; color: #b45309;">
                    <span style="font-size: 14px;">✦</span>
                    <span style="font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;">Wish Card</span>
                </div>

                <p
                    :style="{
                        minHeight: '88px',
                        margin: '0',
                        fontSize: '18px',
                        lineHeight: '1.6',
                        color: hasWish ? '#0f172a' : '#64748b',
                        fontStyle: hasWish ? 'normal' : 'italic',
                        fontWeight: hasWish ? '600' : '400',
                        whiteSpace: 'pre-wrap',
                        wordBreak: 'break-word',
                    }"
                >
                    {{ wishPreview }}
                </p>

                
            </div>
        </div>
    </Teleport>
</template>
