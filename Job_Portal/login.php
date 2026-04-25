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
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Please fill in all fields."]);
    exit;
}

// Check admin table first
$stmtAdmin = $conn->prepare("SELECT admin_id, name, email, password FROM admin WHERE email = ?");
$stmtAdmin->bind_param("s", $email);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

if ($resultAdmin->num_rows > 0) {
    $admin = $resultAdmin->fetch_assoc();
    
    // Plain text password comparison (matches existing seed data)
    if ($admin['password'] === $password) {
        $_SESSION['user_id'] = $admin['admin_id']; // For consistency, though it's an admin ID
        $_SESSION['user_name'] = $admin['name'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['role'] = 'admin';
        $_SESSION['logged_in'] = true;

        echo json_encode([
            "success" => true,
            "message" => "Admin login successful!",
            "role" => "admin",
            "redirect" => "admin_dashboard.php",
            "user" => [
                "id" => $admin['admin_id'],
                "name" => $admin['name'],
                "email" => $admin['email']
            ]
        ]);
        $stmtAdmin->close();
        $conn->close();
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect admin password. Try again."]);
        $stmtAdmin->close();
        $conn->close();
        exit;
    }
}
$stmtAdmin->close();

// If not an admin, check Employer table
$stmtEmployer = $conn->prepare("SELECT employer_id, name, email, password, company_id FROM employer WHERE email = ?");
$stmtEmployer->bind_param("s", $email);
$stmtEmployer->execute();
$resultEmployer = $stmtEmployer->get_result();

if ($resultEmployer->num_rows > 0) {
    $emp = $resultEmployer->fetch_assoc();
    
    if ($emp['password'] === $password) {
        $_SESSION['user_id'] = $emp['employer_id'];
        $_SESSION['user_name'] = $emp['name'];
        $_SESSION['user_email'] = $emp['email'];
        $_SESSION['company_id'] = $emp['company_id'];
        $_SESSION['role'] = 'employer';
        $_SESSION['logged_in'] = true;

        echo json_encode([
            "success" => true,
            "message" => "Employer login successful!",
            "role" => "employer",
            "redirect" => "employer_dashboard.php",
            "user" => [
                "id" => $emp['employer_id'],
                "name" => $emp['name'],
                "email" => $emp['email'],
                "company_id" => $emp['company_id']
            ]
        ]);
        $stmtEmployer->close();
        $conn->close();
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password. Try again."]);
        $stmtEmployer->close();
        $conn->close();
        exit;
    }
}
$stmtEmployer->close();

// If not an employer, check Job Seeker table
$stmt = $conn->prepare("SELECT job_seeker_id, name, email, password, phone, skills, experience FROM job_seeker WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Plain text password comparison (matches existing seed data)
    if ($user['password'] === $password) {
        // Store user info in session
        $_SESSION['user_id'] = $user['job_seeker_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_skills'] = $user['skills'];
        $_SESSION['user_experience'] = $user['experience'];
        $_SESSION['role'] = 'job_seeker';
        $_SESSION['logged_in'] = true;

        echo json_encode([
            "success" => true,
            "message" => "Login successful!",
            "role" => "job_seeker",
            "user" => [
                "id" => $user['job_seeker_id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "phone" => $user['phone'],
                "skills" => $user['skills'],
                "experience" => $user['experience']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password. Try again."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No account found with this email."]);
}

$stmt->close();
$conn->close();
?>