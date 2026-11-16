import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
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
    },
  ],
})

export default router
