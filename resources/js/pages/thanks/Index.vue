<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { demo as spinDemo } from '@/routes/spin';
import { Gift, Sparkles, Star } from 'lucide-vue-next';

type StoredPrize = {
    label: string;
    color?: string | null;
    imageUrl?: string | null;
};

const prize = ref<StoredPrize>({
    label: 'Mystery Gift',
    color: '#f59e0b',
    imageUrl: null,
});
const props = defineProps<{ qrToken?: string | null }>();

onMounted(() => {
    try {
        const raw = localStorage.getItem('wishinluck:last-prize');
        if (!raw) return;

        const parsed = JSON.parse(raw) as StoredPrize;
        if (parsed?.label) {
            prize.value = {
                label: parsed.label,
                color: parsed.color || '#f59e0b',
                imageUrl: parsed.imageUrl || null,
            };
        }
    } catch {
        // Keep demo fallback prize if localStorage is unavailable or malformed.
    }
});

const backToSpin = () => {
    router.visit(props.qrToken ? spinDemo({ query: { qr: props.qrToken } }) : spinDemo());
};
</script>

<template>
    <Head title="Thanks for Shopping" />

    <div class="min-h-screen overflow-x-clip bg-linear-to-b from-amber-100 via-orange-50 to-rose-50 px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-4xl items-center justify-center sm:min-h-[calc(100vh-5rem)]">
            <div class="relative w-full max-w-2xl overflow-x-clip">
                <!-- <div class="lucky-orb lucky-orb--one" />
                <div class="lucky-orb lucky-orb--two" />
                <div class="lucky-orb lucky-orb--three" /> -->

                <Card class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/85 backdrop-blur">
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-linear-to-r from-amber-400 via-orange-400 to-rose-400" />

                    <CardContent class="p-6 sm:p-8">
                        <div class="flex flex-col items-center text-center">
                            <div
                                class="lucky-badge mb-4 grid size-16 place-items-center rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-amber-950 shadow-lg"
                            >
                                <Sparkles class="size-8" />
                            </div>

                            <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase">Wish In Luck</p>
                            <h1 class="mt-2 text-2xl font-semibold text-slate-900 sm:text-3xl">Thanks for Shopping</h1>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
                                Your lucky spin is complete. Here is the gift you got from the wheel.
                            </p>

                            <div
                                class="lucky-prize-shell mt-6 w-full rounded-3xl border p-5 shadow-lg sm:p-6"
                                :style="{
                                    borderColor: `${prize.color ?? '#f59e0b'}44`,
                                    background: `linear-gradient(180deg, #ffffff, ${prize.color ?? '#f59e0b'}12)`,
                                }"
                            >
                                <div class="mb-4 flex items-center justify-center gap-2 text-amber-700">
                                    <Star class="lucky-star lucky-star--left size-4" />
                                    <span class="text-xs font-semibold tracking-[0.16em] uppercase">Lucky Gift</span>
                                    <Star class="lucky-star lucky-star--right size-4" />
                                </div>

                                <div
                                    class="relative mx-auto mb-4 grid size-20 place-items-center rounded-2xl border border-white/80 bg-white/90 shadow-md"
                                >
                                    <div class="prize-glow" :style="{ backgroundColor: `${prize.color ?? '#f59e0b'}55` }" />

                                    <img
                                        v-if="prize.imageUrl"
                                        :src="prize.imageUrl"
                                        :alt="prize.label"
                                        class="relative z-10 h-12 w-12 rounded object-contain"
                                    />
                                    <Gift v-else class="relative z-10 size-8 text-amber-600" />
                                </div>

                                <p class="text-xs tracking-[0.14em] text-slate-500 uppercase">You received</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-900 sm:text-3xl">{{ prize.label }}</p>
                            </div>

                            <div class="mt-6 flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                                <Button type="button" class="w-full cursor-pointer bg-amber-500 text-white hover:bg-amber-600 sm:w-auto">
                                    Exist
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>

<style scoped>
.lucky-orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(30px);
    opacity: 0.5;
    pointer-events: none;
    animation: luckyDrift 6s ease-in-out infinite;
}

.lucky-orb--one {
    width: 120px;
    height: 120px;
    top: -18px;
    left: -22px;
    background: rgb(251 191 36 / 0.45);
}

.lucky-orb--two {
    width: 150px;
    height: 150px;
    right: -28px;
    top: 16%;
    background: rgb(251 146 60 / 0.35);
    animation-delay: -1.7s;
}

.lucky-orb--three {
    width: 110px;
    height: 110px;
    left: 16%;
    bottom: -24px;
    background: rgb(244 114 182 / 0.28);
    animation-delay: -3.1s;
}

.lucky-badge {
    animation: luckyBadgeFloat 2.2s ease-in-out infinite;
}

.lucky-prize-shell {
    position: relative;
    overflow: hidden;
}

.lucky-prize-shell::before {
    content: '';
    position: absolute;
    inset: -30% 55% auto -20%;
    height: 160%;
    background: linear-gradient(120deg, rgb(255 255 255 / 0), rgb(255 255 255 / 0.5), rgb(255 255 255 / 0));
    transform: rotate(14deg);
    animation: shineSweep 3.2s ease-in-out infinite;
    pointer-events: none;
}

.prize-glow {
    position: absolute;
    inset: 8px;
    border-radius: 14px;
    filter: blur(14px);
    opacity: 0.9;
    animation: glowPulse 1.8s ease-in-out infinite;
}

.lucky-star {
    color: rgb(245 158 11);
    animation: twinkle 1.7s ease-in-out infinite;
}

.lucky-star--right {
    animation-delay: -0.8s;
}

@media (prefers-reduced-motion: reduce) {
    .lucky-orb,
    .lucky-badge,
    .lucky-prize-shell::before,
    .prize-glow,
    .lucky-star {
        animation: none !important;
    }
}

@keyframes luckyDrift {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(0, -6px, 0) scale(1.03);
    }
}

@keyframes luckyBadgeFloat {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

@keyframes shineSweep {
    0% {
        transform: translateX(-120%) rotate(14deg);
        opacity: 0;
    }
    25% {
        opacity: 1;
    }
    55% {
        opacity: 1;
    }
    100% {
        transform: translateX(220%) rotate(14deg);
        opacity: 0;
    }
}

@keyframes glowPulse {
    0%,
    100% {
        opacity: 0.7;
        transform: scale(0.98);
    }
    50% {
        opacity: 1;
        transform: scale(1.02);
    }
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.55;
        transform: scale(0.9);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}
</style>
