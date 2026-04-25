<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employer') {
    header('Location: login_logOut_interface.html');
    exit;
}
include 'connection.php';

$employer_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $salary = !empty($_POST['salary']) ? floatval($_POST['salary']) : null;
    $city = trim($_POST['city']);
    $street = trim($_POST['street']);
    
    if (empty($title) || empty($description) || empty($city)) {
        $message = "Please fill in all required fields (*).";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO job_posting (title, description, salary, city, street, employer_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $title, $description, $salary, $city, $street, $employer_id);
        
        if ($stmt->execute()) {
            $message = "Job posting published successfully!";
            $msg_type = "success";
        } else {
            $message = "Error publishing job. Please try again.";
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - JobPortal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 0; }
        .navbar { background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-size: 20px; font-weight: 700; color: #0f172a; text-decoration: none;}
        .logo-text { font-weight: 400; color: #475569; font-size: 16px; margin-left: 5px; }
        .nav-buttons { display: flex; gap: 15px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; border: 1px solid transparent; background: transparent; display: inline-block; }
        .btn-outline { border-color: #64748b; color: #475569; }
        .btn-outline:hover { background: #f1f5f9; }
        .btn-red { background: #ef4444; color: white; }
        .btn-red:hover { background: #dc2626; }
        
        .welcome-banner { background: linear-gradient(90deg, #375b48 0%, #87cca2 100%); color: #0f172a; padding: 30px 40px; }
        .welcome-banner h1 { margin: 0; font-size: 24px; font-weight: 600; }
        
        .container { max-width: 800px; margin: -20px auto 40px; padding: 0 20px; position: relative; z-index: 10; }
        
        .form-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; margin-bottom: 0; }
        
        label { display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 8px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; font-family: 'Poppins', sans-serif; box-sizing: border-box; outline: none; transition: border-color 0.2s; }
        input:focus, textarea:focus { border-color: #10b981; }
        
        .msg { padding: 12px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; display: none; }
        .msg.show { display: block; }
        .msg.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .msg.error { background: #fee2e2; color: #b91c1c; border: 1px solid #ef4444; }
        
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .btn-gray { background: #64748b; color: white; padding: 10px 20px; }
        .btn-gray:hover { background: #475569; }
        .btn-green { background: #10b981; color: white; padding: 10px 20px; border: none; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; border-radius: 6px; cursor: pointer; }
        .btn-green:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="employer_dashboard.php" class="logo">JobPortal <span class="logo-text">BD - Employer</span></a>
        <div class="nav-buttons">
            <a href="employer_dashboard.php" class="btn btn-outline">Dashboard</a>
            <a href="logout.php" class="btn btn-red">Logout</a>
        </div>
    </div>
    
    <div class="welcome-banner">
        <h1>Create a New Job Posting</h1>
    </div>
    
    <div class="container">
        <div class="form-card">
            <?php if ($message): ?>
                <div class="msg show <?= $msg_type ?>"><?= $message ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="title">Job Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Senior Software Engineer" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description *</label>
                    <textarea id="description" name="description" rows="5" placeholder="Describe the responsibilities and requirements..." required></textarea>
                </div>
                
                <div class="form-row" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="salary">Salary (Monthly)</label>
                        <input type="number" id="salary" name="salary" placeholder="e.g. 50000" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" placeholder="e.g. Dhaka" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="street">Street / Area</label>
                    <input type="text" id="street" name="street" placeholder="e.g. Banani">
                </div>
                
                <div class="form-actions">
                    <a href="employer_dashboard.php" class="btn btn-gray">Cancel</a>
                    <button type="submit" class="btn-green">Publish Job</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
