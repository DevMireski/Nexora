<script setup>
import { ref, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { isAdmin } from '../utils/auth.js'

const route = useRoute()
const router = useRouter()

const showNav = ref(!route.meta.guest)
const isAdminUser = ref(isAdmin())

watchEffect(() => {
    showNav.value = !route.meta.guest
    isAdminUser.value = isAdmin()
})

function logout() {
    localStorage.removeItem('nexora_token')
    router.push('/login')
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <nav v-if="showNav" class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
                <span class="text-slate-800 font-semibold text-base tracking-tight">
                    Nexora
                </span>
                <div class="flex items-center gap-6">
                    <router-link
                        to="/dashboard"
                        class="text-sm text-slate-500 hover:text-slate-700 transition-colors"
                        active-class="text-slate-800 font-medium"
                    >
                        Dashboard
                    </router-link>
                    <router-link
                        to="/tasks"
                        class="text-sm text-slate-500 hover:text-slate-700 transition-colors"
                        active-class="text-slate-800 font-medium"
                    >
                        Tarefas
                    </router-link>
                    <router-link
                        v-if="isAdminUser"
                        to="/users"
                        class="text-sm text-slate-500 hover:text-slate-700 transition-colors"
                        active-class="text-slate-800 font-medium"
                    >
                        Usuários
                    </router-link>
                    <button
                        @click="logout"
                        class="text-sm text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        Sair
                    </button>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-6 py-8">
            <router-view />
        </main>
    </div>
</template>
