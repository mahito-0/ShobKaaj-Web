<?php
require_once 'c:/xampp/htdocs/project-simulator-ShobKaaj/Management/Shared/MVC/db/config.php';
$sql = "SELECT id, email, role, latitude, longitude FROM users WHERE role = 'worker'";
$result = $conn->query($sql);
$workers = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($workers, JSON_PRETTY_PRINT);
?>
