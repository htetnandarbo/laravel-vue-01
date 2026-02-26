<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    title: string;
    message: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-amber-50 to-white px-4 py-8">
        <div class="mx-auto grid w-full max-w-xl gap-6">
            <Card class="rounded-2xl border bg-white shadow-sm">
                <CardHeader>
                    <CardTitle class="text-2xl">{{ title }}</CardTitle>
                    <CardDescription>{{ message }}</CardDescription>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    Scan a QR code first, then open the link from the QR to continue.
                </CardContent>
            </Card>
        </div>
    </div>
</template>
