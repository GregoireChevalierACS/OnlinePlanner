<script setup lang="ts">
import type { Slot } from '@/api/types'
import { timeOf } from '@/composables/dates'

defineProps<{ slots: Slot[]; selected: Slot | null; loading: boolean }>()
const emit = defineEmits<{ select: [slot: Slot] }>()
</script>

<template>
  <p v-if="loading" class="muted">Chargement des créneaux…</p>
  <p v-else-if="slots.length === 0" class="muted">Aucun créneau disponible ce jour-là.</p>
  <ul v-else class="slots" aria-label="Créneaux disponibles">
    <li v-for="slot in slots" :key="slot.startsAt">
      <button
        type="button"
        class="slot"
        :aria-pressed="selected?.startsAt === slot.startsAt"
        @click="emit('select', slot)"
      >
        {{ timeOf(slot.startsAt) }}
      </button>
    </li>
  </ul>
</template>
