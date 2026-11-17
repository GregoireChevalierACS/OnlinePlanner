import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { HttpClient } from './api/HttpClient'
import { authGatewayKey, HttpAuthGateway } from './api/AuthGateway'
import { HttpSchedulingGateway, schedulingGatewayKey } from './api/SchedulingGateway'
import { authGuard } from './composables/authGuard'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

// Composition root : seul endroit qui choisit les adapters concrets des ports.
const http = new HttpClient(import.meta.env.VITE_API_URL ?? '')
app.provide(schedulingGatewayKey, new HttpSchedulingGateway(http))
app.provide(authGatewayKey, new HttpAuthGateway(http))
app.use(createPinia())

router.beforeEach(authGuard(() => useAuthStore()))
app.use(router)

app.mount('#app')
