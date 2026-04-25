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
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$skills = isset($data['skills']) ? trim($data['skills']) : '';
$experience = isset($data['experience']) ? trim($data['experience']) : '';

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

// Check if email already exists
$check = $conn->prepare("SELECT job_seeker_id FROM job_seeker WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "An account with this email already exists."]);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Insert new job seeker
$stmt = $conn->prepare("INSERT INTO job_seeker (name, email, password, phone, skills, experience) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $email, $password, $phone, $skills, $experience);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;

    // Auto-login after registration
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_phone'] = $phone;
    $_SESSION['user_skills'] = $skills;
    $_SESSION['user_experience'] = $experience;
    $_SESSION['logged_in'] = true;

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully!",
        "user" => [
            "id" => $newId,
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "skills" => $skills,
            "experience" => $experience
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed. Please try again."]);
}

$stmt->close();
$conn->close();
?>
