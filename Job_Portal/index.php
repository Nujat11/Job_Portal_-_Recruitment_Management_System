<?php
session_start();
include 'connection.php';

// Fetch dynamic stats from database
$jobs_count = $conn->query("SELECT count(*) as c FROM job_posting")->fetch_assoc()['c'];
$companies_count = $conn->query("SELECT count(*) as c FROM company")->fetch_assoc()['c'];
$seekers_count = $conn->query("SELECT count(*) as c FROM job_seeker")->fetch_assoc()['c'];

// Enhance Search Feature
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
if (!empty($search)) {
    $searchTerm = $conn->real_escape_string($search);
    $whereClause = "WHERE jp.title LIKE '%$searchTerm%' OR jp.description LIKE '%$searchTerm%' OR c.name LIKE '%$searchTerm%'";
}

// Fetch active job postings with their related companies dynamically
$sql = "SELECT jp.job_id, jp.title, jp.salary, jp.city, jp.street, c.name 
        FROM job_posting jp 
        JOIN employer e ON jp.employer_id = e.employer_id
        JOIN company c ON e.company_id = c.company_id
        $whereClause
        ORDER BY jp.job_id DESC";

$jobs = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JobPortal BD - Dynamic Landing</title>

<!-- Bootstrap for rapid scalable UI styling -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background-color: #f8f9fa;
}

/* HERO SECTION */
.hero {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: white;
    padding: 100px 0;
    text-align: center;
}
.hero span { color: #38bdf8; }

.search-box {
    max-width: 650px;
    margin: 30px auto;
}
.search-box input:focus {
    box-shadow: none;
}

/* CATEGORY */
.category-box {
    padding: 24px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    background: #fff;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.category-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}

/* JOB CARD */
.job-card {
    border: 1px solid #e2e8f0;
    padding: 24px;
    border-radius: 16px;
    background: #fff;
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.job-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}

.salary {
    background: #e0f2fe;
    color: #0284c7;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 15px;
}

.job-card h5 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
    font-size: 1.1rem;
}
.job-card p {
    color: #64748b;
    font-size: 14px;
    flex-grow: 1;
}

/* CTA */
.cta {
    background: linear-gradient(45deg, #0284c7, #38bdf8);
    color: white;
    padding: 60px;
    text-align: center;
    border-radius: 20px;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="navbar-brand m-0 p-0" style="text-decoration:none; font-size: 24px; font-weight:700; letter-spacing:-0.5px; color:#0f2027;">JobPortal <span style="color:#0284c7;">BD</span></a>
    <div>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <?php 
                $dashLink = "login_logOut_interface.html"; // default fallback
                if($_SESSION['role'] === 'admin') $dashLink = 'admin_dashboard.php';
                if($_SESSION['role'] === 'employer') $dashLink = 'employer_dashboard.php';
                if($_SESSION['role'] === 'job_seeker') $dashLink = 'seeker_dashboard.php'; 
            ?>
            <a href="<?= $dashLink ?>" class="btn btn-primary fw-semibold rounded-pill px-4 me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-light border text-danger fw-semibold rounded-pill px-4">Logout</a>
        <?php else: ?>
            <a href="login_logOut_interface.html" class="btn btn-outline-dark fw-semibold rounded-pill px-4 me-2">Login</a>
            <a href="login_logOut_interface.html" class="btn btn-primary fw-semibold rounded-pill px-4">Sign Up</a>
        <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1 class="display-4 fw-bold" style="letter-spacing:-1px;">Find your next <span>dream</span> career opportunity</h1>
        <p class="lead mb-4" style="color:rgba(255,255,255,0.8);">Connect with top companies across Bangladesh dynamically.</p>

        <form method="GET" action="index.php">
            <div class="search-box input-group input-group-lg bg-white rounded-pill overflow-hidden shadow-lg p-1">
                <input type="text" name="search" class="form-control border-0 px-4 bg-transparent" placeholder="Search for jobs or companies..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Search</button>
            </div>
        </form>

        <div class="d-flex justify-content-center gap-5 mt-5">
            <div><h3 class="fw-bold mb-0 text-white"><?= $jobs_count ?>+</h3><small style="color:rgba(255,255,255,0.6);">Active Jobs</small></div>
            <div><h3 class="fw-bold mb-0 text-white"><?= $companies_count ?></h3><small style="color:rgba(255,255,255,0.6);">Companies</small></div>
            <div><h3 class="fw-bold mb-0 text-white"><?= $seekers_count ?>+</h3><small style="color:rgba(255,255,255,0.6);">Candidates</small></div>
        </div>
    </div>
</section>

<!-- JOB LIST (DYNAMIC) -->
<section class="container my-5 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0" style="color: #1e293b;"><?= !empty($search) ? 'Search Results' : 'Featured Jobs' ?></h3>
        <?php if(!empty($search)) echo '<a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Clear Search</a>'; ?>
    </div>
    
    <div class="row g-4 mt-1">
        <?php if ($jobs->num_rows > 0): ?>
            <?php while($job = $jobs->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="job-card">
                        <div>
                            <span class="salary">৳<?= number_format($job['salary']) ?>/mo</span>
                        </div>
                        <h5><?= htmlspecialchars($job['title']) ?></h5>
                        <p class="mb-4 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#94a3b8" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022zM6 8.694 1 10.36V15h5V8.694zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15z"/><path d="M2 11h1v1H2v-1zm2 0h1v1H4v-1zm-2 2h1v1H2v-1zm2 0h1v1H4v-1zm4-4h1v1H8V9zm2 0h1v1h-1V9zm-2 2h1v1H8v-1zm2 0h1v1h-1v-1zm2-2h1v1h-1V9zm0 2h1v1h-1v-1zM8 7h1v1H8V7zm2 0h1v1h-1V7zm2 0h1v1h-1V7zM8 5h1v1H8V5zm2 0h1v1h-1V5zm2 0h1v1h-1V5zm0-2h1v1h-1V3z"/></svg>
                            <?= htmlspecialchars($job['name']) ?> - <?= htmlspecialchars($job['city']) ?>, <?= htmlspecialchars($job['street']) ?>
                        </p>
                        
                        <!-- Apply Form directly tied to database action -->
                        <form action="apply.php" method="POST">
                            <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                            <button type="submit" class="btn btn-outline-dark w-100 fw-semibold rounded-pill py-2">Apply Now</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <h4>No jobs match your search criteria.</h4>
                <p>Try searching for Software, Networking, or Design.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="container my-5">
    <div class="cta shadow-lg">
        <h2 class="fw-bold mb-4">Take the next step in your career</h2>
        <?php if(!isset($_SESSION['logged_in'])): ?>
            <a href="login_logOut_interface.html" class="btn btn-light btn-lg px-5 fw-bold text-primary rounded-pill shadow">Sign Up Free</a>
        <?php else: ?>
            <a href="seeker_dashboard.php" class="btn btn-light btn-lg px-5 fw-bold text-primary rounded-pill shadow">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</section>

</body>
</html>
