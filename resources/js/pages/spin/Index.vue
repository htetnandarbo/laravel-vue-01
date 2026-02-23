<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch, type Component } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { demo as thanksDemo } from '@/routes/thanks';
import { Briefcase, Coffee, Gem, Gift, Headphones, Package, ShoppingBag, Smartphone, Ticket, Trophy, Umbrella, Watch } from 'lucide-vue-next';

type PrizeSegment = {
    label: string;
    wheelLabel: string;
    color: string;
    textColor?: string;
    icon: Component;
    imageUrl?: string;
};

const prizeSegments: PrizeSegment[] = [
    { label: 'Umbrella', wheelLabel: 'Umbrella', color: '#f59e0b', textColor: '#451a03', icon: Umbrella, imageUrl: '/demo-prizes/umbrella.png' },
    {
        label: 'Shopping Bag',
        wheelLabel: 'Bag',
        color: '#fcd34d',
        textColor: '#78350f',
        icon: ShoppingBag,
        imageUrl: '/demo-prizes/shopping-bag.png',
    },
    {
        label: 'Coffee Voucher',
        wheelLabel: 'Coffee',
        color: '#fb7185',
        textColor: '#4c0519',
        icon: Coffee,
        imageUrl: '/demo-prizes/coffee-voucher.png',
    },
    { label: 'Gift Box', wheelLabel: 'Gift', color: '#c084fc', textColor: '#3b0764', icon: Gift, imageUrl: '/demo-prizes/gift-box.png' },
    { label: 'Headphones', wheelLabel: 'Audio', color: '#60a5fa', textColor: '#082f49', icon: Headphones, imageUrl: '/demo-prizes/headphones.png' },
    { label: 'Travel Bag', wheelLabel: 'Travel', color: '#34d399', textColor: '#022c22', icon: Briefcase, imageUrl: '/demo-prizes/travel-bag.png' },
    {
        label: 'Smartphone Stand',
        wheelLabel: 'Stand',
        color: '#fdba74',
        textColor: '#431407',
        icon: Smartphone,
        imageUrl: '/demo-prizes/smartphone-stand.png',
    },
    {
        label: 'Golden Ticket',
        wheelLabel: 'Ticket',
        color: '#f9a8d4',
        textColor: '#500724',
        icon: Ticket,
        imageUrl: '/demo-prizes/golden-ticket.png',
    },
    { label: 'Premium Watch', wheelLabel: 'Watch', color: '#93c5fd', textColor: '#172554', icon: Watch, imageUrl: '/demo-prizes/premium-watch.png' },
    { label: 'Mystery Box', wheelLabel: 'Mystery', color: '#a7f3d0', textColor: '#064e3b', icon: Package, imageUrl: '/demo-prizes/mystery-box.png' },
    { label: 'Lucky Gem', wheelLabel: 'Gem', color: '#d8b4fe', textColor: '#4c1d95', icon: Gem, imageUrl: '/demo-prizes/lucky-gem.png' },
];

const segmentAngle = 360 / prizeSegments.length;
const wheelRotation = ref(0);
const isSpinning = ref(false);
const spinDurationMs = 4200;
const resultModalOpen = ref(false);
const selectedPrize = ref<PrizeSegment | null>(null);
const spinCount = ref(0);
const showScrollTutorial = ref(false);
const guideItemRefs = new Map<string, HTMLElement>();
let spinTimeoutId: number | null = null;

const wheelGradient = computed(() => {
    const stops = prizeSegments
        .map((segment, index) => {
            const start = index * segmentAngle;
            const end = start + segmentAngle;
            return `${segment.color} ${start}deg ${end}deg`;
        })
        .join(', ');

    return `conic-gradient(${stops})`;
});

const normalizedRotation = computed(() => {
    const angle = wheelRotation.value % 360;
    return angle < 0 ? angle + 360 : angle;
});

const currentPointerIndex = computed(() => {
    // Pointer is fixed at top while the wheel rotates clockwise.
    const pointerAngleOnWheel = (360 - normalizedRotation.value + 360) % 360;
    return Math.floor(pointerAngleOnWheel / segmentAngle) % prizeSegments.length;
});

const currentPointerPrize = computed(() => prizeSegments[currentPointerIndex.value]);

const spinWheel = () => {
    if (isSpinning.value) return;

    resultModalOpen.value = false;
    showScrollTutorial.value = false;
    isSpinning.value = true;
    spinCount.value += 1;

    const turns = 6 + Math.floor(Math.random() * 4);
    const landingAngle = Math.floor(Math.random() * 360);
    wheelRotation.value += turns * 360 + landingAngle;

    if (spinTimeoutId) {
        window.clearTimeout(spinTimeoutId);
    }

    spinTimeoutId = window.setTimeout(() => {
        selectedPrize.value = currentPointerPrize.value;
        resultModalOpen.value = true;
        isSpinning.value = false;
        spinTimeoutId = null;
    }, spinDurationMs);
};

const handleResultNext = () => {
    if (selectedPrize.value) {
        localStorage.setItem(
            'wishinluck:last-prize',
            JSON.stringify({
                label: selectedPrize.value.label,
                color: selectedPrize.value.color,
                imageUrl: selectedPrize.value.imageUrl ?? null,
            }),
        );
    }

    showScrollTutorial.value = false;
    resultModalOpen.value = false;
    router.visit(thanksDemo());
};

const setGuideItemRef = (label: string, el: Element | null) => {
    if (el instanceof HTMLElement) {
        guideItemRefs.set(label, el);
        return;
    }

    guideItemRefs.delete(label);
};

const scrollToWinningGiftGuide = async () => {
    if (!selectedPrize.value) return;

    await nextTick();

    const winnerRow = guideItemRefs.get(selectedPrize.value.label);
    if (!winnerRow) return;

    winnerRow.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });

    showScrollTutorial.value = true;
};

watch(resultModalOpen, async (isOpen, wasOpen) => {
    if (wasOpen && !isOpen && selectedPrize.value) {
        await scrollToWinningGiftGuide();
    }
});

watch(
    [wheelRotation, spinCount, selectedPrize],
    () => {
        localStorage.setItem(
            'wishinluck:spin-state',
            JSON.stringify({
                wheelRotation: wheelRotation.value,
                spinCount: spinCount.value,
                selectedPrizeLabel: selectedPrize.value?.label ?? null,
            }),
        );
    },
    { deep: false },
);

onMounted(() => {
    try {
        const raw = localStorage.getItem('wishinluck:spin-state');
        if (!raw) return;

        const state = JSON.parse(raw) as {
            wheelRotation?: number;
            spinCount?: number;
            selectedPrizeLabel?: string | null;
        };

        if (typeof state.wheelRotation === 'number') {
            wheelRotation.value = state.wheelRotation;
        }

        if (typeof state.spinCount === 'number') {
            spinCount.value = state.spinCount;
        }

        if (state.selectedPrizeLabel) {
            const matchedPrize = prizeSegments.find((segment) => segment.label === state.selectedPrizeLabel);
            if (matchedPrize) {
                selectedPrize.value = matchedPrize;
            }
        }
    } catch {
        // Ignore malformed demo storage data.
    }
});

onBeforeUnmount(() => {
    if (spinTimeoutId) {
        window.clearTimeout(spinTimeoutId);
        spinTimeoutId = null;
    }
});
</script>

<template>
    <Head title="Spin Wheel Demo" />

    <div class="min-h-screen bg-linear-to-b from-amber-100 via-orange-50 to-rose-50 px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto w-full max-w-5xl">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.08fr_0.92fr]">
                <Card class="rounded-3xl border border-white/60 bg-white/85 shadow-xl shadow-orange-100/80 backdrop-blur">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-slate-900">Spin Wheel</CardTitle>
                        <CardDescription class="text-slate-600">
                            Press spin and wait for the wheel to stop. The prize result will appear in a modal popup.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="space-y-5">
                        <div class="relative mx-auto w-full max-w-sm">
                            <div class="pointer-triangle absolute top-0 left-1/2 z-20 -translate-x-1/2 -translate-y-1" />

                            <div class="absolute inset-4 rounded-full bg-orange-300/25 blur-2xl" />

                            <div
                                class="wheel-shell relative mt-5 aspect-square rounded-full border-8 border-white/70 shadow-2xl shadow-orange-200/80"
                                :class="{ 'wheel-spinning': isSpinning }"
                                :style="{
                                    background: wheelGradient,
                                    transform: `rotate(${wheelRotation}deg)`,
                                    transition: isSpinning ? `transform ${spinDurationMs}ms cubic-bezier(0.08, 0.85, 0.18, 1)` : 'none',
                                }"
                            >
                                <div class="absolute inset-3 rounded-full border border-white/70 bg-white/10" />

                                <div
                                    class="absolute top-1/2 left-1/2 z-10 grid size-[84px] -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-4 border-white/90 bg-amber-600 text-center text-xs font-bold tracking-[0.14em] text-white shadow-xl"
                                >
                                    SPIN
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Spins: <span class="font-semibold text-amber-950">{{ spinCount }}</span>
                            </div>

                            <Button
                                type="button"
                                class="w-full cursor-pointer rounded-xl bg-amber-500 text-white shadow-md shadow-amber-200 hover:bg-amber-600 sm:w-auto"
                                :disabled="isSpinning"
                                @click="spinWheel"
                            >
                                {{ isSpinning ? 'Spinning...' : 'Spin Now' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-3xl border border-white/60 bg-white/85 shadow-xl shadow-orange-100/80 backdrop-blur">
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-slate-900">
                            <Gift class="size-5 text-amber-600" />
                            Gift Guide
                        </CardTitle>
                        <CardDescription class="text-slate-600">
                            After spinning, the winning gift stays highlighted here even if the result modal is closed.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <div class="grid grid-cols-1 gap-2">
                            <div
                                v-for="segment in prizeSegments"
                                :key="`guide-${segment.label}`"
                                :ref="(el) => setGuideItemRef(segment.label, el)"
                                class="prize-guide-item flex items-center justify-between rounded-xl border px-3 py-2 transition-all duration-300"
                                :class="{
                                    'border-slate-200 bg-white': !selectedPrize,
                                    'prize-guide-item--winner border-amber-300 bg-amber-50/80': selectedPrize?.label === segment.label,
                                    'opacity-40 grayscale saturate-0': selectedPrize && selectedPrize.label !== segment.label,
                                }"
                            >
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        class="prize-guide-icon grid size-8 shrink-0 place-items-center rounded-lg border bg-white shadow-sm"
                                        :class="{ 'prize-guide-icon--winner': selectedPrize?.label === segment.label }"
                                    >
                                        <img
                                            v-if="segment.imageUrl"
                                            :src="segment.imageUrl"
                                            :alt="segment.label"
                                            class="h-5 w-5 rounded object-contain"
                                        />
                                        <component v-else :is="segment.icon" class="size-4 text-slate-700" />
                                    </span>

                                    <span class="size-2.5 shrink-0 rounded-full border border-white/70" :style="{ backgroundColor: segment.color }" />

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-800">{{ segment.label }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ selectedPrize?.label === segment.label ? 'Winning gift' : 'Prize option' }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="rounded-full px-2 py-1 text-[11px] font-medium"
                                    :class="selectedPrize?.label === segment.label ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ selectedPrize?.label === segment.label ? 'Won' : 'Gift' }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="selectedPrize && showScrollTutorial"
                            class="tutorial-shadow mt-3 rounded-xl border border-amber-200/80 bg-white/90 p-3 shadow-lg shadow-amber-100"
                        >
                            <div class="flex items-start gap-3">
                                <div class="tutorial-bounce grid size-8 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-700">↓</div>
                                <div>
                                    <p class="text-sm font-semibold text-amber-900">Your gift is saved here</p>
                                    <p class="text-xs leading-5 text-amber-800/90">
                                        If the modal is closed, check this highlighted gift. Scroll down to continue to the next page.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mx-auto mt-5 w-full max-w-3xl">
                <Card class="rounded-2xl border border-white/60 bg-white/80 shadow-lg shadow-orange-100/70 backdrop-blur">
                    <CardContent class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Continue To Next Page</p>
                            <p class="text-xs text-slate-600">
                                {{
                                    selectedPrize
                                        ? `Winning gift: ${selectedPrize.label}. You can continue now.`
                                        : 'Spin first to unlock the next page.'
                                }}
                            </p>
                        </div>

                        <Button
                            type="button"
                            class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                            :disabled="!selectedPrize"
                            @click="handleResultNext"
                        >
                            Next Page
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="resultModalOpen">
            <DialogContent class="border border-amber-100 bg-white sm:max-w-md">
                <DialogHeader class="space-y-2">
                    <DialogTitle class="flex items-center gap-2 text-slate-900">
                        <Trophy class="size-5 text-amber-500" />
                        Prize Unlocked
                    </DialogTitle>
                    <DialogDescription class="text-slate-600">
                        The wheel has stopped. Show this prize result to the user and continue to the next step.
                    </DialogDescription>
                </DialogHeader>

                <div
                    v-if="selectedPrize"
                    class="rounded-2xl border p-4 text-center"
                    :style="{
                        borderColor: `${selectedPrize.color}55`,
                        backgroundColor: `${selectedPrize.color}18`,
                    }"
                >
                    <div class="mx-auto mb-3 grid size-12 place-items-center rounded-xl border border-white/70 bg-white/80 shadow-sm">
                        <img
                            v-if="selectedPrize.imageUrl"
                            :src="selectedPrize.imageUrl"
                            :alt="selectedPrize.label"
                            class="h-8 w-8 rounded object-contain"
                        />
                        <component v-else :is="selectedPrize.icon" class="size-6 text-slate-800" />
                    </div>
                    <p class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">You got</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ selectedPrize.label }}</p>
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" class="w-full sm:w-auto" @click="resultModalOpen = false">Close</Button>
                    <Button
                        type="button"
                        class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                        @click="handleResultNext"
                    >
                        Next
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.pointer-triangle {
    width: 0;
    height: 0;
    border-left: 14px solid transparent;
    border-right: 14px solid transparent;
    border-bottom: 0;
    border-top: 20px solid rgb(251 191 36);
    filter: drop-shadow(0 4px 6px rgb(0 0 0 / 0.25));
}

.wheel-shell::before {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 9999px;
    background: conic-gradient(
        from 0deg,
        rgb(255 255 255 / 0.18),
        transparent 30%,
        rgb(255 255 255 / 0.16),
        transparent 70%,
        rgb(255 255 255 / 0.18)
    );
    z-index: -1;
    filter: blur(6px);
}

.wheel-spinning {
    will-change: transform;
}

.prize-guide-item--winner {
    position: relative;
    box-shadow: 0 10px 24px rgb(251 191 36 / 0.18);
}

.prize-guide-item--winner::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 14px;
    border: 1px solid rgb(251 191 36 / 0.45);
    animation: luckyPulse 1.8s ease-in-out infinite;
    pointer-events: none;
}

.prize-guide-icon--winner {
    position: relative;
    border-color: rgb(252 211 77);
    box-shadow:
        0 0 0 3px rgb(251 191 36 / 0.16),
        0 8px 16px rgb(251 191 36 / 0.2);
    animation: luckyFloat 1.6s ease-in-out infinite;
}

.prize-guide-icon--winner::before,
.prize-guide-icon--winner::after {
    content: '';
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: rgb(251 191 36 / 0.9);
    box-shadow: 0 0 10px rgb(251 191 36 / 0.65);
    pointer-events: none;
}

.prize-guide-icon--winner::before {
    top: -4px;
    right: -4px;
    animation: luckySpark 1.2s ease-in-out infinite;
}

.prize-guide-icon--winner::after {
    bottom: -3px;
    left: -5px;
    width: 5px;
    height: 5px;
    background: rgb(249 168 212 / 0.95);
    box-shadow: 0 0 10px rgb(249 168 212 / 0.55);
    animation: luckySpark 1.4s ease-in-out infinite reverse;
}

.tutorial-shadow {
    animation: tutorialShadowPulse 1.8s ease-in-out infinite;
}

.tutorial-bounce {
    animation: tutorialArrowBounce 1.2s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .wheel-shell {
        transition: none !important;
    }

    .prize-guide-item--winner::before,
    .prize-guide-icon--winner,
    .prize-guide-icon--winner::before,
    .prize-guide-icon--winner::after,
    .tutorial-shadow,
    .tutorial-bounce {
        animation: none !important;
    }
}

@keyframes luckyPulse {
    0%,
    100% {
        opacity: 0.45;
        transform: scale(1);
    }
    50% {
        opacity: 0.95;
        transform: scale(1.012);
    }
}

@keyframes luckyFloat {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-2px);
    }
}

@keyframes luckySpark {
    0%,
    100% {
        opacity: 0.4;
        transform: scale(0.8);
    }
    50% {
        opacity: 1;
        transform: scale(1.15);
    }
}

@keyframes tutorialShadowPulse {
    0%,
    100% {
        box-shadow:
            0 10px 20px rgb(251 191 36 / 0.14),
            0 0 0 0 rgb(251 191 36 / 0.08);
    }
    50% {
        box-shadow:
            0 12px 24px rgb(251 191 36 / 0.2),
            0 0 0 6px rgb(251 191 36 / 0.06);
    }
}

@keyframes tutorialArrowBounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(3px);
    }
}
</style>
