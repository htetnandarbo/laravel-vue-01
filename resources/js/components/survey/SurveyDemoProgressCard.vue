<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        badge?: string;
        title?: string;
        description?: string;
        currentStep?: number;
        totalSteps?: number;
        progress?: number;
    }>(),
    {
        badge: 'Client Demo',
        title: 'Customer Survey Form',
        description: 'Mobile-first demo UI with sample questions only (no backend submission).',
        currentStep: 1,
        totalSteps: 3,
        progress: undefined,
    },
);

const safeTotal = computed(() => Math.max(1, props.totalSteps));
const safeStep = computed(() => Math.min(Math.max(1, props.currentStep), safeTotal.value));
const progressPercent = computed(() => {
    if (typeof props.progress === 'number') {
        return Math.min(100, Math.max(0, props.progress));
    }

    return Math.round((safeStep.value / safeTotal.value) * 100);
});
</script>

<template>
    <div class="rounded-2xl border border-amber-100 bg-white/80 p-4 shadow-sm backdrop-blur sm:p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-[0.18em] text-amber-600 uppercase">{{ badge }}</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900 sm:text-2xl">{{ title }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ description }}</p>
            </div>

            <div class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Page {{ safeStep }}/{{ safeTotal }}</div>
        </div>

        <div class="mt-4">
            <div class="h-2 rounded-full bg-slate-100">
                <div
                    class="h-2 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-300"
                    :style="{ width: `${progressPercent}%` }"
                />
            </div>
        </div>
    </div>
</template>
