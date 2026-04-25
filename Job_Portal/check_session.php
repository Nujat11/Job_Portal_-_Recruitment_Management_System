<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        "logged_in" => true,
        "role" => isset($_SESSION['role']) ? $_SESSION['role'] : 'job_seeker',
        "redirect" => (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : ((isset($_SESSION['role']) && $_SESSION['role'] === 'employer') ? 'employer_dashboard.php' : ''),
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email'],
            "phone" => isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : '',
            "skills" => isset($_SESSION['user_skills']) ? $_SESSION['user_skills'] : '',
            "experience" => isset($_SESSION['user_experience']) ? $_SESSION['user_experience'] : ''
        ]
    ]);
} else {
    echo json_encode(["logged_in" => false]);
}
?>
