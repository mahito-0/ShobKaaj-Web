# Location Map Feature

## Overview
The Location Map feature enhances the ShobKaaj platform by providing interactive, geographic context for jobs and users. Using Leaflet.js and OpenStreetMap, it allows Clients to see Worker locations and drop pins for job sites, enables Workers to find jobs near them, and gives Admins a high-level view of platform activity.

## User Roles & Usage

### 1. Client
- **Post a Job**: Clients can drop a pin on an interactive map to set the precise location of a job (saving `latitude` and `longitude`).
- **Find Talent**: A "Map View" allows Clients to see Workers represented as markers on a map, facilitating local hiring. Clicking a marker reveals the Worker's mini-profile.

### 2. Worker
- **Profile Location**: Workers can define their base location by dropping a pin on a map within their profile settings.
- **Find Work**: A "Map View" toggle allows Workers to browse open jobs near their current or preferred location, making localized job hunting easier.

### 3. Admin
- **Dashboard Map**: A comprehensive "Platform Activity" map displays markers for active users and ongoing jobs, providing a geographic overview of platform engagement.

## Technical Implementation

### Frontend (Vanilla JavaScript)
- **Library**: Leaflet.js (loaded via CDN) with OpenStreetMap tiles. No API key is required.
- **`map-utils.js`**: A shared utility script located in `Management/Shared/MVC/js/map-utils.js` to handle map initialization, marker rendering, and HTML5 Geolocation.

### Backend (PHP API)
- API endpoints are updated to handle `latitude` and `longitude` fields.
  - `profileAPI.php`: Handles saving user coordinates.
  - `jobsAPI.php`: Handles saving job coordinates and fetching jobs within a geographic bounding box.
  - `talentAPI.php`: Fetches workers based on geographic location.
  - `adminAPI.php`: Aggregates active jobs and users for the admin map.

### Database Updates (MySQL)
**`users` table additions:**
- `latitude` (DECIMAL(10, 8))
- `longitude` (DECIMAL(11, 8))
- `address_text` (VARCHAR(255))

**`jobs` table additions:**
- `latitude` (DECIMAL(10, 8))
- `longitude` (DECIMAL(11, 8))
- `location_name` (VARCHAR(255))

## Key UI Components
- **Map Container**: A full-width or inline `div` initialized by Leaflet.
- **Custom Markers**: SVG or PNG icons colored differently based on the entity (e.g., Blue for Jobs, Green for Workers).
- **Popups**: Leaflet popups attached to markers containing brief details (Title, Name, Budget/Rate, and an action button like "View Profile" or "Apply").

## Security & Privacy
- **Opt-in Location**: Using browser geolocation will prompt the user for permission.
- **Data Truncation**: For privacy, exact worker locations might be slightly jittered or rounded on public views unless the worker explicitly opts for precise sharing.
