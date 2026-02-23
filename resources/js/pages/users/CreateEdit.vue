<script setup lang="ts">
import { index, store , update } from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
const props = defineProps<{
    user:any;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
});

const submit = () => {
   if(props.user.id){
    form.submit(update(props.user.id));
   }else{
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

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" placeholder="Email" v-model="form.email" />
                        <InputError :message="form.errors.email"></InputError>
                    </div>

                    <!-- Password -->
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input id="password" type="text" placeholder="Password" v-model="form.password" />
                        <InputError :message="form.errors.password"></InputError>
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
