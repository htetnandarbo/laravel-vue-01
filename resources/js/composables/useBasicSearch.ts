import { router } from '@inertiajs/vue3';
import { Ref, watch } from 'vue';

export function useBasicSearch(url: string, q: Ref<string>) {
    let timeoutId: number | null = null;

    watch(
        q,
        (value) => {
            if (!url) return;

            if (timeoutId) {
                window.clearTimeout(timeoutId);
            }

            timeoutId = window.setTimeout(() => {
                const search = String(value ?? '').trim();

                router.get(
                    url,
                    search ? { search } : {},
                    {
                        preserveScroll: true,
                        preserveState: true,
                        replace: true,
                    },
                );
            }, 350);
        },
        { flush: 'post' },
    );
}

