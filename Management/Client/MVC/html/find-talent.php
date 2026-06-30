<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Talent - ShobKaaj</title>
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Shared/MVC/css/base.css">
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Client/MVC/css/find-talent.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/navbar.js"></script>

    <main class="container">
        <section class="search-header">
            <h1>Find Top Talent</h1>
            <p class="subtitle">Connect with skilled professionals for your next project.</p>

            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search by name or skill...">
                <button class="btn primary" id="searchBtn">Search</button>
            </div>
            
            <div class="view-toggles" style="margin-top: 1rem; display: flex; justify-content: center; gap: 1rem;">
                <button class="btn primary" id="toggleGridBtn"><i class="fas fa-th-large"></i> Grid View</button>
                <button class="btn outline" id="toggleMapBtn"><i class="fas fa-map-marked-alt"></i> Map View</button>
            </div>
        </section>

        <section class="talent-grid" id="talentGrid">
            <!-- Workers will be loaded here -->
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i> Loading talent...
            </div>
        </section>

        <section id="talentMapContainer" style="display: none; height: 600px; border-radius: var(--radius); border: 1px solid rgba(255, 255, 255, 0.1); margin-top: 2rem;">
            <!-- Map will be rendered here -->
        </section>
    </main>

    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/utils.js?v=<?php echo time(); ?>"></script>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/map-utils.js?v=<?php echo time(); ?>"></script>
    <script src="../js/find-talent.js?v=<?php echo time(); ?>"></script>
</body>

</html>