<?php
header('Content-Type: application/json');
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = isset($data['email']) ? trim($data['email']) : '';

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Please enter your email address."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
    exit;
}

// Function to check if email exists in a specific table
function checkEmailExists($conn, $email, $table) {
    if ($table === 'admin') {
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ?");
    } else if ($table === 'employer') {
        $stmt = $conn->prepare("SELECT employer_id FROM employer WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT job_seeker_id FROM job_seeker WHERE email = ?");
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    
    return $exists;
}

$found = false;
$found_in = '';

if (checkEmailExists($conn, $email, 'job_seeker')) {
    $found = true;
    $found_in = 'job_seeker';
} else if (checkEmailExists($conn, $email, 'employer')) {
    $found = true;
    $found_in = 'employer';
} else if (checkEmailExists($conn, $email, 'admin')) {
    $found = true;
    $found_in = 'admin';
}

if ($found) {
    echo json_encode(["success" => true, "message" => "Account found. Proceed to reset password.", "table" => $found_in]);
} else {
    echo json_encode(["success" => false, "message" => "No account found for this email."]);
}

$conn->close();
?>
