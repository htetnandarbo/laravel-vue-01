<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { watch } from 'vue';

import { Bold, Heading1, Heading2, Italic, List, ListOrdered, Minus, Quote, Redo, Undo } from 'lucide-vue-next';

/* Props */
const props = defineProps<{
    modelValue: string;
}>();

/* Emits */
const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

/* Editor */
const editor = useEditor({
    editorProps: {
        attributes: {
            class: 'prose border p-4 rounded-b min-h-[9rem] max-h-[12rem] overflow-y-auto outline-none',
        },
    },
    extensions: [StarterKit],
    content: props.modelValue,
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

/* Keep editor in sync if parent updates value */
watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) return;
        if (editor.value.getHTML() !== value) {
            editor.value.commands.setContent(value, false);
        }
    },
);
</script>

<template>
    <div v-if="editor" class="rounded border">
        <!-- Toolbar -->
        <div class="flex flex-wrap gap-2 border-b p-2">
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('bold') }"
                class="p-1"
            >
                <Bold class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('italic') }"
                class="p-1"
            >
                <Italic class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('heading', { level: 1 }) }"
                class="p-1"
            >
                <Heading1 class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('heading', { level: 2 }) }"
                class="p-1"
            >
                <Heading2 class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('bulletList') }"
                class="p-1"
            >
                <List class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('orderedList') }"
                class="p-1"
            >
                <ListOrdered class="size-5" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'rounded bg-gray-200': editor.isActive('blockquote') }"
                class="p-1"
            >
                <Quote class="size-5" />
            </button>

            <button type="button" @click="editor.chain().focus().setHorizontalRule().run()" class="p-1">
                <Minus class="size-5" />
            </button>

            <button type="button" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" class="p-1">
                <Undo class="size-5" />
            </button>

            <button type="button" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" class="p-1">
                <Redo class="size-5" />
            </button>
        </div>

        <!-- Editor -->
        <EditorContent :editor="editor" />
    </div>
</template>
