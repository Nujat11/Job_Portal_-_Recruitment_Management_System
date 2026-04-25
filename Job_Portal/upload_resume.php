<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'job_seeker') {
    header("Location: login_logOut_interface.html#login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["resume"])) {
    $seeker_id = $_SESSION['user_id'];
    
    $target_dir = "uploads/resumes/";
    // Ensure directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["resume"]["name"]);
    // Generate unique name to prevent overwriting other users
    $new_file_name = "resume_" . $seeker_id . "_" . time() . ".pdf";
    $target_file = $target_dir . $new_file_name;
    
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Limit to PDF
    if ($fileType != "pdf") {
        $_SESSION['flash_err'] = "Only PDF files are allowed.";
        header("Location: seeker_dashboard.php");
        exit;
    }

    if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
        // Insert into DB
        $upload_date = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO resume (job_seeker_id, file_path, upload_date) VALUES (?, ?, ?)");
        $db_filepath = '/' . $target_dir . $new_file_name; // Store relative path starting with /
        
        $stmt->bind_param("iss", $seeker_id, $db_filepath, $upload_date);
        
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "Resume successfully uploaded!";
        } else {
            $_SESSION['flash_err'] = "Database error setting up resume.";
        }
        $stmt->close();
    } else {
        $_SESSION['flash_err'] = "Sorry, there was an error uploading your file.";
    }
}

header("Location: seeker_dashboard.php");
exit;
?>
