<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref(null)

async function submit() {
    error.value = null
    loading.value = true

    try {
        const { data } = await axios.post('/api/v1/auth/login', form.value)
        localStorage.setItem('nexora_token', data.data.token)
        router.push('/dashboard')
    } catch (e) {
        const msg = e.response?.data?.message ?? 'Falha ao conectar. Tente novamente.'
        error.value = msg
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
        <div class="w-full max-w-sm">

            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold text-slate-800 tracking-tight">Nexora</h1>
                <p class="mt-1 text-sm text-slate-400">Plataforma de Gestão Inteligente</p>
            </div>

            <div class="bg-white rounded-md shadow-md border border-slate-100 p-8">
                <h2 class="text-base font-medium text-slate-700 mb-6">Entrar na sua conta</h2>

                <div
                    v-if="error"
                    class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600"
                >
                    {{ error }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                            E-mail
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="voce@empresa.com"
                            class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-slate-400 focus:bg-white transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide">
                            Senha
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-slate-400 focus:bg-white transition-colors"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="mt-2 w-full rounded-md bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="loading">Entrando…</span>
                        <span v-else>Entrar</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</template>
