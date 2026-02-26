<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{ qr: any; questions: any[] }>();

const initialAnswers: Record<string, any> = {};
for (const question of props.questions) {
    initialAnswers[String(question.id)] = question.type === 'checkbox' ? [] : '';
}

const form = useForm({
    user_identifier: '',
    answers: initialAnswers,
});

const wishForm = useForm({
    message: '',
});

const submitForm = () => form.post(`/qr/${props.qr.token}/submit`, { preserveScroll: true });
const submitWish = () => wishForm.post(`/qr/${props.qr.token}/wish`, { preserveScroll: true, onSuccess: () => wishForm.reset() });
const answerError = (id: number) => (form.errors as Record<string, string>)[`answers.${id}`];
const toggleCheckboxAnswer = (questionId: number, option: string, checked: boolean | 'indeterminate') => {
    const key = String(questionId);
    const current = Array.isArray(form.answers[key]) ? [...form.answers[key]] : [];

    if (checked === true) {
        if (!current.includes(option)) current.push(option);
    } else {
        form.answers[key] = current.filter((value: string) => value !== option);
        return;
    }

    form.answers[key] = current;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-amber-50 to-white px-4 py-8">
        <div class="mx-auto grid w-full max-w-3xl gap-6">
            <Card class="rounded-2xl border bg-white shadow-sm">
                <CardHeader>
                    <CardTitle class="text-2xl">{{ qr.name || 'QR Form' }}</CardTitle>
                    <CardDescription>Token: {{ qr.token }}</CardDescription>
                </CardHeader>

                <CardContent>
                <form class="grid gap-4" @submit.prevent="submitForm">
                    <div class="grid gap-1">
                        <Label class="text-sm font-medium">User Identifier (optional)</Label>
                        <Input v-model="form.user_identifier" />
                        <InputError :message="form.errors.user_identifier" />
                    </div>

                    <div v-for="question in questions" :key="question.id" class="grid gap-2 rounded-lg border p-4">
                        <Label class="text-sm font-medium">
                            {{ question.label }} <span v-if="question.is_required" class="text-red-600">*</span>
                        </Label>

                        <Input v-if="question.type === 'text'" v-model="form.answers[String(question.id)]" />
                        <Input v-else-if="question.type === 'number'" v-model="form.answers[String(question.id)]" type="number" />
                        <Textarea v-else-if="question.type === 'textarea'" v-model="form.answers[String(question.id)]" class="min-h-24" />

                        <Select v-else-if="question.type === 'select'" v-model="form.answers[String(question.id)]">
                            <SelectTrigger>
                                <SelectValue placeholder="Select..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in question.options" :key="option" :value="option">{{ option }}</SelectItem>
                            </SelectContent>
                        </Select>

                        <div v-else-if="question.type === 'checkbox'" class="grid gap-2">
                            <label v-for="option in question.options" :key="option" class="flex items-center gap-2 text-sm">
                                <Checkbox
                                    :checked="Array.isArray(form.answers[String(question.id)]) && form.answers[String(question.id)].includes(option)"
                                    @update:checked="(value) => toggleCheckboxAnswer(question.id, option, value)"
                                />
                                <span>{{ option }}</span>
                            </label>
                        </div>

                        <Input v-else-if="question.type === 'date'" v-model="form.answers[String(question.id)]" type="date" />

                        <InputError :message="answerError(question.id)" />
                    </div>

                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="form.processing" class="bg-amber-500 text-white hover:bg-amber-600">
                            {{ form.processing ? 'Submitting...' : 'Submit Form' }}
                        </Button>
                        <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Submitted.</span>
                    </div>
                </form>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border bg-white shadow-sm">
                <CardHeader>
                    <CardTitle class="text-xl">Send a Wish</CardTitle>
                </CardHeader>
                <CardContent>
                <form class="grid gap-3" @submit.prevent="submitWish">
                    <Textarea v-model="wishForm.message" class="min-h-28" placeholder="Write your wish..." />
                    <InputError :message="wishForm.errors.message" />
                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="wishForm.processing" class="bg-slate-900 text-white hover:bg-slate-700">
                            {{ wishForm.processing ? 'Sending...' : 'Submit Wish' }}
                        </Button>
                        <span v-if="wishForm.recentlySuccessful" class="text-sm text-emerald-600">Wish submitted.</span>
                    </div>
                </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
