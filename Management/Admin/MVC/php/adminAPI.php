<?php
require_once '../../../Shared/MVC/db/config.php';
session_start();

// Basic security: Ensure only admins can access this API
// In a real app, strict session role checking would be here.
// For now, we trust the JS frontend checks + session exists
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

class AdminAPI
{
    var $db;

    function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    function sendResponse($status, $message, $data = [])
    {
        echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
        exit;
    }

    function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_stats':
                $this->getStats();
                break;
            case 'get_users':
                $this->getUsers();
                break;
            case 'get_jobs':
                $this->getJobs();
                break;
            case 'delete_job':
                $this->deleteJob();
                break;
            case 'update_status':
                $this->updateStatus();
                break;
            case 'get_job_analytics':
                $this->getJobAnalytics();
                break;
            case 'get_user_analytics':
                $this->getUserAnalytics();
                break;
            case 'get_revenue_analytics':
                $this->getRevenueAnalytics();
                break;
            default:
                $this->sendResponse('error', 'Invalid Action');
        }
    }

    function getStats()
    {
        $stats = [
            'total_users' => 0,
            'verified_users' => 0,
            'terminated_users' => 0,
            'total_jobs' => 0
        ];

        // Total Users
        $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin'");
        if ($res) $stats['total_users'] = $res->fetch_assoc()['c'];

        // Verified Users
        $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin' AND is_verified = 'verified'");
        if ($res) $stats['verified_users'] = $res->fetch_assoc()['c'];

        // Terminated Users
        $res = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role != 'admin' AND status = 'terminated'");
        if ($res) $stats['terminated_users'] = $res->fetch_assoc()['c'];

        // Total Jobs
        $res = $this->db->query("SELECT COUNT(*) as c FROM jobs");
        if ($res) $stats['total_jobs'] = $res->fetch_assoc()['c'];

        $this->sendResponse('success', 'Stats fetched', ['stats' => $stats]);
    }

    function getUsers()
    {
        $sql = "SELECT id, first_name, last_name, email, role, is_verified, status, latitude, longitude FROM users WHERE role != 'admin' ORDER BY id DESC";
        $result = $this->db->query($sql);
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $this->sendResponse('success', 'Users fetched', ['users' => $users]);
    }

    function getJobs()
    {
        // Join with users to get client name
        $sql = "SELECT j.id, j.title, j.budget, j.status, j.created_at, j.latitude, j.longitude,
                       u.first_name, u.last_name 
        FROM jobs j 
        JOIN users u ON j.client_id = u.id 
        ORDER BY j.created_at DESC";

        $result = $this->db->query($sql);
        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        $this->sendResponse('success', 'Jobs fetched', ['jobs' => $jobs]);
    }

    function deleteJob()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $jobId = $data['job_id'] ?? null;

        if (!$jobId) {
            $this->sendResponse('error', 'Job ID required');
        }

        $stmt = $this->db->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $jobId);

        if ($stmt->execute()) {
            $this->sendResponse('success', 'Job deleted successfully');
        } else {
            $this->sendResponse('error', 'Failed to delete job');
        }
    }

    function updateStatus()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? null;
        $type = $data['type'] ?? null;

        if (!$userId || !$type) {
            $this->sendResponse('error', 'Missing parameters');
        }

        $sql = "";

        if ($type === 'verify') {
            $sql = "UPDATE users SET is_verified = 'verified' WHERE id = ?";
        } elseif ($type === 'unverify') {
            $sql = "UPDATE users SET is_verified = 'unverified' WHERE id = ?";
        } elseif ($type === 'terminate') {
            $sql = "UPDATE users SET status = 'terminated' WHERE id = ?";
        } elseif ($type === 'activate') {
            $sql = "UPDATE users SET status = 'active' WHERE id = ?";
        } else {
            $this->sendResponse('error', 'Invalid type');
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->sendResponse('error', 'Prepare failed: ' . $this->db->error);
        }
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $this->sendResponse('success', 'User updated successfully');
        } else {
            $this->sendResponse('error', 'Database error: ' . $stmt->error);
        }
    }

    function getJobAnalytics()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get jobs posted in the date range
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM jobs 
                WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(created_at) 
                ORDER BY date ASC";

        $result = $this->db->query($sql);

        $dataMap = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $dataMap[$row['date']] = $row['count'];
            }
        }

        // Fill in specific dates with 0
        $finalData = [];
        $labels = [];

        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $labels[] = date('M d', $current);
            $finalData[] = $dataMap[$dateStr] ?? 0;
            $current = strtotime('+1 day', $current);
        }

        $this->sendResponse('success', 'Analytics data fetched', [
            'analytics' => [
                'labels' => $labels,
                'data' => $finalData
            ]
        ]);
    }

    function getUserAnalytics()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get users registered in the date range
        // Excluding admins from the count typically makes sense for "User Growth"
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM users 
                WHERE role != 'admin' 
                AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(created_at) 
                ORDER BY date ASC";

        $result = $this->db->query($sql);

        $dataMap = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $dataMap[$row['date']] = $row['count'];
            }
        }

        // Fill in specific dates with 0
        $finalData = [];
        $labels = [];

        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $labels[] = date('M d', $current);
            $finalData[] = $dataMap[$dateStr] ?? 0;
            $current = strtotime('+1 day', $current);
        }

        $this->sendResponse('success', 'User analytics fetched', [
            'analytics' => [
                'labels' => $labels,
                'data' => $finalData
            ]
        ]);
    }

    function getRevenueAnalytics()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get revenue from completed jobs in the date range
        $sql = "SELECT DATE(created_at) as date, SUM(budget) as revenue 
                FROM jobs 
                WHERE status = 'completed'
                AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(created_at) 
                ORDER BY date ASC";

        $result = $this->db->query($sql);

        $dataMap = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $dataMap[$row['date']] = $row['revenue'];
            }
        }

        // Fill in specific dates with 0
        $finalData = [];
        $labels = [];

        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $labels[] = date('M d', $current);
            $finalData[] = $dataMap[$dateStr] ?? 0;
            $current = strtotime('+1 day', $current);
        }

        $this->sendResponse('success', 'Revenue analytics fetched', [
            'analytics' => [
                'labels' => $labels,
                'data' => $finalData
            ]
        ]);
    }
}

header('Content-Type: application/json');
$api = new AdminAPI();
$api->handleRequest();
