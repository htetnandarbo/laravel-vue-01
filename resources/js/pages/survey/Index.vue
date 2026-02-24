<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    return Boolean(form.fullName && form.visitGoal && form.satisfaction);
});

const nextStep = () => {
    // if (!canProceedToWish.value) return;
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
                <CardHeader class="bg-white">
                    <CardTitle class="text-lg text-slate-900 sm:text-xl">Survey Questions</CardTitle>
                    <CardDescription class="text-sm text-slate-600">
                        Fill this survey, then tap Next to continue to the wish & get luck.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6 bg-white">
                    <div class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="referenceImage">ပုံထည့်ရန်</Label>
                            <div class="rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/60 p-4">
                                <label for="referenceImage" class="group block cursor-pointer transition hover:opacity-95">
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-xl bg-white p-2 text-amber-600 shadow-sm">
                                            <ImagePlus class="size-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                                <UploadCloud class="size-4 text-amber-600" />
                                                Add Image
                                            </div>
                                            <p class="mt-2 text-xs font-medium text-amber-700">
                                                {{
                                                    referenceImageFile ? `Selected: ${referenceImageFile.name}` : 'Click here to choose an image file'
                                                }}
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
                            <Input
                                id="referenceImage"
                                ref="referenceImageInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onReferenceImageChange"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="fullName">သင့်နာမည် ကဘာလဲ? </Label>
                            <Input id="fullName" v-model="form.fullName" type="text" placeholder="မစုမွန်" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="favoriteProduct">သင်အကြိုက်ဆုံး ပစ္စည်းကဘယ်လိုပစ္စည်းလဲ? </Label>
                            <Input id="favoriteProduct" v-model="form.favoriteProduct" type="text" placeholder="Wireless earbuds" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="eventDate">သင် ဘယ်အချိန်ဝယ်လိုက်တာလဲ? </Label>
                            <Input id="eventDate" v-model="form.eventDate" type="date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="monthlyBudget"> 1 to 10 အမှတ်ပေးမယ်ဆိုရင်ရော? </Label>
                            <Input id="monthlyBudget" v-model="form.monthlyBudget" type="number" min="0" step="1" placeholder="8" />
                        </div>

                        <div class="grid gap-2">
                            <Label>သင် ဘယ်အကြောင်းကြောင့် ဝင်လာခဲ့တယ်လဲ?</Label>
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
                            <Label for="city">သင် ဘယ်မြို့ကလဲ? </Label>
                            <Input id="city" v-model="form.city" type="text" placeholder="Yangon" />
                        </div>

                        <div class="grid gap-2">
                            <Label>သင့်ရဲ့ စိတ်ကျေနပ်မူက?</Label>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <button
                                    v-for="option in ['Satisfied', 'Neutral', 'Unsatisfied']"
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
                            <Label>ဒီနေ စိတ်အဝင်စားဆုံးက?</Label>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label
                                    v-for="interest in ['Promotions', 'New arrivals', 'Membership']"
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
                            <Label for="improvements">ကျွနိုပ်တို့ ဘာများ Improve လုပ်ရမလဲ?</Label>
                            <Textarea id="improvements" v-model="form.improvements" rows="4" placeholder="Message..." />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <div />
                        <!-- :disabled="!canProceedToWish" -->
                        <Button
                            type="button"
                            class="w-full cursor-pointer rounded-xl bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
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
