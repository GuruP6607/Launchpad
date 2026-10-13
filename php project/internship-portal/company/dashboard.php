
<?php
require __DIR__ . "/../config/auth.php";
requireRole("company");
require __DIR__ . "/../config/database.php";

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$companyId = (int) $stmt->fetchColumn();

if (!$companyId) {
    exit("Company profile not found.");
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ?");
$stmt->execute([$companyId]);
$totalInternships = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ? AND status = 'open'");
$stmt->execute([$companyId]);
$openInternships = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM applications a
     JOIN internships i ON i.id = a.internship_id
     WHERE i.company_id = ?"
);
$stmt->execute([$companyId]);
$totalApplicants = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT i.id, i.title, i.status, COUNT(a.id) AS applicant_count
     FROM internships i
     LEFT JOIN applications a ON a.internship_id = i.id
     WHERE i.company_id = ?
     GROUP BY i.id, i.title, i.status, i.created_at
     ORDER BY i.created_at DESC LIMIT 5"
);
$stmt->execute([$companyId]);
$internships = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Company Dashboard | LaunchPad</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar"><div class="container nav-inner">
    <a class="brand" href="../index.php">launch<span>pad.</span></a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_internship.php">Post internship</a>
        <a href="internships.php">My listings</a>
        <a href="applicants.php">Applicants</a>
        <a href="../logout.php">Log out</a>
    </div>
</div></nav>

<header class="dashboard-header"><div class="container">
    <span class="eyebrow">COMPANY SPACE</span>
    <h1>Welcome, <?= h($_SESSION["name"] ?? "Company") ?>.</h1>
    <p class="muted">Find talented people and manage your opportunities.</p>
    <a class="btn btn-primary" href="add_internship.php">+ Post an internship</a>
</div></header>

<main class="container section">
    <div class="stats-grid">
        <article class="stat-card"><span class="muted">Total listings</span><span class="stat-number"><?= $totalInternships ?></span></article>
        <article class="stat-card"><span class="muted">Open listings</span><span class="stat-number"><?= $openInternships ?></span></article>
        <article class="stat-card"><span class="muted">Applications received</span><span class="stat-number"><?= $totalApplicants ?></span></article>
    </div>

    <div class="section-heading">
        <h2>Recent listings</h2>
        <a class="text-link" href="internships.php">Manage listings →</a>
    </div>

    <?php if (!$internships): ?>
        <div class="empty-state"><h3>Ready to meet your next intern?</h3><p>Create your first internship listing.</p><a class="btn btn-primary" href="add_internship.php">Post an internship</a></div>
    <?php else: ?>
        <div class="table-wrap"><table>
            <thead><tr><th>Position</th><th>Status</th><th>Applicants</th><th>Action</th></tr></thead>
            <tbody><?php foreach ($internships as $item): ?>
                <tr>
                    <td><?= h($item["title"]) ?></td>
                    <td><span class="status"><?= h(ucfirst($item["status"])) ?></span></td>
                    <td><?= (int) $item["applicant_count"] ?></td>
                    <td><a class="text-link" href="applicants.php?internship_id=<?= (int) $item["id"] ?>">View applicants →</a></td>
                </tr>
            <?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</main>
</body>
</html>