<script setup>
defineProps({
    isOpen: { type: Boolean, required: true },
    title:  { type: String,  default: null },
})

defineEmits(['close'])
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
            >
                <!-- Overlay -->
                <div
                    class="absolute inset-0 bg-slate-900/50"
                    @click="$emit('close')"
                />

                <!-- Card -->
                <div class="relative w-full max-w-md bg-white rounded-md shadow-xl flex flex-col">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                        <slot name="title">
                            <span class="text-base font-medium text-slate-700">{{ title }}</span>
                        </slot>
                        <button
                            @click="$emit('close')"
                            class="ml-4 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                            aria-label="Fechar"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div
                        v-if="$slots.footer"
                        class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3"
                    >
                        <slot name="footer" />
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.18s ease;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
