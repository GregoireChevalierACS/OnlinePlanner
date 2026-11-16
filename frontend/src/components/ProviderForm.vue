<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { NewProvider } from '@/api/types'

defineProps<{ disabled: boolean }>()
const emit = defineEmits<{ submit: [provider: NewProvider] }>()

const weekdays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']

const name = ref('')
const slotDurationMinutes = ref(30)
const bufferMinutes = ref(0)
const minimumNoticeMinutes = ref(60)
const days = reactive(
  weekdays.map((label, index) => ({
    label,
    weekday: index + 1,
    open: index < 5,
    opensAt: '09:00',
    closesAt: '17:00',
  })),
)

function submit(): void {
  emit('submit', {
    name: name.value.trim(),
    openingHours: days
      .filter((d) => d.open)
      .map(({ weekday, opensAt, closesAt }) => ({ weekday, opensAt, closesAt })),
    slotDurationMinutes: slotDurationMinutes.value,
    bufferMinutes: bufferMinutes.value,
    minimumNoticeMinutes: minimumNoticeMinutes.value,
  })
}
</script>

<template>
  <form class="form" @submit.prevent="submit">
    <label>
      Nom du prestataire
      <input v-model="name" name="name" required />
    </label>

    <fieldset>
      <legend>Horaires d'ouverture</legend>
      <div v-for="day in days" :key="day.weekday" class="day-row">
        <label class="inline">
          <input v-model="day.open" type="checkbox" :name="`open-${day.weekday}`" />
          {{ day.label }}
        </label>
        <input
          v-model="day.opensAt"
          type="time"
          :disabled="!day.open"
          :aria-label="`Ouverture ${day.label}`"
        />
        <input
          v-model="day.closesAt"
          type="time"
          :disabled="!day.open"
          :aria-label="`Fermeture ${day.label}`"
        />
      </div>
    </fieldset>

    <div class="grid">
      <label>
        Durée d'un rendez-vous (min)
        <input
          v-model.number="slotDurationMinutes"
          name="duration"
          type="number"
          min="5"
          max="480"
          required
        />
      </label>
      <label>
        Battement (min)
        <input v-model.number="bufferMinutes" name="buffer" type="number" min="0" max="240" />
      </label>
      <label>
        Délai de prévenance (min)
        <input v-model.number="minimumNoticeMinutes" name="notice" type="number" min="0" />
      </label>
    </div>

    <button type="submit" class="primary" :disabled="disabled">Créer l'agenda</button>
  </form>
</template>
