<script setup lang="ts">
import { inject } from 'vue'

const props = defineProps<{
  placeholder?: string
}>()

const selectContext = inject('selectContext') as any
</script>

<template>
  <span class="text-sm">
    {{ selectContext?.selectedValue.value || props.placeholder || 'Select...' }}
  </span>
</template>