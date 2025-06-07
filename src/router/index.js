import { createRouter, createWebHistory } from 'vue-router'
import InscriptionView from '@/views/InscriptionView.vue'
import GestionView from '@/views/GestionView.vue'
import RechercheView from '@/views/RechercheView.vue'
import LoginView from '@/views/LoginView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/inscription'
    },
    {
      path: '/inscription',
      name: 'inscription',
      component: InscriptionView
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/gestion',
      name: 'gestion',
      component: GestionView,
      meta: { requiresAuth: true }
    },
    {
      path: '/recherche',
      name: 'recherche',
      component: RechercheView,
      meta: { requiresAuth: true }
    }
  ]
})

// Navigation guard to check authentication
router.beforeEach(async (to, from, next) => {
  // Check if the route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuthenticated

    // If not authenticated, redirect to login page with redirect parameter
    if (!isAuthenticated) {
      next({
        name: 'login',
        query: { redirect: to.fullPath }
      })
    } else {
      // User is authenticated, proceed
      next()
    }
  } else {
    // Route doesn't require authentication
    next()
  }
})

export default router
