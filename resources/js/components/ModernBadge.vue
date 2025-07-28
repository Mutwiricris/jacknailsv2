<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

interface Props {
  className?: string;
  variant?: 'default' | 'premium' | 'glow' | 'soft';
}

const props = withDefaults(defineProps<Props>(), {
  className: '',
  variant: 'default',
});

const variants = {
  default: 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-700 border border-rose-200/50 backdrop-blur-sm',
  premium: 'bg-gradient-to-r from-rose-100 to-pink-100 text-rose-800 border border-rose-300/50 shadow-lg backdrop-blur-sm',
  glow: 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-lg pulse-glow border-0',
  soft: 'neumorphism text-rose-700 border-0 shadow-none',
};
</script>

<template>
  <Badge
    :class="`${variants[variant]} px-6 py-3 rounded-full font-medium transition-all duration-300 hover:scale-105 ${className}`"
  >
    <slot />
  </Badge>
</template>

<style scoped>
.pulse-glow {
  animation: pulse-glow 3s ease-in-out infinite;
}

.neumorphism {
  background: linear-gradient(145deg, #ffffff, #f0f0f0);
  box-shadow: 20px 20px 40px rgba(244, 63, 94, 0.1), -20px -20px 40px rgba(255, 255, 255, 0.9);
}

@keyframes pulse-glow {
  0%,
  100% {
    box-shadow: 0 0 30px rgba(244, 63, 94, 0.4);
  }
  50% {
    box-shadow: 0 0 50px rgba(244, 63, 94, 0.8);
  }
}
</style>