<script setup lang="ts">
// This component provides animated floating background elements
</script>

<template>
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <!-- Animated floating shapes -->
    <div class="floating-shape" />
    <div class="floating-shape" />
    <div class="floating-shape" />
    <div class="floating-shape" />

    <!-- Additional decorative elements -->
    <div class="absolute top-1/4 left-1/3 w-2 h-2 bg-rose-400/30 rounded-full animate-pulse" />
    <div
      class="absolute top-3/4 right-1/4 w-3 h-3 bg-pink-400/30 rounded-full animate-pulse"
      style="animation-delay: 1s"
    />
    <div
      class="absolute top-1/2 left-1/4 w-1 h-1 bg-purple-400/30 rounded-full animate-pulse"
      style="animation-delay: 2s"
    />

    <!-- Geometric shapes -->
    <div class="absolute top-20 right-20 w-8 h-8 border border-rose-200/30 rotate-45 rotate-slow" />
    <div
      class="absolute bottom-40 left-20 w-6 h-6 border border-pink-200/30 rounded-full rotate-slow"
      style="animation-delay: 10s"
    />
  </div>
</template>

<style scoped>
.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(244, 63, 94, 0.1), rgba(236, 72, 153, 0.1));
  filter: blur(1px);
  animation: float 8s ease-in-out infinite;
}

.floating-shape:nth-child(1) {
  width: 80px;
  height: 80px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.floating-shape:nth-child(2) {
  width: 60px;
  height: 60px;
  top: 20%;
  right: 15%;
  animation-delay: 2s;
}

.floating-shape:nth-child(3) {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 15%;
  animation-delay: 4s;
}

.floating-shape:nth-child(4) {
  width: 70px;
  height: 70px;
  bottom: 30%;
  right: 10%;
  animation-delay: 6s;
}

.rotate-slow {
  animation: rotate-slow 20s linear infinite;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0px) rotate(0deg);
  }
  33% {
    transform: translateY(-15px) rotate(1deg);
  }
  66% {
    transform: translateY(-8px) rotate(-1deg);
  }
}

@keyframes rotate-slow {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>