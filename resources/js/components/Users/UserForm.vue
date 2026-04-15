<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseInput from '../UI/BaseInput.vue'
import BaseButton from '../UI/BaseButton.vue'
import InfoTooltip from '../UI/InfoTooltip.vue'

const props = defineProps({
    user: { type: Object, default: null },
})

const emit = defineEmits(['saved'])

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
})

const errors = ref({})
const submitting = ref(false)
const isEdit = ref(false)

const calendarId = ref('')
const connectingCalendar = ref(false)
const calendarError = ref('')
const calendarSuccess = ref('')

watch(
    () => props.user,
    (user) => {
        isEdit.value = !!user
        calendarError.value = ''
        calendarSuccess.value = ''
        if (user) {
            form.value = {
                name:                  user.name,
                email:                 user.email,
                password:              '',
                password_confirmation: '',
                role:                  user.roles?.[0]?.name ?? '',
            }
            calendarId.value = user.google_calendar_id ?? ''
        } else {
            form.value = { name: '', email: '', password: '', password_confirmation: '', role: '' }
            calendarId.value = ''
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

    const payload = { ...form.value }
    if (isEdit.value && !payload.password) {
        delete payload.password
        delete payload.password_confirmation
    }
    if (!payload.role) delete payload.role

    submitting.value = true
    try {
        const url    = isEdit.value ? `/api/v1/users/${props.user.id}` : '/api/v1/users'
        const method = isEdit.value ? 'put' : 'post'
        const { data } = await axios[method](url, payload, { headers: authHeaders() })
        emit('saved', data.data)
    } catch (e) {
        if (e.response?.status === 422) {
            const bag = e.response.data?.errors ?? {}
            errors.value = Object.fromEntries(
                Object.entries(bag).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
            )
        } else {
            errors.value._general = 'Erro ao salvar usuário. Tente novamente.'
        }
    } finally {
        submitting.value = false
    }
}

async function connectCalendar() {
    calendarError.value = ''
    calendarSuccess.value = ''
    connectingCalendar.value = true
    try {
        await axios.put('/api/v1/users/calendar', {
            google_calendar_id: calendarId.value,
            user_id: props.user.id,
        }, { headers: authHeaders() })
        calendarSuccess.value = 'Google Agenda conectada com sucesso!'
    } catch (e) {
        if (e.response?.status === 422) {
            const msg = Object.values(e.response.data?.errors ?? {})[0]
            calendarError.value = Array.isArray(msg) ? msg[0] : (msg ?? 'Dados inválidos.')
        } else {
            calendarError.value = 'Erro ao conectar a agenda. Tente novamente.'
        }
    } finally {
        connectingCalendar.value = false
    }
}

const SELECT_CLASSES = [
    'w-full rounded-md border px-3 py-2 text-sm text-slate-700',
    'transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0',
    'border-slate-300 bg-slate-50 focus:ring-slate-400 focus:border-slate-400 focus:bg-white',
].join(' ')

const SELECT_ERROR_CLASSES = [
    'w-full rounded-md border px-3 py-2 text-sm text-slate-700',
    'transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0',
    'border-red-400 bg-red-50 focus:ring-red-300 focus:border-red-400',
].join(' ')
</script>

<template>
    <form @submit.prevent="submit" novalidate>
        <div class="flex flex-col gap-4">
            <p v-if="errors._general" class="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-600">
                {{ errors._general }}
            </p>

            <BaseInput
                v-model="form.name"
                label="Nome"
                placeholder="Nome completo"
                :error="errors.name"
            />

            <BaseInput
                v-model="form.email"
                label="E-mail"
                type="email"
                placeholder="email@exemplo.com"
                :error="errors.email"
            />

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                    Role
                </label>
                <select
                    v-model="form.role"
                    :class="errors.role ? SELECT_ERROR_CLASSES : SELECT_CLASSES"
                >
                    <option value="">Sem role</option>
                    <option value="admin">admin</option>
                    <option value="manager">manager</option>
                    <option value="user">user</option>
                </select>
                <p v-if="errors.role" class="mt-1.5 text-xs text-red-500">{{ errors.role }}</p>
            </div>

            <BaseInput
                v-model="form.password"
                label="Senha"
                type="password"
                :placeholder="isEdit ? 'Deixe em branco para não alterar' : 'Mínimo 8 caracteres'"
                :error="errors.password"
            />

            <BaseInput
                v-if="form.password || !isEdit"
                v-model="form.password_confirmation"
                label="Confirmar senha"
                type="password"
                placeholder="Repita a senha"
                :error="errors.password_confirmation"
            />
        </div>

        <!-- Google Calendar — only visible when editing an existing user -->
        <div v-if="isEdit" class="mt-5 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-1.5 mb-3">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                    Google Agenda
                </span>
                <InfoTooltip>
                    <p class="font-semibold mb-1.5">Como encontrar o ID da agenda:</p>
                    <ol class="list-decimal pl-3 space-y-1 text-slate-200">
                        <li>Abra o Google Calendar</li>
                        <li>Clique nos três pontos da agenda desejada</li>
                        <li>Selecione <em>"Configurações e compartilhamento"</em></li>
                        <li>Role até a seção <em>"Integrar agenda"</em></li>
                        <li>Copie o <strong>"ID da agenda"</strong></li>
                    </ol>
                    <p class="mt-2 text-slate-400">Ex: xxxxxxx@group.calendar.google.com</p>
                </InfoTooltip>
            </div>

            <div class="flex gap-2">
                <input
                    v-model="calendarId"
                    type="text"
                    placeholder="xxxxxxx@group.calendar.google.com"
                    class="flex-1 rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-slate-400 focus:bg-white"
                />
                <BaseButton
                    type="button"
                    variant="secondary"
                    :disabled="connectingCalendar || !calendarId.trim()"
                    @click="connectCalendar"
                >
                    {{ connectingCalendar ? 'Conectando…' : 'Conectar' }}
                </BaseButton>
            </div>

            <p v-if="calendarError" class="mt-1.5 text-xs text-red-500">{{ calendarError }}</p>
            <p v-if="calendarSuccess" class="mt-1.5 text-xs text-emerald-600">{{ calendarSuccess }}</p>
        </div>

        <div class="mt-6 flex justify-end">
            <BaseButton type="submit" variant="primary" :disabled="submitting">
                {{ submitting ? 'Salvando…' : (isEdit ? 'Salvar alterações' : 'Criar usuário') }}
            </BaseButton>
        </div>
    </form>
</template>
