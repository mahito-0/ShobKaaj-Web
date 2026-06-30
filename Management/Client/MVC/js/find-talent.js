let mapInstance = null;
let currentWorkers = [];

document.addEventListener('DOMContentLoaded', () => {
    fetchWorkers();

    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    searchBtn.addEventListener('click', () => {
        fetchWorkers(searchInput.value);
    });

    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            fetchWorkers(searchInput.value);
        }
    });
    const toggleGridBtn = document.getElementById('toggleGridBtn');
    const toggleMapBtn = document.getElementById('toggleMapBtn');
    const talentGrid = document.getElementById('talentGrid');
    const mapContainer = document.getElementById('talentMapContainer');
    
    let mapMarkers = [];

    toggleGridBtn.addEventListener('click', () => {
        talentGrid.style.display = 'grid'; // Base CSS handles grid template
        mapContainer.style.display = 'none';
        toggleGridBtn.classList.replace('outline', 'primary');
        toggleMapBtn.classList.replace('primary', 'outline');
    });

    toggleMapBtn.addEventListener('click', () => {
        talentGrid.style.display = 'none';
        mapContainer.style.display = 'block';
        toggleMapBtn.classList.replace('outline', 'primary');
        toggleGridBtn.classList.replace('primary', 'outline');
        
        // Initialize map if not already done
        if (!mapInstance) {
            loadLeafletResources(() => {
                mapInstance = initMap('talentMapContainer');
                renderMapMarkers(window.currentWorkers || []);
            });
        } else {
            // Force Leaflet to recalculate map size since it was hidden
            setTimeout(() => {
                mapInstance.invalidateSize();
            }, 100);
        }
    });
});


async function fetchWorkers(query = '') {
    const grid = document.getElementById('talentGrid');
    // Show spinner while fetching
    grid.innerHTML = `<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading talent...</div>`;

    try {
        const basePath = '/project-simulator-ShobKaaj/Management/Shared/MVC/php/';
        const url = `${basePath}authAPI.php?action=get_workers&search=${encodeURIComponent(query)}&_t=${Date.now()}`;
        // AJAX: Fetch list of workers
        const response = await fetch(url);
        const result = await response.json();
        console.log("FETCHED WORKERS API RESULT:", result);

        if (result.status === 'success' && result.workers.length > 0) {
            let cardsHtml = '';

            result.workers.forEach(worker => {
                const avatar = window.getAvatarPath(worker.avatar, 'worker');
                const rating = parseFloat(worker.rating || 0).toFixed(1);
                const reviewCount = worker.reviews_count || 0;
                const jobsDone = worker.completed_jobs || 0;
                const earnings = parseInt(worker.total_earnings || 0).toLocaleString();

                cardsHtml += `
                <div class="talent-card">
                    <img src="${avatar}" alt="${worker.first_name}" class="talent-avatar">
                    <h3 class="talent-name">${worker.first_name} ${worker.last_name}</h3>
                    
                    <div class="talent-rating">
                        <i class="fas fa-star"></i> ${rating} <span>(${reviewCount} reviews)</span>
                    </div>

                    <div style="margin: 10px 10px; display:flex; flex-wrap:wrap; gap:4px;">
                        ${worker.skills ? worker.skills.split(',').slice(0, 3).map(s => `<span class="badge secondary" style="font-size:0.75rem; align-items:center">${s.trim()}</span>`).join('') : ''}
                    </div>

                    <div class="talent-stats">
                        <div class="stat-item">
                            <span class="stat-val">${jobsDone}</span>
                            <span class="stat-label">Jobs Done</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">৳${earnings}</span>
                            <span class="stat-label">Earned</span>
                        </div>
                    </div>

                    <button class="btn outline view-profile-btn" onclick="window.location.href='/project-simulator-ShobKaaj/Management/Shared/MVC/html/view-profile.php?id=${worker.id}'" style="cursor:pointer;">View Profile</button>
                </div>
                `;
            });
            grid.innerHTML = cardsHtml;
            
            // Save for map
            window.currentWorkers = result.workers;
            if (typeof mapInstance !== 'undefined' && mapInstance) {
                renderMapMarkers(window.currentWorkers);
            }
        } else {
            grid.innerHTML = `<div class="empty-state">No workers found matching your search.</div>`;
        }
    } catch (error) {
        console.error('Failed to load workers:', error);
        grid.innerHTML = `<div class="empty-state" style="color:var(--error)">Unable to load talent list. Please execute 'Refresh' or try again later.</div>`;
    }
}

function renderMapMarkers(workers) {
    if (!mapInstance) return;
    
    // Clear existing markers
    if (window.mapLayerGroup) {
        mapInstance.removeLayer(window.mapLayerGroup);
    }
    
    window.mapLayerGroup = L.layerGroup().addTo(mapInstance);
    
    let hasLocation = false;
    const bounds = L.latLngBounds();

    workers.forEach(worker => {
        console.log("Checking worker location:", worker.email, worker.latitude, worker.longitude);
        if (worker.latitude && worker.longitude) {
            hasLocation = true;
            const lat = parseFloat(worker.latitude);
            const lng = parseFloat(worker.longitude);
            const avatar = window.getAvatarPath(worker.avatar, 'worker');
            
            const popupContent = `
                <div style="text-align: center; min-width: 150px;">
                    <img src="${avatar}" style="width: 50px; height: 50px; border-radius: 50%; margin-bottom: 5px;">
                    <h4 style="margin: 0; color: #111;">${worker.first_name} ${worker.last_name}</h4>
                    <p style="margin: 5px 0; font-size: 0.8rem; color: #666;">${worker.skills ? worker.skills.split(',')[0] : 'Freelancer'}</p>
                    <a href="/project-simulator-ShobKaaj/Management/Shared/MVC/html/view-profile.php?id=${worker.id}" style="display: inline-block; background: #6366f1; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.8rem;">View Profile</a>
                </div>
            `;
            
            // Use a custom div icon to avoid Leaflet default image 404s
            const userIcon = L.divIcon({
                html: '<i class="fas fa-user" style="color: #6366f1; font-size: 24px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3));"></i>',
                className: 'custom-div-icon',
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                popupAnchor: [0, -24]
            });
            
            const marker = L.marker([lat, lng], {icon: userIcon}).bindPopup(popupContent);
            window.mapLayerGroup.addLayer(marker);
            bounds.extend([lat, lng]);
        }
    });

    if (hasLocation) {
        mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
    }
}
