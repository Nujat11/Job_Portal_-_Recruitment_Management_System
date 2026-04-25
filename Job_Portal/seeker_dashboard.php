<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'job_seeker') {
    header("Location: login_logOut_interface.html#login");
    exit;
}

include 'connection.php';
$job_seeker_id = $_SESSION['user_id'];

// Get jobs applied by the seeker
$apps_sql = "
    SELECT a.application_id, a.status, a.apply_date, jp.title, c.name
    FROM application_tracking a
    JOIN job_posting jp ON a.job_id = jp.job_id
    JOIN employer e ON jp.employer_id = e.employer_id
    JOIN company c ON e.company_id = c.company_id
    WHERE a.job_seeker_id = ?
    ORDER BY a.application_id DESC
";
$stmt = $conn->prepare($apps_sql);
$stmt->bind_param("i", $job_seeker_id);
$stmt->execute();
$applications = $stmt->get_result();
$stmt->close();

// Get the seeker's current resumes
$resumes_sql = "SELECT file_path, upload_date FROM resume WHERE job_seeker_id = ? ORDER BY upload_date DESC LIMIT 1";
$r_stmt = $conn->prepare($resumes_sql);
$r_stmt->bind_param("i", $job_seeker_id);
$r_stmt->execute();
$resume_res = $r_stmt->get_result();
$has_resume = $resume_res->num_rows > 0;
$latest_resume = $has_resume ? $resume_res->fetch_assoc() : null;
$r_stmt->close();

// Get notifications
$notif_sql = "SELECT * FROM notification WHERE job_seeker_id = ? ORDER BY created_at DESC LIMIT 10";
$n_stmt = $conn->prepare($notif_sql);
$n_stmt->bind_param("i", $job_seeker_id);
$n_stmt->execute();
$notifications = $n_stmt->get_result();
$n_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seeker Dashboard | JobPortal BD</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
.card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.header-bg { background: linear-gradient(135deg, #0d0d0d, #203a43); color: white; padding: 40px 0; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-light py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="job_portal.php" class="navbar-brand"><b>JobPortal</b> BD</a>
    <div>
        <a href="job_portal.php" class="btn btn-outline-dark me-2">Browse Jobs</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>

<div class="header-bg">
    <div class="container">
        <h2>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
        <p>Manage your profile, upload your resume, and track your applications.</p>
    </div>
</div>

<div class="container my-5">
    <?php
    if(isset($_SESSION['flash_msg'])) {
        echo "<div class='alert alert-info'>" . $_SESSION['flash_msg'] . "</div>";
        unset($_SESSION['flash_msg']);
    }
    if(isset($_SESSION['flash_err'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['flash_err'] . "</div>";
        unset($_SESSION['flash_err']);
    }
    ?>

    <div class="row">
        <!-- Profile / Resume Setup -->
        <div class="col-md-4 mb-4">
            
            <!-- Notifications Card -->
            <div class="card p-4 mb-4">
                <h4 class="mb-3 d-flex justify-content-between align-items-center">
                    Notifications
                    <?php if($notifications->num_rows > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $notifications->num_rows ?></span>
                    <?php endif; ?>
                </h4>
                <div style="max-height: 250px; overflow-y: auto;">
                    <?php if ($notifications->num_rows > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php while($notif = $notifications->fetch_assoc()): ?>
                                <li class="list-group-item px-0" style="font-size: 14px;">
                                    <strong><span style="color: #10b981;">•</span></strong> <?= htmlspecialchars($notif['message']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($notif['created_at']) ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted small">No new notifications.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card p-4 h-100">
                <h4 class="mb-4">My Resume</h4>
                <?php if ($has_resume): ?>
                    <div class="alert alert-success">
                        <strong>✓ Active Resume Found</strong><br>
                        Uploaded on: <?= htmlspecialchars($latest_resume['upload_date']) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>No Resume Uploaded!</strong><br>
                        You must upload a resume before you can apply to any jobs.
                    </div>
                <?php endif; ?>

                <hr>
                <form action="upload_resume.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="resumeFile" class="form-label">Upload New Resume (PDF only)</label>
                        <input class="form-control" type="file" id="resumeFile" name="resume" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload Resume</button>
                </form>
            </div>
        </div>

        <!-- Applications -->
        <div class="col-md-8 mb-4">
            <div class="card p-4 h-100">
                <h4 class="mb-4">My Applications</h4>
                <?php if ($applications->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Applied On</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($app = $applications->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['title']) ?></td>
                                        <td><?= htmlspecialchars($app['name']) ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($app['apply_date'])) ?></td>
                                        <td>
                                            <?php 
                                            // Handle status colors
                                            $badge = 'bg-secondary';
                                            if($app['status'] == 'Shortlisted') $badge = 'bg-warning text-dark';
                                            if($app['status'] == 'Hired') $badge = 'bg-success';
                                            if($app['status'] == 'Rejected') $badge = 'bg-danger';
                                            if($app['status'] == 'Applied') $badge = 'bg-info';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($app['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You haven't applied to any jobs yet. Go browse the portal and start applying!</p>
                    <a href="job_portal.php" class="btn btn-outline-primary">Browse Jobs</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
