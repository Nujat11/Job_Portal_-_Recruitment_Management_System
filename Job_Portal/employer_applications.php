<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'employer') {
    header('Location: login_logOut_interface.html');
    exit;
}
include 'connection.php';

$employer_id = $_SESSION['user_id'];
$filter_job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

$query = "SELECT a.application_id, a.apply_date, a.status, s.name, s.email, s.phone, j.title as job_title, r.file_path 
          FROM application_tracking a 
          JOIN job_seeker s ON a.job_seeker_id = s.job_seeker_id 
          JOIN job_posting j ON a.job_id = j.job_id 
          LEFT JOIN resume r ON a.resume_id = r.resume_id 
          WHERE j.employer_id = ?";

if ($filter_job_id > 0) {
    $query .= " AND j.job_id = ?";
    $stmt = $conn->prepare($query . " ORDER BY a.apply_date DESC");
    $stmt->bind_param("ii", $employer_id, $filter_job_id);
} else {
    $stmt = $conn->prepare($query . " ORDER BY a.apply_date DESC");
    $stmt->bind_param("i", $employer_id);
}

$stmt->execute();
$applications = $stmt->get_result();
$statuses = ['Applied', 'Shortlisted', 'Rejected', 'Hired'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Tracking - JobPortal</title>
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
        
        .container { max-width: 1200px; margin: -20px auto 40px; padding: 0 20px; position: relative; z-index: 10; }
        
        .table-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 13px; color: #0f172a; font-weight: 600; padding: 15px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        td { font-size: 14px; color: #334155; padding: 15px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top;}
        tr:last-child td { border-bottom: none; }
        
        .applicant-name { font-weight: 600; color: #0f172a; margin-bottom: 4px; display: block;}
        .applicant-meta { font-size: 12px; color: #64748b; margin-bottom: 2px;}
        
        .btn-teal-outline { border: 1px solid #2dd4bf; color: #14b8a6; padding: 6px 12px; border-radius: 4px; font-size: 12px; text-decoration: none; display: inline-block;}
        .btn-teal-outline:hover { background: #2dd4bf; color: white; }
        
        .status-actions { display: flex; gap: 10px; align-items: center; }
        .status-select { padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 13px; outline: none;}
        .btn-save { background: #2563eb; color: white; padding: 6px 15px; border-radius: 4px; font-size: 12px; font-weight: 500; border: none; cursor: pointer;}
        .btn-save:hover { background: #1d4ed8; }

        .toast { position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 1000; }
        .toast.show { opacity: 1; }
        .toast.error { background: #ef4444; }
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
        <h1>Applicant Tracking <?= $filter_job_id ? "- Ordered by Title" : "" ?></h1>
    </div>
    
    <div class="container">
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th width="25%">Applicant</th>
                        <th width="25%">Job Applied For</th>
                        <th width="15%">Date</th>
                        <th width="15%">Resume</th>
                        <th width="20%">Status Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($applications->num_rows > 0): ?>
                        <?php while ($row = $applications->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="applicant-name"><?= htmlspecialchars($row['name']) ?></span>
                                    <div class="applicant-meta">Email: <?= htmlspecialchars($row['email']) ?></div>
                                    <div class="applicant-meta">Phone: <?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['job_title']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($row['apply_date'])) ?></td>
                                <td>
                                    <?php if ($row['file_path']): 
                                        $display_path = ltrim($row['file_path'], '/');
                                    ?>
                                        <a href="<?= htmlspecialchars($display_path) ?>" target="_blank" class="btn-teal-outline">View Resume</a>
                                    <?php else: ?>
                                        <span class="applicant-meta">No Resume</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="status-actions">
                                        <select class="status-select" id="status-<?= $row['application_id'] ?>">
                                            <?php foreach ($statuses as $status): ?>
                                                <option value="<?= $status ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-save" onclick="updateStatus(<?= $row['application_id'] ?>)">Save</button>
                                        <?php if ($row['status'] === 'Shortlisted'): ?>
                                            <button class="btn-save" style="background:#10b981; margin-left: 5px;" onclick="scheduleInterview(<?= $row['application_id'] ?>)">Schedule Interview</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px;">No applications found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="toast" class="toast">Status updated successfully</div>

    <script>
        function updateStatus(applicationId) {
            const selectEl = document.getElementById('status-' + applicationId);
            const status = selectEl.value;
            const btn = selectEl.nextElementSibling;
            
            btn.innerHTML = '...';
            btn.disabled = true;

            fetch('update_application_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = 'Save';
                btn.disabled = false;
                
                const toast = document.getElementById('toast');
                toast.textContent = data.message;
                if (data.success) {
                    toast.className = 'toast show';
                } else {
                    toast.className = 'toast error show';
                }
                
                setTimeout(() => {
                    toast.className = 'toast';
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerHTML = 'Save';
                btn.disabled = false;
                const toast = document.getElementById('toast');
                toast.textContent = 'Network Error';
                toast.className = 'toast error show';
                setTimeout(() => {
                    toast.className = 'toast';
                }, 3000);
            });
        }

        function scheduleInterview(applicationId) {
            const interviewDate = prompt("Enter interview date/time (e.g. 2026-05-10 10:00 AM):");
            if (!interviewDate) return;

            fetch('schedule_interview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    interview_date: interviewDate,
                    interview_type: 'Online' // Default to Online for simplicity
                })
            })
            .then(response => response.json())
            .then(data => {
                const toast = document.getElementById('toast');
                toast.textContent = data.message;
                if (data.success) {
                    toast.className = 'toast show';
                } else {
                    toast.className = 'toast error show';
                }
                setTimeout(() => {
                    toast.className = 'toast';
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Network Error");
            });
        }
    </script>
</body>
</html>
