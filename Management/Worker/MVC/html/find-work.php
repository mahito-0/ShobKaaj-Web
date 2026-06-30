<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Work - ShobKaaj</title>
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Shared/MVC/css/base.css">
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Worker/MVC/css/find-work.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Navbar injected here -->
    <div id="navbar-container"></div>

    <div class="main-content">
        <div class="search-container">

            <div class="search-header">
                <h1 class="page-title">Find Your Next Project</h1>
                <p class="page-subtitle">Browse hundreds of active jobs and start working with top clients today.</p>
            </div>

            <!-- Search Form -->
            <div class="search-bar-wrapper">
                <form id="searchForm" class="search-form">
                    <!-- Keyword Search -->
                    <div class="input-group">
                        <i class="fas fa-search input-icon"></i>
                        <input type="text" id="searchInput" class="input search-input" placeholder="Search by title, keyword...">
                    </div>

                    <!-- Location Filter -->
                    <div class="input-group">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" id="locationInput" class="input search-input" placeholder="Location">
                    </div>

                    <!-- Category Filter -->
                    <div class="input-group">
                        <i class="fas fa-filter input-icon"></i>
                        <select id="categoryInput" class="input search-input" style="appearance:none; cursor:pointer;">
                            <option value="all">All Categories</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                            <option value="Writing">Writing</option>
                            <option value="Data Entry">Data Entry</option>
                            <option value="IT Support">IT Support</option>
                            <option value="tutoring">Tutoring</option>
                            <option value="delivery">Delivery</option>
                            <option value="repairs">Repairs</option>
                            <option value="household">Household</option>
                            <option value="it_support">IT Support</option>
                            <option value="design">Design & Creative</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <button type="submit" class="btn primary lg">
                        Search Jobs
                    </button>
                </form>
            </div>
            
            <div class="view-toggles" style="margin-bottom: 2rem; display: flex; justify-content: center; gap: 1rem;">
                <button class="btn primary" id="toggleGridBtn"><i class="fas fa-list"></i> List View</button>
                <button class="btn outline" id="toggleMapBtn"><i class="fas fa-map-marked-alt"></i> Map View</button>
            </div>

            <!-- Job Results -->
            <div id="jobsContainer" class="job-grid">
                <!-- Javascript will populate this -->
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 40px;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading active jobs...</p>
                </div>
            </div>

            <!-- Map View Container -->
            <div id="jobMapContainer" style="display: none; height: 600px; border-radius: var(--radius); border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 2rem;">
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/utils.js?v=<?php echo time(); ?>"></script>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/map-utils.js?v=<?php echo time(); ?>"></script>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/navbar.js"></script>
    <script src="../js/find-work.js?v=<?php echo time(); ?>"></script>
</body>

</html>