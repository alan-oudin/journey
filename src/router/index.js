import { createRouter, createWebHistory } from 'vue-router'
import InscriptionView from '@/views/InscriptionView.vue'
import GestionView from '@/views/GestionView.vue'
import RechercheView from '@/views/RechercheView.vue'

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
      path: '/gestion',
      name: 'gestion',
      component: GestionView
    },
    {
      path: '/recherche',
      name: 'recherche',
      component: RechercheView
    }
  ]
})

export default router
