<script setup lang="ts">
import { ref, watch } from 'vue'
import BookingForm from '@/components/BookingForm.vue'
import ProblemAlert from '@/components/ProblemAlert.vue'
import SlotPicker from '@/components/SlotPicker.vue'
import { isoDate, timeOf } from '@/composables/dates'
import { useBookingStore } from '@/stores/booking'

const props = defineProps<{ providerId: string }>()
const booking = useBookingStore()
const day = ref(isoDate(new Date()))

watch(
  [() => props.providerId, day],
  ([providerId, selectedDay]) => booking.loadSlots(providerId, selectedDay),
  {
    immediate: true,
  },
)
</script>

<template>
  <section class="card">
    <h1>Prendre rendez-vous</h1>

    <label>
      Jour
      <input v-model="day" type="date" name="day" required />
    </label>

    <ProblemAlert v-if="booking.problem" :problem="booking.problem" />
    <p v-if="booking.bookedSlot" class="success" role="status">
      Rendez-vous demandé le {{ booking.day }} à {{ timeOf(booking.bookedSlot.startsAt) }}. Il sera
      confirmé par le prestataire.
    </p>

    <SlotPicker
      :slots="booking.slots"
      :selected="booking.selected"
      :loading="booking.loading"
      @select="booking.select"
    />

    <BookingForm v-if="booking.selected" :disabled="!booking.canBook" @submit="booking.book" />
  </section>
</template>
