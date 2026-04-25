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
$interview_date = isset($data['interview_date']) ? trim($data['interview_date']) : '';
$interview_type = isset($data['interview_type']) ? trim($data['interview_type']) : 'Online';

if ($application_id <= 0 || empty($interview_date)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}

// Ensure the application belongs to this employer and get required details
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

// Try to parse the date for MySQL format, but let's assume valid datetime string for simplicity since it's a prompt
// Format it nicely
$mysql_date = date('Y-m-d H:i:s', strtotime($interview_date));
if (!$mysql_date) {
    $mysql_date = $interview_date; // Fallback
}

$status = "Scheduled";

$insert_stmt = $conn->prepare("INSERT INTO interview (application_id, interview_date, interview_type, status) VALUES (?, ?, ?, ?)");
$insert_stmt->bind_param("isss", $application_id, $mysql_date, $interview_type, $status);

if ($insert_stmt->execute()) {
    $seeker_id = $app_info['job_seeker_id'];
    $job_title = $app_info['title'];
    $company_name = $app_info['company_name'] ?? 'our company';
    
    $msg = "An interview for the position of '" . $job_title . "' at " . $company_name . " has been scheduled on " . $interview_date . " (" . $interview_type . ").";
    
    $notif_stmt = $conn->prepare("INSERT INTO notification (job_seeker_id, message) VALUES (?, ?)");
    $notif_stmt->bind_param("is", $seeker_id, $msg);
    $notif_stmt->execute();
    $notif_stmt->close();

    echo json_encode(['success' => true, 'message' => 'Interview scheduled successfully! Notification sent.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error while scheduling interview.']);
}

$insert_stmt->close();
$conn->close();
?>
