<script setup lang="ts">
import type { Problem } from '@/api/types'

defineProps<{ problem: Problem }>()
</script>

<template>
  <div class="alert" role="alert">
    <p class="alert-title">{{ problem.detail }}</p>
    <ul v-if="problem.violations?.length">
      <li v-for="violation in problem.violations" :key="violation.field + violation.message">
        <strong>{{ violation.field }}</strong> : {{ violation.message }}
      </li>
    </ul>
  </div>
</template>
