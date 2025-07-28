<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';

interface Props {
  end: number;
  duration?: number;
  suffix?: string;
  className?: string;
}

const props = withDefaults(defineProps<Props>(), {
  duration: 2000,
  suffix: '',
  className: '',
});

const count = ref(0);
const isVisible = ref(false);
const counterRef = ref<HTMLDivElement>();
let observer: IntersectionObserver | null = null;
let animationFrame: number | null = null;

const displayValue = computed(() => {
  return `${count.value}${props.suffix}`;
});

const startAnimation = () => {
  if (!isVisible.value) return;

  let startTime: number;

  const animate = (timestamp: number) => {
    if (!startTime) startTime = timestamp;
    const progress = Math.min((timestamp - startTime) / props.duration, 1);

    // Ease out quart function
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    count.value = Math.floor(easeOutQuart * props.end);

    if (progress < 1) {
      animationFrame = requestAnimationFrame(animate);
    }
  };

  animationFrame = requestAnimationFrame(animate);
};

const setupIntersectionObserver = () => {
  if (!counterRef.value) return;

  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting && !isVisible.value) {
        isVisible.value = true;
        startAnimation();
      }
    },
    { threshold: 0.1 }
  );

  observer.observe(counterRef.value);
};

onMounted(() => {
  setupIntersectionObserver();
});

onUnmounted(() => {
  if (observer) {
    observer.disconnect();
  }
  if (animationFrame) {
    cancelAnimationFrame(animationFrame);
  }
});
</script>

<template>
  <div ref="counterRef" :class="className">
    {{ displayValue }}
  </div>
</template>