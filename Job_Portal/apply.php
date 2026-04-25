<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'job_seeker') {
    header("Location: login_logOut_interface.html#login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);
    $seeker_id = $_SESSION['user_id'];

    $app_check = $conn->prepare("SELECT application_id FROM application_tracking WHERE job_seeker_id = ? AND job_id = ?");
    $app_check->bind_param("ii", $seeker_id, $job_id);
    $app_check->execute();
    $app_result = $app_check->get_result();
    $app_check->close();

    if ($app_result->num_rows > 0) {
        $_SESSION['flash_err'] = "You have already applied for this job.";
        header("Location: seeker_dashboard.php");
        exit;
    }

    if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['resume_file']['tmp_name'];
        $file_name = $_FILES['resume_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'pdf') {
            $_SESSION['flash_err'] = "Only PDF files are allowed for resumes.";
            header("Location: job_portal.php");
            exit;
        }

        $upload_dir = 'uploads/resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_file_name = "resume_" . $seeker_id . "_" . time() . ".pdf";
        $file_path = $upload_dir . $new_file_name; // Fixed: using relative path
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            $upload_date = date('Y-m-d');
            $insert_res = $conn->prepare("INSERT INTO resume (job_seeker_id, file_path, upload_date) VALUES (?, ?, ?)");
            $insert_res->bind_param("iss", $seeker_id, $file_path, $upload_date);
            $insert_res->execute();
            $resume_id = $insert_res->insert_id;
            $insert_res->close();

            $apply_date = date('Y-m-d H:i:s');
            $status = 'Applied';
            
            $insert_app = $conn->prepare("INSERT INTO application_tracking (job_seeker_id, job_id, resume_id, status, apply_date) VALUES (?, ?, ?, ?, ?)");
            $insert_app->bind_param("iiiss", $seeker_id, $job_id, $resume_id, $status, $apply_date);
            
            if ($insert_app->execute()) {
                $_SESSION['flash_msg'] = "Successfully applied for the job with your new resume!";
            } else {
                $_SESSION['flash_err'] = "Error submitting application. Please try again.";
            }
            $insert_app->close();

        } else {
            $_SESSION['flash_err'] = "Failed to save the uploaded resume.";
        }
    } else {
        $_SESSION['flash_err'] = "You must upload a resume (PDF) to apply for this job.";
    }
}

header("Location: seeker_dashboard.php");
exit;
?>
