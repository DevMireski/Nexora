<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import BaseButton from '../components/UI/BaseButton.vue'
import Modal from '../components/UI/Modal.vue'
import UserForm from '../components/Users/UserForm.vue'

const router = useRouter()

const users = ref([])
const loading = ref(true)
const error = ref(null)
const deletingId = ref(null)

const isModalOpen = ref(false)
const userToEdit = ref(null)

const ROLE_CLASSES = {
    admin:   'bg-violet-50 text-violet-600 border-violet-200',
    manager: 'bg-blue-50 text-blue-600 border-blue-200',
    user:    'bg-slate-50 text-slate-500 border-slate-200',
}

function authHeaders() {
    const token = localStorage.getItem('nexora_token')
    return { Authorization: `Bearer ${token}` }
}

async function fetchUsers() {
    loading.value = true
    error.value = null
    try {
        const { data } = await axios.get('/api/v1/users', { headers: authHeaders() })
        users.value = data.data.data ?? []
    } catch (e) {
        if (e.response?.status === 401) {
            localStorage.removeItem('nexora_token')
            router.push('/login')
            return
        }
        if (e.response?.status === 403) {
            router.push('/dashboard')
            return
        }
        error.value = 'Não foi possível carregar os usuários.'
    } finally {
        loading.value = false
    }
}

async function deleteUser(id) {
    if (!confirm('Excluir este usuário? Esta ação não pode ser desfeita.')) return

    deletingId.value = id
    try {
        await axios.delete(`/api/v1/users/${id}`, { headers: authHeaders() })
        users.value = users.value.filter(u => u.id !== id)
    } catch {
        alert('Falha ao excluir. Tente novamente.')
    } finally {
        deletingId.value = null
    }
}

function openCreate() {
    userToEdit.value = null
    isModalOpen.value = true
}

function openEdit(user) {
    userToEdit.value = user
    isModalOpen.value = true
}

function closeModal() {
    isModalOpen.value = false
    userToEdit.value = null
}

function handleUserSaved(savedUser) {
    const idx = users.value.findIndex(u => u.id === savedUser.id)
    if (idx !== -1) {
        users.value[idx] = savedUser
    } else {
        users.value.unshift(savedUser)
    }
    closeModal()
}

onMounted(fetchUsers)
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Usuários</h1>
                <p class="text-sm text-slate-400 mt-0.5">Gerencie os usuários e permissões</p>
            </div>
            <BaseButton variant="primary" @click="openCreate">
                + Novo Usuário
            </BaseButton>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <div class="animate-pulse">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 flex gap-8">
                    <div class="h-3 w-4 bg-slate-200 rounded" />
                    <div class="h-3 w-32 bg-slate-200 rounded" />
                    <div class="h-3 w-40 bg-slate-200 rounded" />
                    <div class="h-3 w-20 bg-slate-200 rounded" />
                </div>
                <div
                    v-for="n in 4"
                    :key="n"
                    class="border-b border-slate-50 last:border-0 px-5 py-4 flex items-center gap-8"
                >
                    <div class="h-3 w-4 bg-slate-100 rounded" />
                    <div class="h-3 w-36 bg-slate-100 rounded" />
                    <div class="h-3 w-44 bg-slate-100 rounded" />
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
        <div v-else-if="users.length === 0" class="py-20 text-center">
            <p class="text-slate-400 text-sm">Nenhum usuário encontrado.</p>
            <BaseButton variant="secondary" class="mt-4" @click="openCreate">
                Criar primeiro usuário
            </BaseButton>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">#</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Nome</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">E-mail</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Roles</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors"
                    >
                        <td class="px-5 py-3.5 text-slate-300 font-mono text-xs">{{ user.id }}</td>

                        <td class="px-5 py-3.5 font-medium text-slate-700">{{ user.name }}</td>

                        <td class="px-5 py-3.5 text-slate-500">{{ user.email }}</td>

                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    :class="ROLE_CLASSES[role.name] ?? 'bg-slate-50 text-slate-500 border-slate-200'"
                                    class="inline-flex items-center rounded-sm border px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles?.length" class="text-slate-300 text-xs">—</span>
                            </div>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <BaseButton variant="secondary" @click="openEdit(user)">
                                    Editar
                                </BaseButton>
                                <BaseButton
                                    variant="danger"
                                    :disabled="deletingId === user.id"
                                    @click="deleteUser(user.id)"
                                >
                                    {{ deletingId === user.id ? 'Excluindo…' : 'Excluir' }}
                                </BaseButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Modal -->
    <Modal
        :is-open="isModalOpen"
        :title="userToEdit ? 'Editar usuário' : 'Novo usuário'"
        @close="closeModal"
    >
        <UserForm :user="userToEdit" @saved="handleUserSaved" />
        <template #footer>
            <BaseButton variant="secondary" @click="closeModal">Cancelar</BaseButton>
        </template>
    </Modal>
</template>
