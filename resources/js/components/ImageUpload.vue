<script setup lang="ts">
import { UploadIcon, XIcon } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';

type Props = {
    label: string;
    modelValue: File | null;
    existingImage?: string | null;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
}>();

const preview = ref<string>('');
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => {
    fileInput.value?.click();
};

// show old image on edit page
onMounted(() => {
    if (props.existingImage) {
        preview.value = props.existingImage;
    }
});

// in case props change
watch(
    () => props.existingImage,
    (val) => {
        if (val && !preview.value) {
            preview.value = val;
        }
    },
);

const handleFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Only image files allowed');
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert('Maximum file size is 10MB');
        return;
    }

    // preview new image
    const reader = new FileReader();
    reader.onload = () => {
        preview.value = reader.result as string;
    };
    reader.readAsDataURL(file);

    emit('update:modelValue', file);
};

const removeFile = () => {
    preview.value = '';
    if (fileInput.value) fileInput.value.value = '';
    emit('update:modelValue', null);
};
</script>

<template>
    <div class="grid gap-2">
        <label class="text-sm font-medium">{{ label }}</label>

        <!-- Upload box -->
        <div
            v-if="!preview"
            class="flex h-40 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 hover:border-gray-400 sm:h-48"
            @click="triggerFileInput"
        >
            <UploadIcon class="mb-2 h-6 w-6 text-emerald-600" />
            <p class="text-sm">Tap to upload</p>
        </div>

        <!-- Preview -->
        <div v-else class="overflow-hidden rounded-2xl border bg-gray-50">
            <img :src="preview" class="h-40 w-full object-cover sm:h-48" />
            <div class="flex justify-between p-3">
                <span class="truncate text-sm">
                    {{ modelValue?.name ?? 'Existing image' }}
                </span>
                <button type="button" @click="removeFile">
                    <XIcon class="h-5 w-5 cursor-pointer text-gray-500 hover:text-red-600" />
                </button>
            </div>
        </div>

        <input ref="fileInput" type="file" class="hidden" accept="image/*" @change="handleFileSelect" />
    </div>
</template>
