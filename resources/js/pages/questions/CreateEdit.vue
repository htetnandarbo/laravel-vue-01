<script setup lang="ts">
import { index, store, update } from '@/actions/App/Http/Controllers/QuestionController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import Select from '@/components/ui/select/Select.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import SelectLabel from '@/components/ui/select/SelectLabel.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import Switch from '@/components/ui/switch/Switch.vue';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
const props = defineProps<{
    question: any;
}>();

const form = useForm({
    question_text: props.question.question_text,
    type: props.question.type,
    is_required: props.question.is_required,
    order: props.question.order,
    options: props.question.options,
});

const submit = () => {
    if (props.question.id) {
        form.submit(update(props.question.id));
    } else {
        form.submit(store());
    }
};

const showOptions = computed(() => ['radio', 'checkbox', 'select'].includes(form.type));

const addOption = () => {
    form.options.push({
        option_text: '',
    });
};

const removeOption = (index: any) => {
    form.options.splice(index, 1);
};
</script>

<template>
    <AppLayout>
        <div class="ms-5 mt-2">
            <Link :href="index()">
                <Button variant="link" class="h-fit cursor-pointer gap-1 !p-0"> <ArrowLeft class="size-4" /> <span>Back</span></Button>
            </Link>
        </div>

        <div class="m-5 max-w-2xl flex-1 rounded-xl border p-5 lg:m-5">
            <form @submit.prevent="submit">
                <div class="flex flex-col gap-6">
                    <!-- Question -->
                    <div class="grid gap-2">
                        <Label for="question">Question</Label>
                        <Textarea placeholder="Enter your question..." rows="3" v-model="form.question_text" />
                    </div>
                    <InputError :message="form.errors.question_text"></InputError>

                    <!-- Type -->
                    <div class="grid gap-2">
                        <Label for="type">Type</Label>
                        <Select v-model="form.type">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a question type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Types</SelectLabel>
                                    <SelectItem value="text">Text</SelectItem>
                                    <SelectItem value="textarea">Textarea</SelectItem>
                                    <SelectItem value="radio">Radio</SelectItem>
                                    <SelectItem value="checkbox">Checkbox</SelectItem>
                                    <SelectItem value="select">Select</SelectItem>
                                    <SelectItem value="number">Number</SelectItem>
                                    <SelectItem value="date">Date</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.type"></InputError>
                    </div>

                    <div v-if="showOptions" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label>Options</Label>
                            <Button @click.prevent="addOption" variant="outline" class="cursor-pointer">+ Add Option</Button>
                        </div>

                        <div v-for="(option, index) in form.options" :key="index" class="flex gap-2">
                            <Input v-model="option.option_text" placeholder="Option text" />
                            <Button variant="destructive" size="sm" @click.prevent="removeOption(index)" class="cursor-pointer"> ✕ </Button>
                        </div>
                    </div>

                    <!-- Order -->
                    <div class="grid gap-2">
                        <Label>Order</Label>
                        <Input type="number" v-model="form.order" />
                    </div>

                    <!-- Required -->
                    <div class="flex items-center gap-2">
                        <Label for="required">Required</Label>
                        <Switch
                            id="airplane-mode"
                            class="focus:ring-amber-500 data-[state=checked]:bg-emerald-600"
                            v-model:checked="form.is_required"
                        />
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <Button class="w-full cursor-pointer bg-amber-500 text-white transition-all hover:bg-amber-600 sm:w-auto"> Submit </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
