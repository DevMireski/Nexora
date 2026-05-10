<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const metrics = ref(null)
const loading = ref(true)
const error = ref(null)

const ACTION_MAP = {
    created: 'criou',
    updated: 'atualizou',
    deleted: 'excluiu',
}

const APPOINTMENTS = [
    { id: 1, title: 'Reunião de planejamento', date: 'Hoje', time: '09:00', color: 'bg-blue-100 text-blue-700 border-blue-200' },
    { id: 2, title: 'Review de código', date: 'Hoje', time: '14:30', color: 'bg-violet-100 text-violet-700 border-violet-200' },
    { id: 3, title: 'Apresentação do sprint', date: 'Amanhã', time: '10:00', color: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
]

function authHeaders() {
    const token = localStorage.getItem('nexora_token')
    return { Authorization: `Bearer ${token}` }
}

function modelName(subjectType) {
    if (!subjectType) return '—'
    return subjectType.split('\\').at(-1)
}

function formatAction(action) {
    return ACTION_MAP[action] ?? action
}

async function fetchMetrics() {
    try {
        const { data } = await axios.get('/api/v1/dashboard', { headers: authHeaders() })
        metrics.value = data.data
    } catch (e) {
        if (e.response?.status === 401) {
            localStorage.removeItem('nexora_token')
            router.push('/login')
            return
        }
        error.value = 'Não foi possível carregar o dashboard.'
    } finally {
        loading.value = false
    }
}

onMounted(fetchMetrics)
</script>

<template>
    <div class="bg-slate-50 min-h-full -m-6 p-6">
        <div class="mb-7">
            <h1 class="text-xl font-semibold text-slate-800">Dashboard</h1>
            <p class="text-sm text-slate-400 mt-0.5">Visão geral da plataforma</p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-20 text-sm text-slate-400">
            Carregando…
        </div>

        <div
            v-else-if="error"
            class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600"
        >
            {{ error }}
        </div>

        <template v-else-if="metrics">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Usuários -->
                <div class="bg-white rounded-md border border-slate-200 shadow-sm px-5 py-5 flex items-center gap-4">
                    <div class="h-10 w-10 rounded-md bg-slate-100 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-slate-800 leading-none">{{ metrics.total_users }}</p>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mt-1">Usuários</p>
                        <p class="text-xs text-slate-400 mt-0.5">cadastrados</p>
                    </div>
                </div>

                <!-- Tarefas Ativas -->
                <div class="bg-white rounded-md border border-slate-200 shadow-sm px-5 py-5 flex items-center gap-4">
                    <div class="h-10 w-10 rounded-md bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-slate-800 leading-none">{{ metrics.active_tasks }}</p>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mt-1">Tarefas Ativas</p>
                        <p class="text-xs text-slate-400 mt-0.5">pendentes ou em andamento</p>
                    </div>
                </div>

                <!-- Atividades -->
                <div class="bg-white rounded-md border border-slate-200 shadow-sm px-5 py-5 flex items-center gap-4">
                    <div class="h-10 w-10 rounded-md bg-amber-50 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-slate-800 leading-none">{{ metrics.recent_activity.length }}</p>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mt-1">Atividades</p>
                        <p class="text-xs text-slate-400 mt-0.5">últimos eventos registrados</p>
                    </div>
                </div>
            </div>

            <!-- Lower Grid: Activity + Appointments -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Activity Feed (2/3 width) -->
                <div class="lg:col-span-2 bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25" />
                        </svg>
                        <h2 class="text-sm font-medium text-slate-700">Atividade Recente</h2>
                    </div>

                    <div
                        v-if="metrics.recent_activity.length === 0"
                        class="px-5 py-10 text-center text-sm text-slate-400"
                    >
                        Nenhuma atividade registrada.
                    </div>

                    <ul v-else class="divide-y divide-slate-50">
                        <li
                            v-for="entry in metrics.recent_activity"
                            :key="entry.id"
                            class="flex items-start gap-4 px-5 py-4"
                        >
                            <div class="mt-0.5 h-7 w-7 rounded-sm bg-slate-100 flex items-center justify-center shrink-0">
                                <span class="text-xs font-semibold text-slate-500 uppercase">
                                    {{ (entry.user?.name ?? 'S')[0] }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-slate-700">
                                    <span class="font-medium">{{ entry.user?.name ?? 'Sistema' }}</span>
                                    {{ formatAction(entry.action) }}
                                    <span class="text-slate-500">{{ modelName(entry.subject_type) }} #{{ entry.subject_id }}</span>
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ entry.created_at }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Próximos Compromissos (1/3 width) -->
                <div class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <h2 class="text-sm font-medium text-slate-700">Próximos Compromissos</h2>
                        </div>
                        <span class="text-xs text-slate-400 bg-slate-50 border border-slate-200 rounded px-1.5 py-0.5">
                            Google Agenda
                        </span>
                    </div>

                    <ul class="divide-y divide-slate-50">
                        <li
                            v-for="appt in APPOINTMENTS"
                            :key="appt.id"
                            class="flex items-center gap-3 px-5 py-3.5"
                        >
                            <div :class="appt.color" class="shrink-0 rounded border px-1.5 py-0.5 text-center min-w-[44px]">
                                <p class="text-xs font-semibold leading-none">{{ appt.time }}</p>
                                <p class="text-[10px] leading-tight mt-0.5 opacity-75">{{ appt.date }}</p>
                            </div>
                            <p class="text-sm text-slate-600 leading-tight line-clamp-2">{{ appt.title }}</p>
                        </li>
                    </ul>

                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
                        <p class="text-xs text-slate-400 text-center">
                            Conecte sua agenda em
                            <span class="font-medium text-slate-500">Usuários → Editar → Google Agenda</span>
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
