<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import BaseButton from '../components/UI/BaseButton.vue'
import Modal from '../components/UI/Modal.vue'
import TaskForm from '../components/Tasks/TaskForm.vue'

const router = useRouter()

const tasks = ref([])
const loading = ref(true)
const error = ref(null)
const deletingId = ref(null)
const togglingId = ref(null)

const isModalOpen = ref(false)
const taskToEdit = ref(null)

const STATUS_LABELS = {
    pending:  { label: 'Pendente',      classes: 'bg-amber-50 text-amber-600 border-amber-200' },
    progress: { label: 'Em andamento',  classes: 'bg-blue-50 text-blue-600 border-blue-200' },
    done:     { label: 'Concluída',     classes: 'bg-emerald-50 text-emerald-600 border-emerald-200' },
}

function formatDueDate(dateStr) {
    if (!dateStr) return null
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(dateStr))
}

function dueDateClass(dateStr) {
    if (!dateStr) return ''
    const due = new Date(dateStr)
    const now = new Date()
    if (due < now) return 'text-red-500'
    if (due - now < 86_400_000) return 'text-amber-500'
    return 'text-slate-400'
}

function authHeaders() {
    const token = localStorage.getItem('nexora_token')
    return { Authorization: `Bearer ${token}` }
}

async function fetchTasks() {
    loading.value = true
    error.value = null
    try {
        const { data } = await axios.get('/api/v1/tasks', { headers: authHeaders() })
        tasks.value = data.data.data ?? []
    } catch (e) {
        if (e.response?.status === 401) {
            localStorage.removeItem('nexora_token')
            router.push('/login')
            return
        }
        error.value = 'Não foi possível carregar as tarefas.'
    } finally {
        loading.value = false
    }
}

async function toggleDone(task) {
    const newStatus = task.status === 'done' ? 'pending' : 'done'
    togglingId.value = task.id
    try {
        const { data } = await axios.patch(
            `/api/v1/tasks/${task.id}/status`,
            { status: newStatus },
            { headers: authHeaders() }
        )
        const idx = tasks.value.findIndex(t => t.id === task.id)
        if (idx !== -1) tasks.value[idx] = data.data
    } catch {
        alert('Falha ao atualizar status. Tente novamente.')
    } finally {
        togglingId.value = null
    }
}

async function deleteTask(id) {
    if (!confirm('Excluir esta tarefa?')) return

    deletingId.value = id
    try {
        await axios.delete(`/api/v1/tasks/${id}`, { headers: authHeaders() })
        tasks.value = tasks.value.filter(t => t.id !== id)
    } catch {
        alert('Falha ao excluir. Tente novamente.')
    } finally {
        deletingId.value = null
    }
}

function openCreate() {
    taskToEdit.value = null
    isModalOpen.value = true
}

function openEdit(task) {
    taskToEdit.value = task
    isModalOpen.value = true
}

function closeModal() {
    isModalOpen.value = false
    taskToEdit.value = null
}

function handleTaskSaved(savedTask) {
    const idx = tasks.value.findIndex(t => t.id === savedTask.id)
    if (idx !== -1) {
        tasks.value[idx] = savedTask
    } else {
        tasks.value.unshift(savedTask)
    }
    closeModal()
}

onMounted(fetchTasks)
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Tarefas</h1>
                <p class="text-sm text-slate-400 mt-0.5">Gerencie as tarefas do projeto</p>
            </div>
            <BaseButton variant="primary" @click="openCreate">
                + Nova Tarefa
            </BaseButton>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <div class="animate-pulse">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 flex gap-8">
                    <div class="h-3 w-4 bg-slate-200 rounded" />
                    <div class="h-3 w-32 bg-slate-200 rounded" />
                    <div class="h-3 w-24 bg-slate-200 rounded" />
                    <div class="h-3 w-16 bg-slate-200 rounded" />
                </div>
                <div
                    v-for="n in 4"
                    :key="n"
                    class="border-b border-slate-50 last:border-0 px-5 py-4 flex items-center gap-8"
                >
                    <div class="h-3 w-4 bg-slate-100 rounded" />
                    <div class="h-3 w-40 bg-slate-100 rounded" />
                    <div class="h-3 w-28 bg-slate-100 rounded" />
                    <div class="h-3 w-16 bg-slate-100 rounded" />
                </div>
            </div>
        </div>

        <!-- Error -->
        <div
            v-else-if="error"
            class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600"
        >
            {{ error }}
        </div>

        <!-- Empty state -->
        <div v-else-if="tasks.length === 0" class="py-20 text-center">
            <p class="text-slate-400 text-sm">Nenhuma tarefa encontrada.</p>
            <BaseButton variant="secondary" class="mt-4" @click="openCreate">
                Criar primeira tarefa
            </BaseButton>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="w-8 px-3 py-3"></th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">#</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Título</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Responsável</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="task in tasks"
                        :key="task.id"
                        :class="task.status === 'done' ? 'opacity-60' : ''"
                        class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors"
                    >
                        <!-- Quick complete toggle -->
                        <td class="px-3 py-3.5">
                            <button
                                type="button"
                                :disabled="togglingId === task.id"
                                :title="task.status === 'done' ? 'Reabrir tarefa' : 'Marcar como concluída'"
                                class="h-6 w-6 flex items-center justify-center rounded-full border-2 transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-400"
                                :class="task.status === 'done'
                                    ? 'border-emerald-400 bg-emerald-400 text-white hover:bg-emerald-300 hover:border-emerald-300'
                                    : 'border-slate-300 text-transparent hover:border-slate-400 hover:text-slate-400'"
                                @click="toggleDone(task)"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                        </td>

                        <td class="px-5 py-3.5 text-slate-300 font-mono text-xs">{{ task.id }}</td>

                        <td class="px-5 py-3.5">
                            <span
                                class="font-medium text-slate-700"
                                :class="task.status === 'done' ? 'line-through text-slate-400' : ''"
                            >{{ task.title }}</span>
                            <p v-if="task.description" class="text-slate-400 text-xs mt-0.5 line-clamp-1">
                                {{ task.description }}
                            </p>
                            <div v-if="task.due_date" class="flex items-center gap-1 mt-1">
                                <svg class="h-3 w-3 shrink-0" :class="dueDateClass(task.due_date)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span :class="dueDateClass(task.due_date)" class="text-xs font-medium">
                                    {{ formatDueDate(task.due_date) }}
                                </span>
                                <span v-if="new Date(task.due_date) < new Date() && task.status !== 'done'" class="text-xs text-red-400 font-medium">· Vencida</span>
                                <span v-else-if="new Date(task.due_date) - new Date() < 86_400_000 && task.status !== 'done'" class="text-xs text-amber-400">· Hoje</span>
                            </div>
                        </td>

                        <td class="px-5 py-3.5 text-slate-500">
                            {{ task.user?.name ?? '—' }}
                        </td>

                        <td class="px-5 py-3.5">
                            <span
                                :class="STATUS_LABELS[task.status]?.classes ?? 'bg-slate-50 text-slate-500 border-slate-200'"
                                class="inline-flex items-center rounded-sm border px-2 py-0.5 text-xs font-medium"
                            >
                                {{ STATUS_LABELS[task.status]?.label ?? task.status }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <BaseButton variant="secondary" @click="openEdit(task)">
                                    Editar
                                </BaseButton>
                                <BaseButton
                                    variant="danger"
                                    :disabled="deletingId === task.id"
                                    @click="deleteTask(task.id)"
                                >
                                    {{ deletingId === task.id ? 'Excluindo…' : 'Excluir' }}
                                </BaseButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Task Modal -->
    <Modal
        :is-open="isModalOpen"
        :title="taskToEdit ? 'Editar tarefa' : 'Nova tarefa'"
        @close="closeModal"
    >
        <TaskForm :task="taskToEdit" @saved="handleTaskSaved" />
        <template #footer>
            <BaseButton variant="secondary" @click="closeModal">Cancelar</BaseButton>
        </template>
    </Modal>
</template>
