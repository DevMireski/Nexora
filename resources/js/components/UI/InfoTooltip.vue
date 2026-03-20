<script setup>
import { ref } from 'vue'

const visible = ref(false)
</script>

<template>
    <span class="relative inline-flex items-center">
        <button
            type="button"
            class="h-4 w-4 rounded-full bg-slate-200 text-slate-500 text-xs font-bold flex items-center justify-center hover:bg-slate-300 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1"
            @mouseenter="visible = true"
            @mouseleave="visible = false"
            @focus="visible = true"
            @blur="visible = false"
        >i</button>

        <Transition
            enter-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="absolute left-6 top-1/2 -translate-y-1/2 z-50 w-64 rounded-md bg-slate-800 text-white text-xs p-3 shadow-xl leading-relaxed pointer-events-none"
            >
                <slot />
            </div>
        </Transition>
    </span>
</template>
