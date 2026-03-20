<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    label:      { type: String, default: null },
    type:       { type: String, default: 'text' },
    placeholder:{ type: String, default: '' },
    error:      { type: String, default: null },
    disabled:   { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <div>
        <label
            v-if="label"
            class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide"
        >
            {{ label }}
        </label>

        <input
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            @input="$emit('update:modelValue', $event.target.value)"
            :class="[
                'w-full rounded-md border px-3 py-2 text-sm text-slate-700',
                'placeholder:text-slate-300 transition-colors',
                'focus:outline-none focus:ring-2 focus:ring-offset-0',
                'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-100',
                error
                    ? 'border-red-400 bg-red-50 focus:ring-red-300 focus:border-red-400'
                    : 'border-slate-300 bg-slate-50 focus:ring-slate-400 focus:border-slate-400 focus:bg-white',
            ]"
        />

        <p v-if="error" class="mt-1.5 text-xs text-red-500">
            {{ error }}
        </p>
    </div>
</template>
