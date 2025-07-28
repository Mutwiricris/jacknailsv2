<script setup lang="ts">
import { inject } from 'vue'
import { Check } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps<{
  value: string
  disabled?: boolean
  class?: string
}>()

const selectContext = inject('selectContext') as any

const handleSelect = () => {
  if (!props.disabled) {
    selectContext?.selectValue(props.value)
  }
}

const isSelected = () => {
  return selectContext?.selectedValue.value === props.value
}
</script>

<template>
  <div
    @click="handleSelect"
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
      props.disabled && 'pointer-events-none opacity-50',
      props.class
    )"
  >
    <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check v-if="isSelected()" class="h-4 w-4" />
    </span>

    <span>
      <slot />
    </span>
  </div>
</template>