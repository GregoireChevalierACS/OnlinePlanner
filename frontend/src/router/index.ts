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
