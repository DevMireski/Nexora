import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import TaskList from '../views/TaskList.vue'
import UserList from '../views/UserList.vue'
import { isAdmin } from '../utils/auth.js'

const routes = [
    {
        path: '/login',
        component: Login,
        meta: { guest: true },
    },
    {
        path: '/dashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
    },
    {
        path: '/tasks',
        component: TaskList,
        meta: { requiresAuth: true },
    },
    {
        path: '/users',
        component: UserList,
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/',
        redirect: '/dashboard',
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('nexora_token')

    if (to.meta.requiresAuth && !token) {
        return next('/login')
    }

    if (to.meta.guest && token) {
        return next('/dashboard')
    }

    if (to.meta.requiresAdmin && !isAdmin()) {
        return next('/dashboard')
    }

    next()
})

export default router
