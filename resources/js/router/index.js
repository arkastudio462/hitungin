import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/Register.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/components/Layout.vue'),
        meta: { auth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/pages/Dashboard.vue'),
            },
            {
                path: 'transactions',
                name: 'transactions',
                component: () => import('@/pages/Transactions.vue'),
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('@/pages/Categories.vue'),
            },
            {
                path: 'accounts',
                name: 'accounts',
                component: () => import('@/pages/Accounts.vue'),
            },
            {
                path: 'budgets',
                name: 'budgets',
                component: () => import('@/pages/Budgets.vue'),
            },
            {
                path: 'recurring',
                name: 'recurring',
                component: () => import('@/pages/RecurringTransactions.vue'),
            },
            {
                path: 'savings-goals',
                name: 'savings-goals',
                component: () => import('@/pages/SavingsGoals.vue'),
            },
            {
                path: 'reports',
                name: 'reports',
                component: () => import('@/pages/Reports.vue'),
            },
            {
                path: 'notification-forwards',
                name: 'notification-forwards',
                component: () => import('@/pages/NotificationForwards.vue'),
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('@/pages/Profile.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (to.meta.auth) {
        if (!auth.isAuthenticated) {
            return { name: 'login' };
        }
        if (!auth.user) {
            await auth.fetchUser();
            if (!auth.user) {
                return { name: 'login' };
            }
        }
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }
});

export default router;
