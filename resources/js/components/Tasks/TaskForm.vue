<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseInput from '../UI/BaseInput.vue'
import BaseButton from '../UI/BaseButton.vue'

const props = defineProps({
    task: { type: Object, default: null },
})

const emit = defineEmits(['saved'])

const form = ref({
    title: '',
    description: '',
    status: 'pending',
    due_date: '',
})

const errors = ref({})
const submitting = ref(false)

watch(
    () => props.task,
    (task) => {
        if (task) {
            form.value = {
                title:       task.title,
                description: task.description ?? '',
                status:      task.status,
                due_date:    task.due_date ? task.due_date.slice(0, 16) : '',
            }
        } else {
            form.value = { title: '', description: '', status: 'pending', due_date: '' }
        }
        errors.value = {}
    },
    { immediate: true }
)

function authHeaders() {
    const token = localStorage.getItem('nexora_token')
    return { Authorization: `Bearer ${token}` }
}

async function submit() {
    errors.value = {}
    submitting.value = true

    const payload = { ...form.value }
    if (!payload.due_date) delete payload.due_date

    try {
        const isEdit = !!props.task
        const url    = isEdit ? `/api/v1/tasks/${props.task.id}` : '/api/v1/tasks'
        const method = isEdit ? 'put' : 'post'
        const { data } = await axios[method](url, payload, { headers: authHeaders() })
        emit('saved', data.data)
    } catch (e) {
        if (e.response?.status === 422) {
            const bag = e.response.data?.errors ?? {}
            errors.value = Object.fromEntries(
                Object.entries(bag).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
            )
        } else {
            errors.value._general = 'Erro ao salvar tarefa. Tente novamente.'
        }
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <form @submit.prevent="submit" novalidate>
        <div class="flex flex-col gap-4">
            <p v-if="errors._general" class="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-600">
                {{ errors._general }}
            </p>

            <BaseInput
                v-model="form.title"
                label="Título"
                placeholder="Nome da tarefa"
                :error="errors.title"
            />

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                    Descrição
                </label>
                <textarea
                    v-model="form.description"
                    placeholder="Descrição opcional"
                    rows="3"
                    :class="[
                        'w-full rounded-md border px-3 py-2 text-sm text-slate-700 resize-none',
                        'placeholder:text-slate-300 transition-colors',
                        'focus:outline-none focus:ring-2 focus:ring-offset-0',
                        errors.description
                            ? 'border-red-400 bg-red-50 focus:ring-red-300 focus:border-red-400'
                            : 'border-slate-300 bg-slate-50 focus:ring-slate-400 focus:border-slate-400 focus:bg-white',
                    ]"
                />
                <p v-if="errors.description" class="mt-1.5 text-xs text-red-500">{{ errors.description }}</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                    Status
                </label>
                <select
                    v-model="form.status"
                    :class="[
                        'w-full rounded-md border px-3 py-2 text-sm text-slate-700',
                        'transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0',
                        errors.status
                            ? 'border-red-400 bg-red-50 focus:ring-red-300'
                            : 'border-slate-300 bg-slate-50 focus:ring-slate-400 focus:border-slate-400 focus:bg-white',
                    ]"
                >
                    <option value="pending">Pendente</option>
                    <option value="progress">Em andamento</option>
                    <option value="done">Concluída</option>
                </select>
                <p v-if="errors.status" class="mt-1.5 text-xs text-red-500">{{ errors.status }}</p>
            </div>

            <!-- Due date — triggers Google Calendar sync if user has calendar connected -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                    Data Limite
                </label>
                <input
                    v-model="form.due_date"
                    type="datetime-local"
                    :class="[
                        'w-full rounded-md border px-3 py-2 text-sm text-slate-700',
                        'transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0',
                        errors.due_date
                            ? 'border-red-400 bg-red-50 focus:ring-red-300 focus:border-red-400'
                            : 'border-slate-300 bg-slate-50 focus:ring-slate-400 focus:border-slate-400 focus:bg-white',
                    ]"
                />
                <p v-if="errors.due_date" class="mt-1.5 text-xs text-red-500">{{ errors.due_date }}</p>
                <p class="mt-1 text-xs text-slate-400">
                    Se definida, o evento será sincronizado automaticamente com o Google Agenda.
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <BaseButton type="submit" variant="primary" :disabled="submitting">
                {{ submitting ? 'Salvando…' : (task ? 'Salvar alterações' : 'Criar tarefa') }}
            </BaseButton>
        </div>
    </form>
</template>
