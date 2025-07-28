<template>
  <div class="w-full bg-white rounded-2xl shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
      <div 
        v-for="(step, index) in steps" 
        :key="step.id"
        class="flex items-center"
        :class="index < steps.length - 1 ? 'flex-1' : ''"
      >
        <!-- Step Circle -->
        <div class="flex items-center">
          <div 
            :class="[
              'w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-300',
              step.id < currentStep 
                ? 'bg-green-500 text-white shadow-lg' 
                : step.id === currentStep 
                  ? 'bg-pink-500 text-white shadow-lg scale-110' 
                  : 'bg-gray-200 text-gray-500'
            ]"
          >
            <CheckCircle 
              v-if="step.id < currentStep" 
              class="w-6 h-6" 
            />
            <span v-else>{{ step.id }}</span>
          </div>
          
          <!-- Step Label -->
          <div class="ml-3 hidden sm:block">
            <div 
              :class="[
                'text-sm font-medium transition-colors duration-300',
                step.id <= currentStep ? 'text-gray-900' : 'text-gray-500'
              ]"
            >
              {{ step.title }}
            </div>
            <div 
              :class="[
                'text-xs transition-colors duration-300',
                step.id <= currentStep ? 'text-gray-600' : 'text-gray-400'
              ]"
            >
              {{ step.description }}
            </div>
          </div>
        </div>

        <!-- Progress Line -->
        <div 
          v-if="index < steps.length - 1"
          class="flex-1 mx-4 h-1 bg-gray-200 rounded-full overflow-hidden"
        >
          <div 
            :class="[
              'h-full transition-all duration-500 ease-out rounded-full',
              step.id < currentStep ? 'bg-green-500 w-full' : 'bg-gray-200 w-0'
            ]"
          />
        </div>
      </div>
    </div>

    <!-- Mobile Step Labels -->
    <div class="mt-4 sm:hidden">
      <div class="text-center">
        <div class="text-lg font-semibold text-gray-900">
          {{ currentStepData.title }}
        </div>
        <div class="text-sm text-gray-600">
          {{ currentStepData.description }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
          Step {{ currentStep }} of {{ steps.length }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckCircle } from 'lucide-vue-next'

interface Step {
  id: number
  title: string
  description: string
  icon?: string
}

interface Props {
  currentStep: number
}

const props = defineProps<Props>()

const steps: Step[] = [
  {
    id: 1,
    title: 'Select Services',
    description: 'Choose your nail services'
  },
  {
    id: 2,
    title: 'Date & Time',
    description: 'Pick your appointment slot'
  },
  {
    id: 3,
    title: 'Your Details',
    description: 'Contact information'
  },
  {
    id: 4,
    title: 'Payment',
    description: 'Choose payment method'
  },
  {
    id: 5,
    title: 'Confirm',
    description: 'Review and confirm'
  }
]

const currentStepData = computed(() => {
  return steps.find(step => step.id === props.currentStep) || steps[0]
})
</script>