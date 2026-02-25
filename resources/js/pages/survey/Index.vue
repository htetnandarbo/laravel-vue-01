<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { demo as wishDemo } from '@/routes/wish';
import { Head, router, useForm } from '@inertiajs/vue3';

type Question = {
    id: number;
    label: string;
    type: 'text' | 'number' | 'textarea' | 'select' | 'checkbox' | 'date';
    is_required: boolean;
    options: string[];
    sort_order: number;
};

type QrPayload = {
    id: number;
    token: string;
    name: string | null;
    status: string;
};

const props = defineProps<{
    qr?: QrPayload | null;
    questions?: Question[] | null;
}>();

const questions = (props.questions ?? []).slice().sort((a, b) => Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0));
const hasQr = !!props.qr?.token;

const initialAnswers: Record<string, any> = {};
for (const q of questions) initialAnswers[String(q.id)] = q.type === 'checkbox' ? [] : '';

const form = useForm({
    user_identifier: '',
    answers: initialAnswers,
});

const answerError = (id: number) => (form.errors as Record<string, string>)[`answers.${id}`];

const toggleCheckboxAnswer = (questionId: number, option: string, checked: boolean | 'indeterminate') => {
    const key = String(questionId);
    const current = Array.isArray(form.answers[key]) ? [...form.answers[key]] : [];
    form.answers[key] = checked === true ? Array.from(new Set([...current, option])) : current.filter((v: string) => v !== option);
};

const nextStep = () => {
    if (!props.qr?.token) return;

    form.post(`/qr/${props.qr.token}/submit`, {
        preserveScroll: true,
        onSuccess: () => router.visit(wishDemo({ query: { qr: props.qr!.token } })),
    });
};

</script>

<template>
    <Head :title="hasQr ? 'Survey Questions' : 'QR Required'" />

    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-orange-50 px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto w-full max-w-3xl">
            <Card class="overflow-hidden rounded-3xl border-0 shadow-lg shadow-orange-100/70">
                <CardHeader class="bg-white">
                    <CardTitle class="text-lg text-slate-900 sm:text-xl">{{ hasQr ? 'Survey Questions' : 'QR Required' }}</CardTitle>
                    <CardDescription class="text-sm text-slate-600">
                        <template v-if="hasQr">Fill this survey, then tap Next to continue to the wish & get luck.</template>
                        <template v-else>Scan a QR code first to enter this survey.</template>
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6 bg-white">
                    <div v-if="!hasQr" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        You need a valid QR link to open this survey.
                    </div>

                    <div v-else class="space-y-6">
                        <div>
                            <img
                                src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80"
                                alt="Survey Header"
                                class="h-48 w-full rounded-lg object-cover"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="user_identifier">User Identifier (optional)</Label>
                            <Input id="user_identifier" v-model="form.user_identifier" type="text" placeholder="Phone / Name / ID" />
                            <InputError :message="form.errors.user_identifier" />
                        </div>

                        <div v-for="question in questions" :key="question.id" class="grid gap-2">
                            <Label>
                                {{ question.label }}
                                <span v-if="question.is_required" class="text-red-600">*</span>
                            </Label>

                            <Input v-if="question.type === 'text'" v-model="form.answers[String(question.id)]" type="text" />
                            <Input v-else-if="question.type === 'number'" v-model="form.answers[String(question.id)]" type="number" />
                            <Textarea v-else-if="question.type === 'textarea'" v-model="form.answers[String(question.id)]" rows="4" />

                            <Select v-else-if="question.type === 'select'" v-model="form.answers[String(question.id)]">
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose one" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in question.options" :key="option" :value="option">{{ option }}</SelectItem>
                                </SelectContent>
                            </Select>

                            <div v-else-if="question.type === 'checkbox'" class="grid gap-2 sm:grid-cols-2">
                                <label
                                    v-for="option in question.options"
                                    :key="option"
                                    class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >
                                    <Checkbox
                                        :checked="Array.isArray(form.answers[String(question.id)]) && form.answers[String(question.id)].includes(option)"
                                        @update:checked="(value) => toggleCheckboxAnswer(question.id, option, value)"
                                    />
                                    <span class="font-medium text-slate-800">{{ option }}</span>
                                </label>
                            </div>

                            <Input v-else-if="question.type === 'date'" v-model="form.answers[String(question.id)]" type="date" />

                            <InputError :message="answerError(question.id)" />
                        </div>
                    </div>

                    <div v-if="hasQr" class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <InputError :message="(form.errors as Record<string, string>).answers" />
                        </div>
                        <Button
                            type="button"
                            :disabled="form.processing"
                            class="w-full cursor-pointer rounded-xl bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                            @click="nextStep"
                        >
                            {{ form.processing ? 'Submitting...' : 'Next' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
