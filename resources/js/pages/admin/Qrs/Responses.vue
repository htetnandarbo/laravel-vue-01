<script setup lang="ts">
import ManageSectionContent from '@/components/admin/qrs/ManageSectionContent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import QrManageLayout from '@/layouts/admin/qrs/Layout.vue';

defineProps<{ qr: any; questionTypes: string[] }>();
</script>

<template>
    <AppLayout>
        <QrManageLayout :qr="qr" section="responses">
            <ManageSectionContent :qr="qr" :question-types="questionTypes" section="responses" />
        </QrManageLayout>
    </AppLayout>
</template>
