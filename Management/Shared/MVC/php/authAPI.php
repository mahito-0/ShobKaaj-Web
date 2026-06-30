<?php
require_once '../db/config.php';
session_start();
// Prevent PHP warnings/notices from breaking JSON structure
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

class AuthAPI
{
    var $db;

    function __construct()
    {
        global $conn;
        $this->db = $conn;

        if (!$this->db) {
            $this->sendResponse('error', 'Database connection not available');
        }
    }

    // Send JSON response and exit
    function sendResponse($status, $message, $data = [])
    {
        $response = ['status' => $status, 'message' => $message];
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        echo json_encode($response);
        exit;
    }


    function getInput($key)
    {
        // 1. Try GET (Query Params)
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        // 2. Try standard POST
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // 2. Fallback to JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input[$key])) {
            return $input[$key];
        }

        return '';
    }

    function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        // Simple Router
        switch ($action) {
            case 'select':
                $this->Select();
                break;
            case 'register':
                $this->Register();
                break;
            case 'login':
                $this->Login();
                break;
            case 'update_profile':
                $this->UpdateProfile();
                break;
            case 'change_password':
                $this->ChangePassword();
                break;
            case 'update_avatar':
                $this->UpdateAvatar();
                break;
            case 'reset_password':
                $this->ResetPassword();
                break;
            case 'get_workers':
                $this->GetWorkers();
                break;
            case 'get_public_profile':
                $this->GetPublicProfile();
                break;
            case 'logout':
                $this->Logout();
                break;
            default:
                $this->sendResponse('error', 'No action specified');
        }
    }

    // Fetch all users for testing/admin purposes
    function Select()
    {
        $users = [];
        // Fetch users in descending order to see newest first
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY id DESC");

        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();

            while ($userRow = $result->fetch_assoc()) {
                $users[] = [
                    'id' => $userRow['id'],
                    'name' => $userRow['first_name'] . ' ' . $userRow['last_name'],
                    'email' => $userRow['email'],
                    'role' => $userRow['role']
                ];
            }
            $stmt->close();
        }
        echo json_encode($users);
    }

    function Register()
    {
        $firstName = $this->getInput('first_name');
        $lastName = $this->getInput('last_name');
        $email = $this->getInput('email');
        $phone = $this->getInput('phone');
        $password = $this->getInput('password');
        $role = $this->getInput('user_type') ?: 'client'; // Default to client if not specified

        if (!$firstName || !$email || !$password) {
            $this->sendResponse('error', 'Please fill in all required fields.');
        }

        // Check for existing email to prevent duplicates
        $checkStmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            $this->sendResponse('error', 'This email is already registered.');
        }
        $checkStmt->close();

        // Securely hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssss", $firstName, $lastName, $email, $phone, $hashedPassword, $role);
            if ($stmt->execute()) {
                $this->sendResponse('success', 'Registration successful! You can now log in.');
            } else {
                $this->sendResponse('error', 'Registration failed. Please try again.');
            }
            $stmt->close();
        } else {
            $this->sendResponse('error', 'Database error: Prepare failed');
        }
    }

    function Login()
    {
        $email = $this->getInput('email');
        $password = $this->getInput('password');
        $rememberMe = $this->getInput('remember_me'); // Get "Remember Me" checkbox value

        if (!$email || !$password) {
            $this->sendResponse('error', 'Please enter your email and password.');
        }

        // Retrieve user by email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); // Bind parameters

        if ($stmt->execute()) {
            $result = $stmt->get_result(); // Get result set

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify the password hash
                if (password_verify($password, $user['password'])) {
                    // Check if terminated
                    if (isset($user['status']) && $user['status'] === 'terminated') {
                        $this->sendResponse('error', 'Your account has been terminated. Please contact support.');
                    }

                    // Remove password
                    unset($user['password']);

                    // Set Session 
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];

                    // Cookie
                    if ($rememberMe === 'true' || $rememberMe === '1' || $rememberMe === true) {
                        $cookieExpiry = time() + (30 * 24 * 60 * 60); // 30 days from now
                        setcookie('remembered_email', $email, $cookieExpiry, '/', '', false, true);
                    } else {
                        setcookie('remembered_email', '', time() - 3600, '/', '', false, true);
                    }

                    $this->sendResponse('success', 'Welcome back!', ['user' => $user]);
                } else {
                    $this->sendResponse('error', 'The password you entered is incorrect.');
                }
            } else {
                $this->sendResponse('error', 'No account found with that email address.');
            }
            $stmt->close();
        } else {
            $this->sendResponse('error', 'Login failed due to database error.');
        }
    }

    function UpdateProfile()
    {
        $id = $this->getInput('id');
        $fname = $this->getInput('first_name');
        $lname = $this->getInput('last_name');
        $phone = $this->getInput('phone');
        $skills = $this->getInput('skills');
        $latitude = $this->getInput('latitude') !== '' ? $this->getInput('latitude') : null;
        $longitude = $this->getInput('longitude') !== '' ? $this->getInput('longitude') : null;

        if (!$id || !$fname || !$lname) {
            $this->sendResponse('error', 'Missing required fields');
        }

        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, skills = ?, latitude = ?, longitude = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("ssssddi", $fname, $lname, $phone, $skills, $latitude, $longitude, $id);
            $stmt->execute();
            $stmt->close();


            $check = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $check->bind_param("i", $id);
            $check->execute();
            $result = $check->get_result();

            if ($user = $result->fetch_assoc()) {
                unset($user['password']);
                $this->sendResponse('success', 'Profile updated', ['user' => $user]);
            } else {
                $this->sendResponse('error', 'Failed to fetch updated profile');
            }
            $check->close();
        } else {
            $this->sendResponse('error', 'Database error');
        }
    }

    function ChangePassword()
    {
        $id = $this->getInput('id');
        $current = $this->getInput('current_password');
        $new = $this->getInput('new_password');

        if (!$id || !$current || !$new) {
            $this->sendResponse('error', 'Missing password fields');
        }

        $check = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();
        $user = $result->fetch_assoc();
        $check->close();

        if (!$user || !password_verify($current, $user['password'])) {
            $this->sendResponse('error', 'Incorrect current password');
        }

        $hashed_pass = password_hash($new, PASSWORD_DEFAULT);
        $update = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed_pass, $id);

        if ($update->execute()) {
            $this->sendResponse('success', 'Password changed successfully');
        } else {
            $this->sendResponse('error', 'Failed to update password');
        }
        $update->close();
    }

    function UpdateAvatar()
    {
        $id = $this->getInput('id');

        if (!$id || !isset($_FILES['avatar'])) {
            $this->sendResponse('error', 'Missing ID or file');
        }

        $file = $_FILES['avatar'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $this->sendResponse('error', 'Invalid file type. Only JPG, PNG, GIF allowed.');
        }

        if ($file['size'] > 5 * 1024 * 1024) { // Increased to 5MB
            $this->sendResponse('error', 'File too large. Max 5MB.');
        }


        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $role = $row['role'];
        } else {
            $role = '';
        }
        $stmt->close();

        $targetDir = "";
        $webPathPrefix = "";
        $baseWebPath = "/project-simulator-ShobKaaj/Management";

        if ($role === 'client') {
            $targetDir = "../../../Client/MVC/images/users/";
            $webPathPrefix = "/Client/MVC/images/users/";
        } elseif ($role === 'worker') {
            $targetDir = "../../../Worker/MVC/images/users/";
            $webPathPrefix = "/Worker/MVC/images/users/";
        } elseif ($role === 'admin') {
            $targetDir = "../../../Admin/MVC/images/users/";
            $webPathPrefix = "/Admin/MVC/images/users/";
        } else {
            // Fallback
            $targetDir = "../images/users/";
            $webPathPrefix = "/Shared/MVC/images/users/";
        }

        if ($targetDir !== "" && !file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $newFilename = "user_" . $id . "_" . time() . "." . $ext;
        $targetFile = $targetDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Save absolute web path to DB to avoid client-side guessing
            $dbPath = "/project-simulator-ShobKaaj/Management" . $webPathPrefix . $newFilename;

            $sql = "UPDATE users SET avatar = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $dbPath, $id);

            if ($stmt->execute()) {
                $this->sendResponse('success', 'Avatar updated', ['avatar' => $dbPath]);
            } else {
                $this->sendResponse('error', 'Database update failed');
            }
            $stmt->close();
        } else {
            $this->sendResponse('error', 'Failed to move uploaded file');
        }
    }

    function ResetPassword()
    {
        $email = $this->getInput('email');
        $new_password = $this->getInput('new_password');

        if (!$email || !$new_password) {
            $this->sendResponse('error', 'Email and password are required');
        }

        $check = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $check->close();
            $this->sendResponse('error', 'User not found');
        }
        $check->close();

        $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed_pass, $email);

        if ($update->execute()) {
            $this->sendResponse('success', 'Password reset successfully');
        } else {
            $this->sendResponse('error', 'Failed to update password in database');
        }
        $update->close();
    }

    function GetWorkers()
    {
        $search = $this->getInput('search');

        // Base Query: Get only workers
        $sql = "SELECT users.id, users.first_name, users.last_name, users.avatar, users.email, users.skills, users.latitude, users.longitude,
                        (SELECT COUNT(*) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE applications.worker_id = users.id AND jobs.status = 'completed') as completed_jobs,
                        (SELECT SUM(bid_amount) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE applications.worker_id = users.id AND jobs.status = 'completed') as total_earnings,
                        (SELECT AVG(rating) FROM reviews WHERE reviewee_id = users.id) as rating,
                        (SELECT COUNT(*) FROM reviews WHERE reviewee_id = users.id) as reviews_count
                        FROM users
                        WHERE role = 'worker'";

        $params = [];
        $types = "";

        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        $sql .= " ORDER BY rating DESC, completed_jobs DESC";

        $stmt = $this->db->prepare($sql);
        if ($search) {
            // Bind params dynamically
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $workers = $result->fetch_all(MYSQLI_ASSOC);
            $response = ['status' => 'success', 'message' => 'Workers fetched', 'workers' => $workers];
        file_put_contents(__DIR__ . '/get_workers_log.txt', date('Y-m-d H:i:s') . " - SEARCH: " . ($search ? $search : 'NONE') . " - RESPONSE: " . json_encode($response) . "\n", FILE_APPEND);
        
        $this->sendResponse('success', 'Workers fetched', ['workers' => $workers]);
        } else {
            $this->sendResponse('error', 'Query failed');
        }
        $stmt->close();
    }

    function GetPublicProfile()
    {
        $id = $this->getInput('id');
        if (!$id) {
            $this->sendResponse('error', 'User ID required');
        }

        // 1. Get User Basic Info
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, phone, role, avatar, created_at, skills FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $this->sendResponse('error', 'User not found');
        }

        $stats = [];
        $reviews = [];

        if ($user['role'] === 'worker') {
            // Worker Stats
            $sqlStats = "SELECT
                            (SELECT COUNT(*) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE applications.worker_id = ? AND jobs.status = 'completed') as completed_jobs,
                            (SELECT COALESCE(SUM(bid_amount), 0) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE applications.worker_id = ? AND jobs.status = 'completed') as total_earnings,
                            (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE reviewee_id = ?) as rating";
            $stmtStats = $this->db->prepare($sqlStats);
            $stmtStats->bind_param("iii", $id, $id, $id);
            $stmtStats->execute();
            $resultStats = $stmtStats->get_result();
            $stats = $resultStats->fetch_assoc();
            $stmtStats->close();

            // Fetch Reviews
            $sqlReviews = "SELECT r.rating, r.comment, r.created_at, j.title as job_title, u.first_name as reviewer_name
                              FROM reviews r
                              JOIN jobs j ON r.job_id = j.id
                              JOIN users u ON r.reviewer_id = u.id
                              WHERE r.reviewee_id = ?
                              ORDER BY r.created_at DESC LIMIT 10";
            $stmtReviews = $this->db->prepare($sqlReviews);
            $stmtReviews->bind_param("i", $id);
            $stmtReviews->execute();
            $resultReviews = $stmtReviews->get_result();
            $reviews = $resultReviews->fetch_all(MYSQLI_ASSOC);
            $stmtReviews->close();
        } else {
            // Client Stats
            $sqlStats = "SELECT
                            (SELECT COUNT(*) FROM jobs WHERE client_id = ?) as jobs_posted,
                            (SELECT COALESCE(SUM(budget), 0) FROM jobs WHERE client_id = ? AND status = 'completed') as total_spent";
            $stmtStats = $this->db->prepare($sqlStats);
            $stmtStats->bind_param("ii", $id, $id);
            $stmtStats->execute();
            $resultStats = $stmtStats->get_result();
            $stats = $resultStats->fetch_assoc();
            $stmtStats->close();
        }

        $this->sendResponse('success', 'Profile fetched', [
            'user' => $user,
            'stats' => $stats,
            'reviews' => $reviews
        ]);
    }


    function Logout()
    {
        session_unset();
        session_destroy();

        // clear the "Remember Me" cookie on logout
        if (isset($_COOKIE['remembered_email'])) {
            setcookie('remembered_email', '', time() - 3600, '/', '', false, true);
        }

        $this->sendResponse('success', 'Logged out successfully');
    }
}

// Router Logic
header("Content-Type: application/json");
$AuthAPI = new AuthAPI;
$AuthAPI->handleRequest();
