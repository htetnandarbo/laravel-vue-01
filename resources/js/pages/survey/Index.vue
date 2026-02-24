<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { demo as wishDemo } from '@/routes/wish';
import { ImagePlus, Trash2, UploadCloud } from 'lucide-vue-next';

type SurveyForm = {
    fullName: string;
    favoriteProduct: string;
    eventDate: string;
    monthlyBudget: number | null;
    interests: string[];
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
    favoriteProduct: '',
    eventDate: '',
    monthlyBudget: null,
    interests: [],
    city: '',
    visitGoal: '',
    satisfaction: '',
    preferredContact: '',
    recommendUs: '',
    improvements: '',
    acceptUpdates: true,
    agreeDemo: false,
});
const referenceImageFile = ref<File | null>(null);
const referenceImagePreviewUrl = ref<string | null>(null);
const referenceImageInput = ref<HTMLInputElement | null>(null);

const canProceedToWish = computed(() => {
    return Boolean(form.fullName && form.visitGoal && form.satisfaction && form.preferredContact && form.recommendUs);
});

const nextStep = () => {
    if (!canProceedToWish.value) return;
    router.visit(wishDemo());
};

const toggleInterest = (value: string, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;

    if (isChecked && !form.interests.includes(value)) {
        form.interests = [...form.interests, value];
        return;
    }

    if (!isChecked) {
        form.interests = form.interests.filter((item) => item !== value);
    }
};

const onReferenceImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    referenceImageFile.value = file;

    if (referenceImagePreviewUrl.value) {
        URL.revokeObjectURL(referenceImagePreviewUrl.value);
        referenceImagePreviewUrl.value = null;
    }

    if (file) {
        referenceImagePreviewUrl.value = URL.createObjectURL(file);
    }
};

const clearReferenceImage = () => {
    referenceImageFile.value = null;

    if (referenceImagePreviewUrl.value) {
        URL.revokeObjectURL(referenceImagePreviewUrl.value);
        referenceImagePreviewUrl.value = null;
    }

    if (referenceImageInput.value) {
        referenceImageInput.value.value = '';
    }
};

onBeforeUnmount(() => {
    if (referenceImagePreviewUrl.value) {
        URL.revokeObjectURL(referenceImagePreviewUrl.value);
    }
});
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
                            <Label for="referenceImage">Upload a reference image (optional)</Label>
                            <div class="rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/60 p-4">
                            <label
                                for="referenceImage"
                                class="group block cursor-pointer transition hover:opacity-95"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="rounded-xl bg-white p-2 text-amber-600 shadow-sm">
                                        <ImagePlus class="size-5" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                            <UploadCloud class="size-4 text-amber-600" />
                                            Add Image
                                        </div>
                                        <p class="mt-1 text-xs text-slate-600">
                                            Upload a photo or screenshot so we can understand your preference better (demo only).
                                        </p>
                                        <p class="mt-2 text-xs font-medium text-amber-700">
                                            {{ referenceImageFile ? `Selected: ${referenceImageFile.name}` : 'Click here to choose an image file' }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="referenceImagePreviewUrl" class="mt-3 overflow-hidden rounded-xl border bg-white">
                                    <img :src="referenceImagePreviewUrl" alt="Reference preview" class="max-h-48 w-full object-cover" />
                                </div>
                            </label>

                            <div v-if="referenceImageFile" class="mt-3 flex justify-end">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50"
                                    @click="clearReferenceImage"
                                >
                                    <Trash2 class="size-3.5" />
                                    Remove image
                                </button>
                            </div>
                            </div>
                            <Input id="referenceImage" ref="referenceImageInput" type="file" accept="image/*" class="hidden" @change="onReferenceImageChange" />
                        </div>
                        

                        <div class="grid gap-2">
                            <Label for="fullName">What is your full name? * </Label>
                            <Input id="fullName" v-model="form.fullName" type="text" placeholder="John Doe" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="favoriteProduct">Which product are you interested in most? </Label>
                            <Input id="favoriteProduct" v-model="form.favoriteProduct" type="text" placeholder="Wireless earbuds" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="eventDate">When do you plan to visit or purchase? </Label>
                            <Input id="eventDate" v-model="form.eventDate" type="date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="monthlyBudget">What is your estimated monthly budget? </Label>
                            <Input id="monthlyBudget" v-model="form.monthlyBudget" type="number" min="0" step="1" placeholder="100" />
                        </div>


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
                            <Label for="city">Which city are you from? </Label>
                            <Input id="city" v-model="form.city" type="text" placeholder="Yangon" />
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
                            <Label>What are you interested in today? (checkbox)</Label>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label
                                    v-for="interest in ['Promotions', 'New arrivals', 'Support', 'Membership']"
                                    :key="interest"
                                    class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >
                                    <Checkbox
                                        :id="`interest-${interest.toLowerCase().replaceAll(' ', '-')}`"
                                        :checked="form.interests.includes(interest)"
                                        @update:checked="(value) => toggleInterest(interest, value)"
                                    />
                                    <span class="font-medium text-slate-800">{{ interest }}</span>
                                </label>
                            </div>
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
