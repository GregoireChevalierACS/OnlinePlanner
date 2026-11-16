<script setup lang="ts">
import { ref } from 'vue'
import type { Customer } from '@/stores/booking'

defineProps<{ disabled: boolean }>()
const emit = defineEmits<{ submit: [customer: Customer] }>()

const name = ref('')
const email = ref('')
</script>

<template>
  <form class="form" @submit.prevent="emit('submit', { name: name.trim(), email: email.trim() })">
    <label>
      Nom
      <input v-model="name" name="name" required autocomplete="name" />
    </label>
    <label>
      E-mail
      <input v-model="email" name="email" type="email" required autocomplete="email" />
    </label>
    <button type="submit" class="primary" :disabled="disabled">Réserver ce créneau</button>
  </form>
</template>
