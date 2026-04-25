<?php
session_start();
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not authenticated as admin, bounce back to login page
    header('Location: login_logOut_interface.html');
    exit;
}
include 'connection.php';

// Get total counts for the database overview
$stats = [];
$tables = ['job_seeker', 'employer', 'company', 'job_posting', 'application_tracking', 'interview'];
foreach ($tables as $table) {
    $res = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    $stats[$table] = $res->fetch_assoc()['total'];
}

// Get recent job seekers
$seekers = $conn->query("SELECT * FROM job_seeker ORDER BY job_seeker_id DESC LIMIT 5");
// Get recent companies
$companies = $conn->query("SELECT * FROM company ORDER BY company_id DESC LIMIT 5");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JobPortal Database</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 0; }
        .sidebar { width: 260px; background: #1e293b; height: 100vh; position: fixed; padding: 30px 20px; box-sizing: border-box; border-right: 1px solid #334155; }
        .main-content { margin-left: 260px; padding: 40px; }
        .logo { font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 50px; text-align: center; }
        .logo span { color: #38bdf8; }
        .nav-item { padding: 14px 20px; background: transparent; border-radius: 10px; margin-bottom: 10px; cursor: pointer; color: #94a3b8; font-weight: 500; transition: all 0.3s ease; }
        .nav-item:hover, .nav-item.active { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
        .nav-item.logout { background: transparent; border: 1px solid #ef4444; color: #ef4444; margin-top: auto; position: absolute; bottom: 30px; width: calc(100% - 40px); text-align: center; }
        .nav-item.logout:hover { background: #ef4444; color: white; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 32px; font-weight: 600; letter-spacing: -0.5px; }
        .user-info { background: #1e293b; padding: 12px 24px; border-radius: 30px; font-size: 14px; border: 1px solid #334155; display: flex; align-items: center; gap: 10px;}
        .user-avatar { width: 32px; height: 32px; background: linear-gradient(135deg, #38bdf8, #3b82f6); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: linear-gradient(145deg, #1e293b, #0f172a); padding: 30px; border-radius: 20px; border: 1px solid #334155; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, #38bdf8, #3b82f6); }
        .stat-value { font-size: 48px; font-weight: 700; color: #fff; margin-bottom: 8px; line-height: 1; }
        .stat-label { font-size: 15px; color: #94a3b8; font-weight: 500; }

        .tables-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media (max-width: 1200px) { .tables-grid { grid-template-columns: 1fr; } }
        
        .data-card { background: #1e293b; padding: 30px; border-radius: 20px; border: 1px solid #334155; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .data-card h3 { margin-top: 0; margin-bottom: 24px; font-size: 20px; color: #fff; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .data-card h3 span { font-size: 12px; padding: 4px 10px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-radius: 20px; font-weight: 500; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px 15px; text-align: left; font-size: 14px; }
        th { color: #94a3b8; font-weight: 500; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; border-bottom: 2px solid #334155; }
        td { color: #e2e8f0; border-bottom: 1px solid rgba(51, 65, 85, 0.5); }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: all 0.2s; }
        tbody tr:hover { background: rgba(51, 65, 85, 0.3); }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Job<span>Portal</span></div>
        <div class="nav-item active">Dashboard Overview</div>
        <div class="nav-item">Manage Users</div>
        <div class="nav-item">System Settings</div>
        <div class="nav-item">Database Logs</div>
        <a href="logout.php" style="text-decoration: none;"><div class="nav-item logout">Logout</div></a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Database System</h1>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <div><?= htmlspecialchars($_SESSION['user_name']) ?> <span style="color:#94a3b8; font-size:12px; margin-left: 5px;">(Admin)</span></div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['job_seeker'] ?></div>
                <div class="stat-label">Total Job Seekers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['employer'] ?></div>
                <div class="stat-label">Registered Employers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['company'] ?></div>
                <div class="stat-label">Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['job_posting'] ?></div>
                <div class="stat-label">Active Job Postings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['application_tracking'] ?></div>
                <div class="stat-label">Submitted Applications</div>
            </div>
        </div>
        
        <div class="tables-grid">
            <div class="data-card">
                <h3>Latest Job Seekers <span>View All</span></h3>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $seekers->fetch_assoc()) { ?>
                            <tr>
                                <td><span class="badge">#<?= $row['job_seeker_id'] ?></span></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                                <td style="color: #94a3b8;"><?= htmlspecialchars($row['email']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <div class="data-card">
                <h3>Recent Companies <span>View All</span></h3>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Company Name</th><th>Location</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $companies->fetch_assoc()) { ?>
                            <tr>
                                <td><span class="badge">#<?= $row['company_id'] ?></span></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                                <td style="color: #94a3b8;"><?= htmlspecialchars($row['city'] . ', ' . $row['street']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
