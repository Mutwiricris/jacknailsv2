<script setup lang="ts">
import { ref, provide, watch } from 'vue'

const props = defineProps<{
  defaultValue?: string
  modelValue?: string
  disabled?: boolean
}>()

const emits = defineEmits<{
  'update:modelValue': [payload: string]
}>()

const isOpen = ref(false)
const selectedValue = ref(props.modelValue || props.defaultValue || '')

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined) {
    selectedValue.value = newValue
  }
})

const selectValue = (value: string) => {
  selectedValue.value = value
  emits('update:modelValue', value)
  isOpen.value = false
}

const toggleOpen = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

provide('selectContext', {
  selectedValue,
  selectValue,
  isOpen,
  toggleOpen,
  disabled: props.disabled
})
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template>