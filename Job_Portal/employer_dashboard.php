<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employer') {
    header('Location: login_logOut_interface.html');
    exit;
}
include 'connection.php';

$employer_id = $_SESSION['user_id'];
$employer_name = $_SESSION['user_name'];

// Count Active Job Postings
$job_stmt = $conn->prepare("SELECT COUNT(*) as total FROM job_posting WHERE employer_id = ?");
$job_stmt->bind_param("i", $employer_id);
$job_stmt->execute();
$job_res = $job_stmt->get_result();
$active_jobs = $job_res->fetch_assoc()['total'];
$job_stmt->close();

// Count Applications Received
$app_stmt = $conn->prepare("SELECT COUNT(*) as total FROM application_tracking a JOIN job_posting j ON a.job_id = j.job_id WHERE j.employer_id = ?");
$app_stmt->bind_param("i", $employer_id);
$app_stmt->execute();
$app_res = $app_stmt->get_result();
$total_apps = $app_res->fetch_assoc()['total'];
$app_stmt->close();

// Recent Job Postings
$recent_jobs_stmt = $conn->prepare("SELECT job_id, title, city, salary FROM job_posting WHERE employer_id = ? ORDER BY job_id DESC LIMIT 5");
$recent_jobs_stmt->bind_param("i", $employer_id);
$recent_jobs_stmt->execute();
$recent_jobs = $recent_jobs_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - JobPortal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 0; }
        .navbar { background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-size: 20px; font-weight: 700; color: #0f172a; }
        .logo-text { font-weight: 400; color: #475569; font-size: 16px; margin-left: 5px; }
        .nav-buttons { display: flex; gap: 15px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; border: 1px solid transparent; background: transparent; }
        .btn-green-outline { border-color: #10b981; color: #10b981; }
        .btn-green-outline:hover { background: #10b981; color: white; }
        .btn-blue-outline { border-color: #3b82f6; color: #3b82f6; }
        .btn-blue-outline:hover { background: #3b82f6; color: white; }
        .btn-red { background: #ef4444; color: white; }
        .btn-red:hover { background: #dc2626; }
        
        .welcome-banner { background: linear-gradient(90deg, #375b48 0%, #87cca2 100%); color: #0f172a; padding: 50px 40px; }
        .welcome-banner h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 600; }
        .welcome-banner p { margin: 0; font-size: 15px; color: #1e293b; color: rgba(15, 23, 42, 0.7); }
        
        .container { max-width: 1100px; margin: -20px auto 40px; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; position: relative; z-index: 10; }
        
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .stat-card { background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .stat-value { font-size: 48px; font-weight: 600; color: #0f172a; margin-bottom: 5px; line-height: 1; }
        .stat-label { font-size: 14px; color: #64748b; margin-bottom: 20px; }
        .btn-primary-solid { background: #10b981; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: 500; }
        .btn-blue-solid { background: #2563eb; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: 500; }

        .table-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table-card h2 { margin: 0 0 20px 0; font-size: 20px; color: #1e293b; font-weight: 600; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 13px; color: #0f172a; font-weight: 600; padding: 15px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        td { font-size: 14px; color: #475569; padding: 15px 10px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        
        .btn-teal-outline { border: 1px solid #2dd4bf; color: #14b8a6; padding: 6px 12px; border-radius: 4px; font-size: 12px; text-decoration: none; }
        .btn-teal-outline:hover { background: #2dd4bf; color: white; }
        .btn-red-outline { border: 1px solid #ef4444; color: #ef4444; padding: 6px 12px; border-radius: 4px; font-size: 12px; text-decoration: none; margin-left: 5px; }
        .btn-red-outline:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">JobPortal <span class="logo-text">BD - Employer</span></div>
        <div class="nav-buttons">
            <a href="employer_post_job.php" class="btn btn-green-outline">Post New Job</a>
            <a href="employer_applications.php" class="btn btn-blue-outline">Applications</a>
            <a href="logout.php" class="btn btn-red">Logout</a>
        </div>
    </div>
    
    <div class="welcome-banner">
        <h1>Welcome back, <?= htmlspecialchars($employer_name) ?>!</h1>
        <p>Manage your company's job postings and review applications.</p>
    </div>
    
    <div class="container">
        <!-- Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $active_jobs ?></div>
                <div class="stat-label">Active Job Postings</div>
                <a href="employer_post_job.php" class="btn-primary-solid">Post Another Job</a>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_apps ?></div>
                <div class="stat-label">Applications Received</div>
                <a href="employer_applications.php" class="btn-blue-solid">Review Applications</a>
            </div>
        </div>
        
        <div class="table-card">
            <h2>Your Recent Postings</h2>
            <table>
                <thead>
                    <tr>
                        <th width="10%">Job ID</th>
                        <th width="35%">Job Title</th>
                        <th width="20%">Location</th>
                        <th width="15%">Salary</th>
                        <th width="20%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_jobs->num_rows > 0): ?>
                        <?php while ($row = $recent_jobs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['job_id'] ?></td>
                                <td style="color: #334155;"><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['city']) ?></td>
                                <td><?= number_format($row['salary'], 2) ?></td>
                                <td>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <a href="employer_applications.php?job_id=<?= $row['job_id'] ?>" class="btn-teal-outline">View Applicants</a>
                                        <a href="delete_job.php?job_id=<?= $row['job_id'] ?>" onclick="return confirm('Are you sure you want to delete this job posting? This will also remove all associated applications and interviews.');" class="btn-red-outline">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center;">No postings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
