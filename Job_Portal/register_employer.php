<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Read JSON body from fetch()
$data = json_decode(file_get_contents("php://input"), true);
$name = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

// Validation
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Name, email, and password are required."]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
    exit;
}

// Check if email already exists in employer table
$check = $conn->prepare("SELECT employer_id FROM employer WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "An employer account with this email already exists."]);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Insert new employer (company_id is set to NULL initially)
$stmt = $conn->prepare("INSERT INTO employer (name, email, password, company_id) VALUES (?, ?, ?, NULL)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;

    // Auto-login after registration
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['company_id'] = null;
    $_SESSION['role'] = 'employer';
    $_SESSION['logged_in'] = true;

    echo json_encode([
        "success" => true,
        "message" => "Employer account created successfully!",
        "role" => "employer",
        "redirect" => "employer_dashboard.php",
        "user" => [
            "id" => $newId,
            "name" => $name,
            "email" => $email,
            "company_id" => null
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed. Please try again."]);
}

$stmt->close();
$conn->close();
?>
