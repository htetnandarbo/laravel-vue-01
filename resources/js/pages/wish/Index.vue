<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { demo as spinDemo } from '@/routes/spin';
import { Gift, Sparkles, Star } from 'lucide-vue-next';

const isWishDialogOpen = ref(false);
const isDesktop = ref(false);
const wishText = ref('');
const draftWish = ref('');

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

    wishText.value = cleanWish;
    isWishDialogOpen.value = false;
};

const nextStep = () => {
    if (!hasWish.value) return;
    router.visit(spinDemo());
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

                                    <div class="flex items-center justify-between text-xs">
                                        <p class="text-slate-500">Tip: Specific wishes feel more meaningful in the demo.</p>
                                        <p :class="draftCount > 220 ? 'text-red-500' : 'text-slate-500'">{{ draftCount }}/220</p>
                                    </div>
                                </div>

                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button type="button" variant="outline" class="w-full sm:w-auto">Cancel</Button>
                                    </DialogClose>
                                    <Button
                                        type="button"
                                        class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                                        :disabled="!draftWish.trim()"
                                        @click="saveWish"
                                    >
                                        Save Wish
                                    </Button>
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
                                </div>

                                <SheetFooter class="gap-2 px-4 pb-4">
                                    <SheetClose as-child>
                                        <Button type="button" variant="outline" class="w-full">Cancel</Button>
                                    </SheetClose>
                                    <Button
                                        type="button"
                                        class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600"
                                        :disabled="!draftWish.trim()"
                                        @click="saveWish"
                                    >
                                        Save Wish
                                    </Button>
                                </SheetFooter>
                            </SheetContent>
                        </Sheet>

                        <div class="flex justify-end border-t border-slate-100 pt-2">
                            <Button
                                type="button"
                                class="w-full cursor-pointer rounded-xl bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                                :disabled="!hasWish"
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
</template>
