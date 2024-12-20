<template>
  <div class="map-container">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <v-card-title class="text-h6 font-weight-bold mt-5">LostNoMore Map</v-card-title>
    <div class="map-wrapper" :class="{ 'map-disabled': disabled }" @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">
      <div id="map" ref="mapRef" class="custom-map"></div>
      <div v-if="isLoading" class="loading-overlay">
        <div class="loading-content">
          <v-progress-circular
            indeterminate
            color="primary"
            size="64"
          ></v-progress-circular>
          <span class="loading-text">Loading map...</span>
        </div>
      </div>
    </div>

    <!-- Snackbar for showing error messages -->
    <v-snackbar v-model="snackbar.visible" :color="snackbar.color" multi-line timeout="4000">
      {{ snackbar.message }}
    </v-snackbar>
  </div>
</template>

<script>
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import axios from 'axios';

export default {
  name: "Map",
  props: {
    disabled: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      map: null,
      isLoading: true,
      snackbar: {
        visible: false,
        message: "",
        color: "error",
      },
      selectedLatLng: null,
      currentMarker: null,
      userLocationMarker: null,
      watchId: null,
      startX: null,
      endX: null,
      _lastPinchDist: null,
      initialLocationSet: false,
      caragaBounds: {
        north: 9.8500, // Surigao del Norte
        south: 7.8500, // Agusan del Sur
        east: 126.5000, // Eastern coast
        west: 125.0500  // Western boundary
      },
      selectedLocation: {
        lat: null,
        lng: null
      },
      marker: null
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.initializeMap();
      this.getCurrentLocation();
    });
  },
  methods: {
    initializeMap() {
      if (this.map) {
        this.map.remove();
      }

      const mapContainer = this.$refs.mapRef;
      if (!mapContainer) {
        this.isLoading = false;
        return;
      }

      this.isLoading = true;

      try {
        // Create bounds for Caraga region
        const bounds = L.latLngBounds(
          L.latLng(this.caragaBounds.south, this.caragaBounds.west),
          L.latLng(this.caragaBounds.north, this.caragaBounds.east)
        );

        this.map = L.map(mapContainer, {
          zoomControl: true,
          zoomAnimation: true,
          dragging: true,
          touchZoom: true,
          doubleClickZoom: true,
          scrollWheelZoom: true,
          boxZoom: true,
          keyboard: true,
          tap: !L.Browser.mobile,
          bounceAtZoomLimits: true,
          maxZoom: 18,
          minZoom: 9, // Restrict minimum zoom to keep focus on Caraga
          maxBounds: bounds,
          maxBoundsViscosity: 1.0 // Make the bounds completely rigid
        });

        // Add zoom control with custom position
        L.control.zoom({
          position: 'bottomright'
        }).addTo(this.map);

        // Add scale control
        L.control.scale({
          imperial: false,
          position: 'bottomleft'
        }).addTo(this.map);

        // Add the tile layer
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "OpenStreetMap contributors",
          maxZoom: 18,
          minZoom: 9,
          bounds: bounds,
          tileSize: 512,
          zoomOffset: -1
        }).addTo(this.map);

        // Add a rectangle to highlight Caraga region
        const caragaRectangle = L.rectangle(bounds, {
          color: "#FF5722",
          weight: 2,
          fill: true,
          fillColor: '#000',
          fillOpacity: 0.1,
          dashArray: '5, 10'
        }).addTo(this.map);

        // Create overlay rectangles to dim areas outside Caraga
        const topRect = L.rectangle(
          [
            [90, -180],
            [this.caragaBounds.north, 180]
          ],
          { color: '#000', fillOpacity: 0.3, stroke: false }
        ).addTo(this.map);

        const bottomRect = L.rectangle(
          [
            [this.caragaBounds.south, -180],
            [-90, 180]
          ],
          { color: '#000', fillOpacity: 0.3, stroke: false }
        ).addTo(this.map);

        const leftRect = L.rectangle(
          [
            [this.caragaBounds.south, -180],
            [this.caragaBounds.north, this.caragaBounds.west]
          ],
          { color: '#000', fillOpacity: 0.3, stroke: false }
        ).addTo(this.map);

        const rightRect = L.rectangle(
          [
            [this.caragaBounds.south, this.caragaBounds.east],
            [this.caragaBounds.north, 180]
          ],
          { color: '#000', fillOpacity: 0.3, stroke: false }
        ).addTo(this.map);

        // Set initial view to center of Caraga
        const caragaCenter = [
          (this.caragaBounds.north + this.caragaBounds.south) / 2,
          (this.caragaBounds.east + this.caragaBounds.west) / 2
        ];
        
        this.map.setView(caragaCenter, 10);
        this.map.setMaxBounds(bounds);

        // Prevent dragging outside bounds
        this.map.on('drag', () => {
          this.map.panInsideBounds(bounds, { animate: false });
        });

        // Add click handler for marking locations
        this.map.on("click", (e) => {
          const clickedPoint = e.latlng;
          
          // Check if click is within Caraga bounds
          if (bounds.contains(clickedPoint)) {
            if (this.currentMarker) {
              this.map.removeLayer(this.currentMarker);
            }

            // Round coordinates to 6 decimal places for precision
            const lat = Number(clickedPoint.lat.toFixed(6));
            const lng = Number(clickedPoint.lng.toFixed(6));

            this.selectedLocation = { lat, lng };
            this.selectedLatLng = clickedPoint;

            // Create custom icon for the marker
            const markerIcon = L.divIcon({
              className: 'custom-marker',
              html: `<div class="marker-pin"></div>`,
              iconSize: [30, 30],
              iconAnchor: [15, 30]
            });

            // Add marker with popup showing coordinates
            this.currentMarker = L.marker(clickedPoint, {
              icon: markerIcon
            }).addTo(this.map);

            // Create popup content with coordinates
            const popupContent = `
              <div class="coordinate-popup">
                <strong>Selected Location:</strong><br>
                Latitude: ${lat}<br>
                Longitude: ${lng}
              </div>
            `;

            // Bind popup to marker and open it
            this.currentMarker.bindPopup(popupContent).openPopup();

            // Emit the selected coordinates
            this.$emit('location-selected', { lat, lng });
          }
        });

        this.map.whenReady(() => {
          this.isLoading = false;
        });
      } catch (error) {
        console.error("Error initializing map:", error);
        this.showSnackbar("Error initializing map. Please refresh the page.", "error");
        this.isLoading = false;
      }
    },
    getCurrentLocation() {
      if (!navigator.geolocation) {
        this.showSnackbar("Geolocation is not supported by your browser.", "error");
        return;
      }

      const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
      };

      // Watch position instead of one-time get
      this.watchId = navigator.geolocation.watchPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          
          // Check if location is within Caraga bounds
          if (latitude >= this.caragaBounds.south && 
              latitude <= this.caragaBounds.north && 
              longitude >= this.caragaBounds.west && 
              longitude <= this.caragaBounds.east) {
            
            // Update or create user location marker
            if (this.userLocationMarker) {
              this.userLocationMarker.setLatLng([latitude, longitude]);
            } else {
              // Create a custom icon for user location
              const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: `
                  <div class="user-dot"></div>
                  <div class="user-pulse"></div>
                  <div class="user-heading"></div>
                `,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
              });

              this.userLocationMarker = L.marker([latitude, longitude], {
                icon: userIcon,
                zIndexOffset: 1000, // Keep marker on top
                interactive: true
              }).addTo(this.map);

              // Add popup with coordinates
              const popupContent = `
                <div class="user-location-popup">
                  <strong>Your Location</strong><br>
                  Latitude: ${latitude.toFixed(6)}<br>
                  Longitude: ${longitude.toFixed(6)}
                </div>
              `;
              this.userLocationMarker.bindPopup(popupContent);
            }

            // Only set view on initial load
            if (!this.initialLocationSet) {
              this.map.setView([latitude, longitude], 15);
              this.initialLocationSet = true;
              this.showSnackbar("Location found!", "success");
            }
          } else {
            this.showSnackbar("You are outside the Caraga region.", "warning");
          }
        },
        (error) => {
          let message = "Unable to retrieve your location.";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              message = "Location access was denied. Please enable location services.";
              break;
            case error.POSITION_UNAVAILABLE:
              message = "Location information is unavailable.";
              break;
            case error.TIMEOUT:
              message = "Location request timed out.";
              break;
          }
          this.showSnackbar(message, "error");
        },
        options
      );
    },
    showSnackbar(message, color) {
      this.snackbar.message = message;
      this.snackbar.color = color;
      this.snackbar.visible = true;
    },
    handleTouchStart(event) {
      this.startX = event.touches[0].clientX;
    },
    handleTouchMove(event) {
      this.endX = event.touches[0].clientX;
    },
    handleTouchEnd() {
      const deltaX = this.endX - this.startX;
      if (deltaX > 50) {
        // Swipe right
        this.swipeRight();
      } else if (deltaX < -50) {
        // Swipe left
        this.swipeLeft();
      }
    },
    swipeRight() {
      // Logic to shift map view right
      console.log('Swiped right');
      // Add your logic here to change the map view
    },
    swipeLeft() {
      // Logic to shift map view left
      console.log('Swiped left');
      // Add your logic here to change the map view
    },
    setLocation(location) {
      if (this.map && location.lat && location.lng) {
        // Remove existing marker if it exists
        if (this.currentMarker) {
          this.map.removeLayer(this.currentMarker);
        }
        
        // Create a new marker at the location
        this.currentMarker = L.marker([location.lat, location.lng], {
          draggable: false
        }).addTo(this.map);
        
        // Add a popup with the coordinates
        this.currentMarker.bindPopup(`
          <div class="text-center">
            <strong>Item Location</strong><br>
            Latitude: ${location.lat.toFixed(6)}<br>
            Longitude: ${location.lng.toFixed(6)}
          </div>
        `).openPopup();
        
        // Center the map on the location
        this.map.setView([location.lat, location.lng], 15);
      }
    },
  },
  beforeDestroy() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
    }
    if (this.map) {
      if (this.currentMarker) {
        this.map.removeLayer(this.currentMarker);
      }
      if (this.userLocationMarker) {
        this.map.removeLayer(this.userLocationMarker);
      }
      if (this.marker) {
        this.map.removeLayer(this.marker);
      }
      this.map.remove();
    }
  },
};
</script>

<style>
.map-container {
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.map-wrapper {
  position: relative;
  flex-grow: 1;
  width: 100%;
  height: 100%;
  min-height: 400px;
}

.custom-map {
  width: 100%;
  height: 100%;
  z-index: 1;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.loading-text {
  color: #1976d2;
  font-weight: 500;
}

.user-location-dot {
  width: 12px;
  height: 12px;
  background: #1976d2;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 2px #1976d2;
  animation: pulse 2s infinite;
}

.custom-pin-marker {
  display: flex;
  justify-content: center;
  align-items: center;
}

.custom-pin-marker i {
  color: #f44336;
  font-size: 30px;
  filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(25, 118, 210, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0);
  }
}

.input-field {
  margin-bottom: 16px;
}

/* Add smooth zoom animation */
.leaflet-zoom-animated {
  transition: transform 0.25s cubic-bezier(0,0,0.25,1);
}

/* Improve touch feedback */
.leaflet-marker-icon {
  transition: transform 0.2s ease-out;
}

.leaflet-marker-icon:active {
  transform: scale(1.1);
}

/* Add touch ripple effect */
.leaflet-control-zoom a {
  position: relative;
  overflow: hidden;
}

.leaflet-control-zoom a::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: scale(0);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s, transform 0.3s;
}

.leaflet-control-zoom a:active::after {
  transform: scale(2);
  opacity: 1;
  transition: 0s;
}

/* Custom user location marker styles */
.user-location-marker {
  position: relative;
}

.user-dot {
  width: 12px;
  height: 12px;
  background-color: #2196F3;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 0 4px rgba(0,0,0,0.3);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.user-pulse {
  width: 24px;
  height: 24px;
  background-color: rgba(33, 150, 243, 0.3);
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: user-pulse 2s infinite;
}

.user-heading {
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-bottom: 10px solid #2196F3;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg);
  display: none; /* Only show when device orientation is available */
}

@keyframes user-pulse {
  0% {
    transform: translate(-50%, -50%) scale(0.5);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(2);
    opacity: 0;
  }
}

.user-location-popup {
  padding: 8px;
  font-size: 12px;
  line-height: 1.4;
}

.user-location-popup strong {
  color: #2196F3;
  display: block;
  margin-bottom: 4px;
}

/* Search marker styles */
.search-location-marker {
  position: relative;
}

.search-marker-pin {
  width: 20px;
  height: 20px;
  background-color: #FF5722;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 0 4px rgba(0,0,0,0.3);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.search-marker-pulse {
  width: 40px;
  height: 40px;
  background-color: rgba(255, 87, 34, 0.3);
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: search-pulse 2s infinite;
}

@keyframes search-pulse {
  0% {
    transform: translate(-50%, -50%) scale(0.5);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(2);
    opacity: 0;
  }
}

/* Search popup styles */
.search-popup {
  padding: 8px;
  max-width: 250px;
}

.search-popup h3 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: bold;
  color: #333;
}

.search-popup p {
  margin: 0;
  font-size: 12px;
  color: #666;
  line-height: 1.4;
}

/* Loading indicator for search */
.v-text-field.input-field.loading .v-input__slot::after {
  content: '';
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  border: 2px solid #ccc;
  border-top-color: #1976D2;
  border-radius: 50%;
  animation: search-spin 0.8s linear infinite;
}

@keyframes search-spin {
  to {
    transform: translateY(-50%) rotate(360deg);
  }
}

/* Custom marker styles */
.custom-marker {
  position: relative;
}

.marker-pin {
  width: 30px;
  height: 30px;
  background-color: #4CAF50;
  border: 3px solid white;
  border-radius: 50% 50% 50% 0;
  transform: rotate(-45deg);
  box-shadow: 0 0 4px rgba(0,0,0,0.3);
  cursor: pointer;
  transition: transform 0.2s;
}

.marker-pin:hover {
  transform: rotate(-45deg) scale(1.1);
}

.marker-pin::after {
  content: '';
  width: 14px;
  height: 14px;
  background: white;
  position: absolute;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

/* Coordinate popup styles */
.coordinate-popup {
  padding: 8px;
  font-size: 12px;
  line-height: 1.4;
}

.coordinate-popup strong {
  color: #4CAF50;
  display: block;
  margin-bottom: 4px;
}
</style>
