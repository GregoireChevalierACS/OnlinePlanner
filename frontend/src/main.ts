import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { HttpClient } from './api/HttpClient'
import { HttpSchedulingGateway, schedulingGatewayKey } from './api/SchedulingGateway'

const app = createApp(App)

// Composition root : seul endroit qui choisit l'adapter concret du port SchedulingGateway.
app.provide(
  schedulingGatewayKey,
  new HttpSchedulingGateway(new HttpClient(import.meta.env.VITE_API_URL ?? '')),
)
app.use(createPinia())
app.use(router)

app.mount('#app')
