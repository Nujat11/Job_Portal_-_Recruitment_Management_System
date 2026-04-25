<?php
header('Content-Type: application/json');
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = isset($data['email']) ? trim($data['email']) : '';
$new_password = isset($data['new_password']) ? $data['new_password'] : '';
$table = isset($data['table']) ? trim($data['table']) : '';

if (empty($email) || empty($new_password) || empty($table)) {
    echo json_encode(["success" => false, "message" => "Missing required information."]);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
    exit;
}

// Ensure table is valid to prevent SQL injection
if (!in_array($table, ['job_seeker', 'employer', 'admin'])) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

// Update password directly (plain text for consistency with this codebase)
$stmt = $conn->prepare("UPDATE `$table` SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $new_password, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Password updated successfully! You can now log in."]);
} else {
    echo json_encode(["success" => false, "message" => "Error securely updating password."]);
}

$stmt->close();
$conn->close();
?>
