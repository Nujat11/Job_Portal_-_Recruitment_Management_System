<?php
session_start();
header('Content-Type: application/json');

// Destroy all session data
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo json_encode(["success" => true, "message" => "Logged out successfully."]);
} else {
    header("Location: logout_success.html");
    exit;
}
?>
