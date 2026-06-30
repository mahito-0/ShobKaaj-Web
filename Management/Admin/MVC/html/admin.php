<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../Shared/MVC/html/auth.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShobKaaj</title>
    <link rel="stylesheet" href="../../../Shared/MVC/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="container">
        <div class="section-header">
            <h1>Dashboard</h1>
            <div class="actions-cell">
                <button id="refreshBtn" class="btn secondary"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color:var(--primary);"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <div class="value" id="totalUsers">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color:#22c55e;"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3>Verified Users</h3>
                    <div class="value" id="verifiedUsers">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color:#ef4444;"><i class="fas fa-ban"></i></div>
                <div class="stat-info">
                    <h3>Terminated Users</h3>
                    <div class="value" id="terminatedUsers">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color:#3b82f6;"><i class="fas fa-briefcase"></i></div>
                <div class="stat-info">
                    <h3>Total Jobs</h3>
                    <div class="value" id="totalJobs">0</div>
                </div>
            </div>
        </div>

        <!-- Platform Activity Map -->
        <div style="margin-top: 30px;">
            <h2 style="margin-bottom: 20px;">Platform Activity</h2>
            <div id="adminMapContainer" style="height: 500px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.1);"></div>
        </div>

        <div style="display: grid; gap: 30px; margin-top: 30px;">
            <!-- Users Table -->
            <div>
                <h2 style="margin-bottom: 20px;">User Management</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="7" style="text-align:center;">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Jobs Table -->
            <div>
                <h2 style="margin-bottom: 20px;">Job Management</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th>Posted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jobsTableBody">
                            <tr>
                                <td colspan="7" style="text-align:center;">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../../Shared/MVC/js/utils.js"></script>
    <script src="../../../Shared/MVC/js/map-utils.js"></script>
    <script src="../../../Shared/MVC/js/navbar.js"></script>
    <script src="../js/admin.js"></script>
</body>

</html>