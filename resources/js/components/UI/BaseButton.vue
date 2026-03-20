<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger'].includes(v),
    },
    type:     { type: String,  default: 'button' },
    disabled: { type: Boolean, default: false },
})

const VARIANT_CLASSES = {
    primary:   'bg-slate-800 text-white hover:bg-slate-700 active:bg-slate-900',
    secondary: 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 active:bg-slate-100',
    danger:    'bg-red-600 text-white hover:bg-red-500 active:bg-red-700',
}
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="[
            'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium',
            'transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1',
            'disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none',
            VARIANT_CLASSES[variant],
            variant === 'primary'   ? 'focus:ring-slate-500' : '',
            variant === 'secondary' ? 'focus:ring-slate-400' : '',
            variant === 'danger'    ? 'focus:ring-red-400'   : '',
        ]"
    >
        <slot />
    </button>
</template>
