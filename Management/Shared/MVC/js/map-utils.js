// map-utils.js
// Utility script for initializing and managing Leaflet maps across ShobKaaj

/**
 * Injects Leaflet CSS and JS into the page dynamically if not already present.
 */
let isLeafletLoading = false;
let leafletLoadCallbacks = [];

function loadLeafletResources(callback) {
    if (window.L) {
        if (callback) callback();
        return;
    }
    
    if (callback) leafletLoadCallbacks.push(callback);
    
    if (isLeafletLoading) return;
    isLeafletLoading = true;
    
    // Load CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
    
    // Load JS
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
        isLeafletLoading = false;
        leafletLoadCallbacks.forEach(cb => cb());
        leafletLoadCallbacks = [];
    };
    document.head.appendChild(script);
}

/**
 * Initializes a Leaflet map inside the specified container ID.
 * @param {string} containerId - The ID of the DOM element for the map.
 * @param {number} lat - Initial latitude.
 * @param {number} lng - Initial longitude.
 * @param {number} zoom - Initial zoom level.
 * @returns {Object} The Leaflet map instance.
 */
function initMap(containerId, lat = 23.8103, lng = 90.4125, zoom = 13) {
    // Default coordinates (Dhaka, Bangladesh)
    const map = L.map(containerId).setView([lat, lng], zoom);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    return map;
}

/**
 * Adds a draggable marker for the user to pick a location.
 * @param {Object} map - The Leaflet map instance.
 * @param {Function} onLocationChange - Callback function passing (lat, lng).
 */
function addLocationPickerMarker(map, onLocationChange, initialLat = null, initialLng = null) {
    let marker;
    
    // Use initial coords or map center
    const center = initialLat && initialLng ? [initialLat, initialLng] : map.getCenter();
    
    // Use custom icon for the picker
    const pickerIcon = L.divIcon({
        html: '<i class="fas fa-map-marker-alt" style="color: #ef4444; font-size: 32px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.4));"></i>',
        className: 'custom-div-icon',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
    
    marker = L.marker(center, { draggable: true, icon: pickerIcon }).addTo(map);
    
    if (initialLat && initialLng) {
         map.setView([initialLat, initialLng], 15);
    }
    
    marker.on('dragend', function (event) {
        const position = marker.getLatLng();
        onLocationChange(position.lat, position.lng);
    });
    
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        onLocationChange(e.latlng.lat, e.latlng.lng);
    });
    
    // Initial callback
    onLocationChange(marker.getLatLng().lat, marker.getLatLng().lng);
    
    return marker;
}

/**
 * Gets the user's current location using the HTML5 Geolocation API.
 * @param {Function} onSuccess - Callback passing (lat, lng).
 * @param {Function} onError - Callback for errors.
 */
function getUserCurrentLocation(onSuccess, onError) {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                onSuccess(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.error("Error getting location", error);
                if (onError) onError(error);
            }
        );
    } else {
        if (onError) onError(new Error("Geolocation not supported by this browser."));
    }
}
