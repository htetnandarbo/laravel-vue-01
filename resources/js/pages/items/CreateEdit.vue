<script setup lang="ts">
import { index, store, update } from '@/actions/App/Http/Controllers/ItemController';
import ImageUpload from '@/components/ImageUpload.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
const props = defineProps<{
    item: any;
}>();

const form = useForm({
    name: props.item.name,
    image: props.item.image,
});

const page = usePage();
const errors = computed(() => page.props.errors);
const submit = () => {
    if (props.item.id) {
        router.post(
            update(props.item.id),
            { ...(form as any), _method: 'put' },
            {
                onError: () => {
                    form.errors = errors.value;
                },
            },
        );
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
            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="flex flex-col gap-6">
                    <!-- Name -->
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" type="text" placeholder="Name" v-model="form.name" />
                        <InputError :message="form.errors.name"></InputError>
                    </div>

                    <!-- Image -->
                    <div class="grid gap-2">
                        <ImageUpload label="Image" v-model="form.image" :existing-image="item.image ? `/storage/${item.image}` : null" />
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
