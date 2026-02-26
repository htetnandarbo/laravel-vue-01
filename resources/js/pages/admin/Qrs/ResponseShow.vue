<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps<{ response: any }>();

const setStatus = (status: string) => router.patch(`/admin/responses/${props.response.id}`, { status }, { preserveScroll: true });

const formatValue = (value: string | null) => {
    if (!value) return '-';
    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed.join(', ') : String(parsed);
    } catch {
        return value;
    }
};
</script>

<template>
    <AppLayout>
        <div class="m-5 grid gap-5">
            <Card>
                <CardContent class="pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-semibold">Response #{{ response.id }}</h1>
                        <p class="text-sm text-muted-foreground">
                            QR: {{ response.qr?.name || response.qr?.token || '-' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link v-if="response.qr?.id" :href="`/admin/qrs/${response.qr.id}/responses`" class="text-sm text-amber-600 hover:underline">Back to QR</Link>
                    </div>
                </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Answers</CardTitle>
                    <CardDescription>Submitted values for this response.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-for="answer in response.answers" :key="answer.id" class="rounded-lg border p-3">
                        <div class="text-sm font-medium">{{ answer.question || 'Question' }}</div>
                        <div class="mt-1 whitespace-pre-wrap text-sm text-muted-foreground">{{ formatValue(answer.value) }}</div>
                    </div>
                    <div v-if="response.answers.length === 0" class="text-sm text-muted-foreground">No answers stored.</div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
