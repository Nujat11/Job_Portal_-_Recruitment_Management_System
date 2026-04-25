<?php
session_start();
include 'connection.php';

// Fetch dynamic stats from database
$jobs_count = $conn->query("SELECT count(*) as c FROM job_posting")->fetch_assoc()['c'];
$companies_count = $conn->query("SELECT count(*) as c FROM company")->fetch_assoc()['c'];
$seekers_count = $conn->query("SELECT count(*) as c FROM job_seeker")->fetch_assoc()['c'];

// Search Logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
if (!empty($search)) {
    $searchTerm = $conn->real_escape_string($search);
    $whereClause = "WHERE jp.title LIKE '%$searchTerm%' OR c.name LIKE '%$searchTerm%'";
}

// Fetch active job postings with their related companies dynamically
$sql = "SELECT jp.job_id, jp.title, jp.salary, jp.city, jp.street, c.name 
        FROM job_posting jp 
        JOIN employer e ON jp.employer_id = e.employer_id
        JOIN company c ON e.company_id = c.company_id
        $whereClause
        ORDER BY jp.job_id DESC
        LIMIT 6";

$jobs = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JobPortal BD</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
}

/* HERO SECTION */
.hero {
    background: #0d0d0d;
    color: white;
    padding: 80px 0;
    text-align: center;
}

.hero span {
    color: orange;
}

.search-box {
    max-width: 600px;
    margin: 20px auto;
}

/* CATEGORY */
.category-box {
    padding: 20px;
    border: 1px solid #eee;
    border-radius: 10px;
    text-align: center;
}

/* JOB CARD */
.job-card {
    border: 1px solid #eee;
    padding: 15px;
    border-radius: 10px;
    background: #fff;
    height: 100%;
}

.salary {
    background: #d4f4e2;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

/* CTA */
.cta {
    background: #c84d2c;
    color: white;
    padding: 50px;
    text-align: center;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-light py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="job_portal.php" class="navbar-brand" style="text-decoration:none;"><b>JobPortal</b> BD</a>
    <div>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <?php 
                $dashLink = "login_logOut_interface.html"; 
                if($_SESSION['role'] === 'admin') $dashLink = 'admin_dashboard.php';
                if($_SESSION['role'] === 'employer') $dashLink = 'employer_dashboard.php';
                if($_SESSION['role'] === 'job_seeker') $dashLink = 'seeker_dashboard.php';
            ?>
            <a href="<?= $dashLink ?>" class="btn btn-outline-dark me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        <?php else: ?>
            <a href="login_logOut_interface.html#login" class="btn btn-outline-dark me-2">Login</a>
            <a href="login_logOut_interface.html#register" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
    <h1>Find your next <span>dream</span> career opportunity</h1>
    <p>Connect with top companies across Bangladesh</p>

    <form action="job_portal.php" method="GET" class="search-box input-group">
        <input type="text" name="search" class="form-control" placeholder="Search jobs" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-warning">Search</button>
    </form>

    <div class="d-flex justify-content-center gap-5 mt-4">
        <div><?= $jobs_count ?>+ Jobs</div>
        <div><?= $companies_count ?> Companies</div>
        <div><?= $seekers_count ?> Candidates</div>
    </div>
</section>

<!-- CATEGORY -->
<section class="container my-5">
    <h3>Explore categories</h3>
    <div class="row mt-3">
        <div class="col-md-3"><div class="category-box">Software Dev</div></div>
        <div class="col-md-3"><div class="category-box">Networking</div></div>
        <div class="col-md-3"><div class="category-box">Design</div></div>
        <div class="col-md-3"><div class="category-box">QA Testing</div></div>
    </div>
</section>

<!-- JOB LIST -->
<section class="container my-5">
    <h3>Featured Jobs</h3>
    <div class="row mt-3">
        <?php if ($jobs->num_rows > 0): ?>
            <?php while($job = $jobs->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="job-card">
                        <span class="salary">৳<?= number_format($job['salary']) ?>/mo</span>
                        <h5><?= htmlspecialchars($job['title']) ?></h5>
                        <p><?= htmlspecialchars($job['name']) ?> - <?= htmlspecialchars($job['city']) ?>, <?= htmlspecialchars($job['street']) ?></p>
                        <form action="apply.php" method="POST" style="display:inline;">
                            <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                            <button type="submit" class="btn btn-outline-dark btn-sm">Apply</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No featured jobs available.</p>
        <?php endif; ?>
    </div>
</section>

<!-- WHY -->
<section class="container my-5">
    <h3>Why Choose Us</h3>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="category-box">
                <h4>01</h4>
                Smart matching
            </div>
        </div>
        <div class="col-md-4">
            <div class="category-box">
                <h4>02</h4>
                One-click apply
            </div>
        </div>
        <div class="col-md-4">
            <div class="category-box">
                <h4>03</h4>
                Verified employers
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <h2>Ready to find your next role?</h2>
    <?php if(!isset($_SESSION['logged_in'])): ?>
        <a href="login_logOut_interface.html#register" class="btn btn-light btn-lg mt-3">Browse Jobs - Sign Up Today</a>
    <?php else: ?>
        <a href="job_portal.php" class="btn btn-light btn-lg mt-3">Browse More Jobs</a>
    <?php endif; ?>
</section>

</body>
</html>
