<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employer') {
    header('Location: login_logOut_interface.html');
    exit;
}
include 'connection.php';

$employer_id = $_SESSION['user_id'];

if (isset($_GET['job_id']) && is_numeric($_GET['job_id'])) {
    $job_id = $_GET['job_id'];

    // Delete the job posting (verifying it belongs to this employer)
    // Note: Due to ON DELETE CASCADE constraints in the DB, 
    // applications and interviews related to this job will also be deleted automatically.
    $stmt = $conn->prepare("DELETE FROM job_posting WHERE job_id = ? AND employer_id = ?");
    $stmt->bind_param("ii", $job_id, $employer_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['success_msg'] = "Job posting successfully deleted.";
    } else {
        $_SESSION['error_msg'] = "Error deleting job. Either it doesn't exist or you don't have permission to delete it.";
    }
    
    $stmt->close();
} else {
    $_SESSION['error_msg'] = "Invalid job ID provided.";
}

$conn->close();
header('Location: employer_dashboard.php');
exit;
?>
