import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

declare module 'vue-router' {
  interface RouteMeta {
    /** Page réservée aux prestataires connectés. */
    requiresAuth?: boolean
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView, meta: { requiresAuth: true } },
    { path: '/connexion', name: 'login', component: () => import('../views/LoginView.vue') },
    {
      path: '/mot-de-passe-oublie',
      name: 'forgot-password',
      component: () => import('../views/ForgotPasswordView.vue'),
    },
    {
      // Lien reçu par e-mail : /mot-de-passe/nouveau?token=…
      path: '/mot-de-passe/nouveau',
      name: 'reset-password',
      component: () => import('../views/ResetPasswordView.vue'),
    },
    {
      path: '/p/:providerId/reserver',
      name: 'booking',
      component: () => import('../views/BookingView.vue'),
      props: true,
    },
    {
      path: '/p/:providerId/agenda',
      name: 'agenda',
      component: () => import('../views/AgendaView.vue'),
      props: true,
      meta: { requiresAuth: true },
    },
  ],
})

export default router
