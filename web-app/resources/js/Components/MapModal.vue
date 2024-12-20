<template>
  <div v-if="show" class="fixed inset-0 z-50">
    <!-- Backdrop with blur -->
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 h-[600px] relative">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Item Location</h3>
          <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="h-[calc(100%-4rem)]">
          <Map ref="mapRef" :disabled="false" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Map from '@/Components/map.vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  item: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);

const mapRef = ref(null);

const initializeMap = async () => {
  if (mapRef.value && !mapRef.value.map) {
    await mapRef.value.initializeMap();
  }
};

const setLocation = (location) => {
  if (mapRef.value) {
    mapRef.value.setLocation(location);
  }
};

onMounted(() => {
  initializeMap();
});

defineExpose({
  initializeMap,
  setLocation,
  map: mapRef
});
</script>
