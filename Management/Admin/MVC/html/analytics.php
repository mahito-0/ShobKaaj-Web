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
    <title>Analytics - ShobKaaj</title>
    <link rel="stylesheet" href="../../../Shared/MVC/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="container">
        <div class="section-header">
            <h1>Analytics</h1>
            <div class="actions-cell">
                <button id="refreshBtn" class="btn secondary"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
        </div>

        <!-- Job Trends -->
        <div style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Job Trends</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" id="startDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <span>to</span>
                    <input type="date" id="endDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <button id="filterBtn" class="btn primary sm">Filter</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="jobAnalyticsChart"></canvas>
            </div>
        </div>

        <!-- User Growth -->
        <div style="margin-top: 30px; margin-bottom: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>User Growth</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" id="userStartDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <span>to</span>
                    <input type="date" id="userEndDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <button id="userFilterBtn" class="btn primary sm">Filter</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="userAnalyticsChart"></canvas>
            </div>
        </div>

        <!-- Revenue Analytics -->
        <div style="margin-top: 30px; margin-bottom: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Revenue Analytics</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" id="revenueStartDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <span>to</span>
                    <input type="date" id="revenueEndDate" class="form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <button id="revenueFilterBtn" class="btn primary sm">Filter</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="revenueAnalyticsChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../Shared/MVC/js/utils.js"></script>
    <script src="../../../Shared/MVC/js/navbar.js"></script>
    <script src="../js/analytics.js"></script>
</body>

</html>