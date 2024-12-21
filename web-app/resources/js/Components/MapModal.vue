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
          <Map ref="mapRef" :disabled="true" :skip-location="true" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
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

const getItemLocation = () => {
  if (props.item && props.item.location) {
    try {
      // Parse the coordinates from the location string
      const [lat, lng] = props.item.location.split(',').map(coord => parseFloat(coord.trim()));
      if (!isNaN(lat) && !isNaN(lng)) {
        console.log('Found valid coordinates:', { lat, lng });
        return { lat, lng };
      }
    } catch (error) {
      console.error('Error parsing location:', error);
    }
  }
  console.log('No valid location found in item:', props.item);
  // Return default location if no valid coordinates found
  return { lat: 8.9467, lng: 125.5449 }; // Default to Butuan City coordinates
};

const initializeMap = async () => {
  try {
    if (mapRef.value && !mapRef.value.map) {
      console.log('Initializing map...');
      await mapRef.value.initializeMap();
    }
    
    // Get location after small delay to ensure map is ready
    setTimeout(() => {
      const location = getItemLocation();
      console.log('Setting location:', location);
      if (mapRef.value && mapRef.value.setLocation) {
        mapRef.value.setLocation(location);
      } else {
        console.error('Map reference or setLocation method not found');
      }
    }, 500);
  } catch (error) {
    console.error('Error in initializeMap:', error);
  }
};

// Watch for changes in the show prop
watch(() => props.show, async (newVal) => {
  if (newVal) {
    await nextTick();
    initializeMap();
  }
});

onMounted(() => {
  if (props.show) {
    initializeMap();
  }
});

defineExpose({
  initializeMap,
  map: mapRef
});
</script>
