<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { demo as wishDemo } from '@/routes/wish';

type SurveyForm = {
    fullName: string;
    email: string;
    phone: string;
    ageRange: string;
    gender: string;
    city: string;
    visitGoal: string;
    satisfaction: string;
    preferredContact: string;
    recommendUs: string;
    improvements: string;
    acceptUpdates: boolean;
    agreeDemo: boolean;
};

const form = reactive<SurveyForm>({
    fullName: '',
    email: '',
    phone: '',
    ageRange: '',
    gender: '',
    city: '',
    visitGoal: '',
    satisfaction: '',
    preferredContact: '',
    recommendUs: '',
    improvements: '',
    acceptUpdates: true,
    agreeDemo: false,
});

const canProceedToWish = computed(() => {
    return Boolean(form.visitGoal && form.satisfaction && form.preferredContact && form.recommendUs);
});

const nextStep = () => {
    if (!canProceedToWish.value) return;
    router.visit(wishDemo());
};
</script>

<template>
    <Head title="Survey Demo" />

    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-orange-50 px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto w-full max-w-3xl">
            <Card class="overflow-hidden rounded-3xl border-0 shadow-lg shadow-orange-100/70">
                <CardHeader class="bg-white pb-4">
                    <CardTitle class="text-lg text-slate-900 sm:text-xl">Survey Questions</CardTitle>
                    <CardDescription class="text-sm text-slate-600">
                        Fill this sample survey, then tap Next to continue to the wish & luck demo screen.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6 bg-white p-4 sm:p-6">
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label>What is your main reason for visiting us? *</Label>
                            <Select v-model="form.visitGoal">
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose one" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Goals</SelectLabel>
                                        <SelectItem value="buy-product">Buy a product</SelectItem>
                                        <SelectItem value="compare-options">Compare options</SelectItem>
                                        <SelectItem value="book-service">Book a service</SelectItem>
                                        <SelectItem value="support">Get support</SelectItem>
                                        <SelectItem value="just-browsing">Just browsing</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label>How satisfied are you with the current experience? *</Label>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <button
                                    v-for="option in ['Very satisfied', 'Satisfied', 'Neutral', 'Unsatisfied']"
                                    :key="option"
                                    type="button"
                                    class="rounded-xl border px-4 py-3 text-left text-sm transition-all"
                                    :class="
                                        form.satisfaction === option
                                            ? 'border-amber-400 bg-amber-50 text-amber-800 shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'
                                    "
                                    @click="form.satisfaction = option"
                                >
                                    {{ option }}
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Preferred contact method *</Label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <button
                                    v-for="method in ['Email', 'Phone', 'WhatsApp', 'SMS']"
                                    :key="method"
                                    type="button"
                                    class="rounded-xl border px-3 py-2 text-sm font-medium transition-all"
                                    :class="
                                        form.preferredContact === method
                                            ? 'border-orange-400 bg-orange-50 text-orange-800'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'
                                    "
                                    @click="form.preferredContact = method"
                                >
                                    {{ method }}
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Would you recommend us to a friend or colleague? *</Label>
                            <RadioGroup v-model="form.recommendUs" class="gap-2">
                                <label
                                    v-for="option in ['Yes', 'Maybe', 'No']"
                                    :key="option"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition hover:border-slate-300"
                                >
                                    <RadioGroupItem :id="`recommend-${option.toLowerCase()}`" :value="option" />
                                    <div>
                                        <p class="font-medium text-slate-800">{{ option }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{
                                                option === 'Yes'
                                                    ? 'Positive intent to refer others'
                                                    : option === 'Maybe'
                                                      ? 'Needs more confidence before recommending'
                                                      : 'Not satisfied enough to recommend'
                                            }}
                                        </p>
                                    </div>
                                </label>
                            </RadioGroup>
                        </div>

                        <div class="grid gap-2">
                            <Label for="improvements">What should we improve? (optional)</Label>
                            <Textarea
                                id="improvements"
                                v-model="form.improvements"
                                rows="4"
                                placeholder="Example: faster checkout, clearer pricing, better support response..."
                            />
                        </div>

                        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <Checkbox id="acceptUpdates" v-model:checked="form.acceptUpdates" />
                            <Label for="acceptUpdates" class="cursor-pointer leading-5">
                                I would like to receive updates or follow-up communication (demo option).
                            </Label>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <div />

                        <Button
                            type="button"
                            class="w-full cursor-pointer rounded-xl bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                            :disabled="!canProceedToWish"
                            @click="nextStep"
                        >
                            Next
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
