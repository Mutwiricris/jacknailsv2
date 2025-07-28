<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 flex flex-col space-y-2 max-w-sm">
      <Transition
        v-for="toast in toasts"
        :key="toast.id"
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 transform translate-x-full scale-95"
        enter-to-class="opacity-100 transform translate-x-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 transform translate-x-0 scale-100"
        leave-to-class="opacity-0 transform translate-x-full scale-95"
      >
        <div
          :class="[
            'flex items-center p-4 rounded-lg shadow-lg border backdrop-blur-sm',
            toast.type === 'success' && 'bg-green-50/95 border-green-200 text-green-800',
            toast.type === 'error' && 'bg-red-50/95 border-red-200 text-red-800',
            toast.type === 'warning' && 'bg-yellow-50/95 border-yellow-200 text-yellow-800',
            toast.type === 'info' && 'bg-blue-50/95 border-blue-200 text-blue-800'
          ]"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mr-3">
            <CheckCircle 
              v-if="toast.type === 'success'" 
              class="w-5 h-5 text-green-500" 
            />
            <AlertCircle 
              v-else-if="toast.type === 'error'" 
              class="w-5 h-5 text-red-500" 
            />
            <AlertTriangle 
              v-else-if="toast.type === 'warning'" 
              class="w-5 h-5 text-yellow-500" 
            />
            <Info 
              v-else 
              class="w-5 h-5 text-blue-500" 
            />
          </div>
          
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div v-if="toast.title" class="font-semibold text-sm">
              {{ toast.title }}
            </div>
            <div class="text-sm">
              {{ toast.message }}
            </div>
          </div>
          
          <!-- Close Button -->
          <button
            @click="removeToast(toast.id)"
            class="flex-shrink-0 ml-3 p-1 rounded-full hover:bg-black/10 transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckCircle, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next'
import { useToast } from './useToast'

const { toasts, removeToast } = useToast()
</script>