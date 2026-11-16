<script setup lang="ts">
import { computed, ref } from 'vue'

/**
 * Demande de confirmation avant une action irréversible (guide : « l'outil propose, l'utilisateur décide »).
 */
const props = defineProps<{
  title: string
  message: string
  confirmLabel: string
  askReason?: boolean
}>()
const emit = defineEmits<{ confirm: [reason: string]; close: [] }>()

const reason = ref('')
const canConfirm = computed(() => !props.askReason || reason.value.trim() !== '')
</script>

<template>
  <div class="backdrop" @click.self="emit('close')">
    <div class="dialog" role="dialog" aria-modal="true" :aria-label="title">
      <h2>{{ title }}</h2>
      <p>{{ message }}</p>
      <label v-if="askReason">
        Motif
        <input v-model="reason" name="reason" required />
      </label>
      <div class="actions">
        <button type="button" @click="emit('close')">Retour</button>
        <button
          type="button"
          class="danger"
          :disabled="!canConfirm"
          @click="emit('confirm', reason.trim())"
        >
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
