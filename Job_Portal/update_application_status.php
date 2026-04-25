<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
include 'connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$application_id = isset($data['application_id']) ? intval($data['application_id']) : 0;
$status = isset($data['status']) ? trim($data['status']) : '';

$valid_statuses = ['Applied', 'Shortlisted', 'Rejected', 'Hired'];

if ($application_id <= 0 || !in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Ensure the application belongs to a job posted by this employer
$employer_id = $_SESSION['user_id'];
$check_stmt = $conn->prepare("SELECT a.application_id, a.job_seeker_id, j.title, c.name as company_name FROM application_tracking a JOIN job_posting j ON a.job_id = j.job_id JOIN employer e ON j.employer_id = e.employer_id LEFT JOIN company c ON e.company_id = c.company_id WHERE a.application_id = ? AND j.employer_id = ? LIMIT 1");
$check_stmt->bind_param("ii", $application_id, $employer_id);
$check_stmt->execute();
$res = $check_stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    $check_stmt->close();
    exit;
}
$app_info = $res->fetch_assoc();
$check_stmt->close();

$update_stmt = $conn->prepare("UPDATE application_tracking SET status = ? WHERE application_id = ?");
$update_stmt->bind_param("si", $status, $application_id);

if ($update_stmt->execute()) {
    // If status is Hired, create a notification
    if ($status === 'Hired') {
        $seeker_id = $app_info['job_seeker_id'];
        $job_title = $app_info['title'];
        $company_name = $app_info['company_name'] ?? 'our company'; // Fallback if no company name
        
        $msg = "Congratulations! You have been hired for the position of '" . $job_title . "' at " . $company_name . ".";
        
        $notif_stmt = $conn->prepare("INSERT INTO notification (job_seeker_id, message) VALUES (?, ?)");
        $notif_stmt->bind_param("is", $seeker_id, $msg);
        $notif_stmt->execute();
        $notif_stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$update_stmt->close();
$conn->close();
?>
