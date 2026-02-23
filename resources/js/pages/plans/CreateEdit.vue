<script setup lang="ts">
import { index, store, update } from '@/actions/App/Http/Controllers/PlanController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
const props = defineProps<{
    plan: any;
}>();

const form = useForm({
    name: props.plan.name,
    display_name: props.plan.display_name,
    detail: props.plan.detail,
});

const submit = () => {
    if (props.plan.id) {
        form.submit(update(props.plan.id));
    } else {
        form.submit(store());
    }
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
                    <!-- Name -->
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" type="text" placeholder="Name" v-model="form.name" />
                        <InputError :message="form.errors.name"></InputError>
                    </div>

                    <!-- Display Name -->
                    <div class="grid gap-2">
                        <Label for="name">Display Name</Label>
                        <Input id="name" type="text" placeholder="Display Name" v-model="form.display_name" />
                        <InputError :message="form.errors.display_name"></InputError>
                    </div>

                    <!-- Detail -->

                    <div class="grid gap-2">
                        <Label for="Detail">Detail</Label>
                        <Textarea placeholder="Type your message here." rows="3" v-model="form.detail" />
                    </div>
                    <InputError :message="form.errors.detail"></InputError>

                    <!-- Submit Button -->
                    <div>
                        <Button class="w-full cursor-pointer bg-amber-500 text-white transition-all hover:bg-amber-600 sm:w-auto"> Submit </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
