<?php
session_start();
// Adjust path to config
require_once '../../../Shared/MVC/db/config.php';

// Auth Check: Only Clients
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    // Redirect to auth or dashboard if not allowed
    header("Location: ../../../Shared/MVC/html/auth.php");
    exit;
}

$title = $category = $budget = $description = "";
$title_err = $category_err = $budget_err = $desc_err = "";
$success_msg = $error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get Client ID
    $client_id = $_SESSION['user_id'];

    // 2. Validate Inputs
    // Title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a job title.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Category
    if (empty(trim($_POST["category"]))) {
        $category_err = "Please select a category.";
    } else {
        $category = trim($_POST["category"]);
    }

    // Budget
    if (empty(trim($_POST["budget"]))) {
        $budget_err = "Please enter a budget.";
    } else {
        $budget = trim($_POST["budget"]);
    }

    // Description
    if (empty(trim($_POST["description"]))) {
        $desc_err = "Please enter a job description.";
    } else {
        $description = trim($_POST["description"]);
    }

    $latitude = isset($_POST["latitude"]) ? floatval($_POST["latitude"]) : null;
    $longitude = isset($_POST["longitude"]) ? floatval($_POST["longitude"]) : null;

    // 3. Insert if no errors
    if (empty($title_err) && empty($category_err) && empty($budget_err) && empty($desc_err)) {

        // Prepare SQL - using defaults for location/deadline as they aren't in form yet
        $sql = "INSERT INTO jobs (client_id, title, description, budget, category, status, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, 'open', ?, ?, NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssdd", $client_id, $title, $description, $budget, $category, $latitude, $longitude);

            if ($stmt->execute()) {
                // Success - Redirect to Dashboard
                header("Location: client-dashboard.php");
                exit;
            } else {
                $error_msg = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database prepare error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job - ShobKaaj</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Shared/MVC/css/base.css">
    <link rel="stylesheet" href="/project-simulator-ShobKaaj/Management/Client/MVC/css/post-job.css">

</head>

<body>

    <div id="navbar-container"></div>

    <main class="container split-layout">

        <!-- Sidebar: Tips -->
        <aside class="layout-sidebar">
            <div class="tips-box">
                <h3><i class="far fa-lightbulb"></i> Posting Tips</h3>
                <ul class="tips-list">
                    <li><strong>Be Specific:</strong> Provide a clear and detailed description of the work.</li>
                    <li><strong>Set a Realistic Budget:</strong> Fair prices attract better quality freelancers.</li>
                    <li><strong>Choose the Right Category:</strong> Helps the right workers find your job.</li>
                    <li><strong>Double Check:</strong> Review your post for accuracy before submitting.</li>
                </ul>
            </div>
        </aside>

        <!-- Main Content: Form -->
        <div class="layout-content">
            <div class="sidebar-header" style="margin-bottom: 2rem;">
                <h1 class="page-title">Post a <span class="highlight">New Job</span></h1>
                <?php if ($error_msg): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); color: var(--error); padding: 1rem; border-radius: var(--radius); margin-top: 1rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="auth-card">
                <form id="postJobForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" class="input" placeholder="e.g. Need a Web Developer for E-commerce site" value="<?php echo htmlspecialchars($title); ?>" required>
                        <span style="color: var(--error); font-size: 0.85rem;"><?php echo $title_err; ?></span>
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="select" required>
                                <option value="" disabled <?php echo empty($category) ? 'selected' : ''; ?>>Select Category</option>
                                <?php
                                $categories = ["Web Development", "Graphic Design", "Digital Marketing", "Writing", "Data Entry", "IT Support", "tutoring", "delivery", "repairs", "household", "design", "other"];
                                foreach ($categories as $cat) {
                                    $selected = ($category == $cat) ? 'selected' : '';
                                    echo "<option value=\"$cat\" $selected>" . ucfirst($cat) . "</option>";
                                }
                                ?>
                            </select>
                            <span style="color: var(--error); font-size: 0.85rem;"><?php echo $category_err; ?></span>
                        </div>

                        <div class="form-group">
                            <label>Budget (৳)</label>
                            <div class="currency-input">
                                <span class="currency-symbol">৳</span>
                                <input type="number" name="budget" class="input" placeholder="5000" min="100" value="<?php echo htmlspecialchars($budget); ?>" required>
                            </div>
                            <span style="color: var(--error); font-size: 0.85rem;"><?php echo $budget_err; ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="textarea input" rows="8" placeholder="Describe the project requirements, deliverables, and timeline..." required><?php echo htmlspecialchars($description); ?></textarea>
                        <span style="color: var(--error); font-size: 0.85rem;"><?php echo $desc_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Job Location</label>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Drop a pin where the job is located.</p>
                        <div id="job-location-map" class="map-container"></div>
                        <input type="hidden" name="latitude" id="input-latitude" value="">
                        <input type="hidden" name="longitude" id="input-longitude" value="">
                    </div>

                    <div class="form-actions">
                        <a href="client-dashboard.php" class="btn outline">Cancel</a>
                        <button type="submit" class="btn primary btn-submit">
                            Post Job Now <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/utils.js"></script>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/navbar.js"></script>
    <script src="/project-simulator-ShobKaaj/Management/Shared/MVC/js/map-utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadLeafletResources(() => {
                const map = initMap('job-location-map');
                
                // Get user's current location to center map, or default to Dhaka
                getUserCurrentLocation((lat, lng) => {
                    map.setView([lat, lng], 13);
                    addLocationPickerMarker(map, (lat, lng) => {
                        document.getElementById('input-latitude').value = lat;
                        document.getElementById('input-longitude').value = lng;
                    }, lat, lng);
                }, () => {
                    addLocationPickerMarker(map, (lat, lng) => {
                        document.getElementById('input-latitude').value = lat;
                        document.getElementById('input-longitude').value = lng;
                    });
                });
            });
        });
    </script>
</body>

</html>