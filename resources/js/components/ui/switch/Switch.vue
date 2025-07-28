<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  defaultChecked?: boolean
  checked?: boolean
  disabled?: boolean
  required?: boolean
  name?: string
  value?: string
  id?: string
  class?: string
  modelValue?: boolean
}>()

const emits = defineEmits<{
  'update:checked': [payload: boolean]
  'update:modelValue': [payload: boolean]
}>()

const isChecked = computed({
  get() {
    return props.modelValue ?? props.checked ?? props.defaultChecked ?? false
  },
  set(value) {
    emits('update:checked', value)
    emits('update:modelValue', value)
  }
})

const toggle = () => {
  if (!props.disabled) {
    isChecked.value = !isChecked.value
  }
}
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="isChecked"
    :disabled="disabled"
    :class="cn(
      'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50',
      isChecked ? 'bg-primary' : 'bg-input',
      props.class
    )"
    @click="toggle"
    v-bind="$attrs"
  >
    <span
      :class="cn(
        'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
        isChecked ? 'translate-x-5' : 'translate-x-0'
      )"
    />
  </button>
</template>