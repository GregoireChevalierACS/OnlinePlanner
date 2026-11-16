<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'

const route = useRoute()
</script>

<template>
  <header class="topbar">
    <RouterLink to="/" class="brand">OnlinePlanner</RouterLink>
    <nav v-if="typeof route.params.providerId === 'string'">
      <RouterLink :to="{ name: 'agenda', params: { providerId: route.params.providerId } }"
        >Agenda</RouterLink
      >
      <RouterLink :to="{ name: 'booking', params: { providerId: route.params.providerId } }"
        >Réservation</RouterLink
      >
    </nav>
  </header>

  <main class="container">
    <RouterView />
  </main>
</template>
