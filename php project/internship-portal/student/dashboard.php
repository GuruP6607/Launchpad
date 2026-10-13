
<?php
require __DIR__ . "/../config/auth.php";
requireRole("student");
require __DIR__ . "/../config/database.php";

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$studentId = (int) $stmt->fetchColumn();

if (!$studentId) {
    exit("Student profile not found. Please contact the administrator.");
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt->execute([$studentId]);
$totalApplications = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM applications
     WHERE student_id = ? AND status = 'Shortlisted'"
);
$stmt->execute([$studentId]);
$shortlisted = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE status = 'open'");
$stmt->execute();
$openInternships = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT a.status, a.applied_at, i.title, i.location, c.company_name
     FROM applications a
     JOIN internships i ON i.id = a.internship_id
     JOIN companies c ON c.id = i.company_id
     WHERE a.student_id = ?
     ORDER BY a.applied_at DESC LIMIT 5"
);
$stmt->execute([$studentId]);
$applications = $stmt->fetchAll();

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard | LaunchPad</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="../index.php">launch<span>pad.</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">My profile</a>
            <a href="internships.php">Find internships</a>
            <a href="applications.php">My applications</a>
            <a href="../logout.php">Log out</a>
        </div>
    </div>
</nav>

<header class="dashboard-header">
    <div class="container">
        <span class="eyebrow">STUDENT SPACE</span>
        <h1>Hey, <?= h($_SESSION["name"] ?? "Student") ?> 👋</h1>
        <p class="muted">Your next opportunity starts with one step.</p>
        <a class="btn btn-primary" href="internships.php">Explore internships ↗</a>
    </div>
</header>

<main class="container section">
    <div class="stats-grid">
        <article class="stat-card">
            <span class="muted">Open internships</span>
            <span class="stat-number"><?= $openInternships ?></span>
        </article>
        <article class="stat-card">
            <span class="muted">Applications sent</span>
            <span class="stat-number"><?= $totalApplications ?></span>
        </article>
        <article class="stat-card">
            <span class="muted">Shortlisted</span>
            <span class="stat-number"><?= $shortlisted ?></span>
        </article>
    </div>

    <div class="section-heading">
        <div>
            <span class="eyebrow">YOUR PROGRESS</span>
            <h2>Recent applications</h2>
        </div>
        <a class="text-link" href="applications.php">View all →</a>
    </div>

    <?php if (!$applications): ?>
        <div class="empty-state">
            <h3>Your journey starts here.</h3>
            <p>No applications yet. Find an internship that fits your interests.</p>
            <a class="btn btn-primary" href="internships.php">Find internships</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Position</th><th>Company</th><th>Applied</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= h($app["title"]) ?></td>
                        <td><?= h($app["company_name"]) ?></td>
                        <td><?= h(date("d M Y", strtotime($app["applied_at"]))) ?></td>
                        <td><span class="status"><?= h($app["status"]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<footer class="footer">LaunchPad · Your next chapter.</footer>
</body>
</html>