<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  title?: string;
  description?: string;
}>();
</script>

<template>
  <div>
    <Head>
      <title>{{ title ? `${title} - Jacknails Kenya` : 'Jacknails Kenya - Luxury Nail Artistry in Kitengela' }}</title>
      <meta name="description" :content="description || 'Premium nail services including acrylic nails, gel tips, stick-ons, gum gel, and artistic nail designs in Kitengela, Kenya.'" />
      <meta name="keywords" content="nail salon, nail art, acrylic nails, gel tips, Kitengela, Kenya, nail designs, manicure, luxury nails" />
      <meta property="og:title" :content="title ? `${title} - Jacknails Kenya` : 'Jacknails Kenya - Luxury Nail Artistry'" />
      <meta property="og:description" :content="description || 'Premium nail services in Kitengela, Kenya'" />
      <meta property="og:type" content="website" />
    </Head>

    <slot />
  </div>
</template>