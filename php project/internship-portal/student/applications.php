
<?php
require __DIR__ . "/../config/auth.php";
requireRole("student");
require __DIR__ . "/../config/database.php";

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$studentId = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT a.status, a.applied_at, i.title, i.location,
            c.company_name
     FROM applications a
     JOIN internships i ON i.id = a.internship_id
     JOIN companies c ON c.id = i.company_id
     WHERE a.student_id = ?
     ORDER BY a.applied_at DESC"
);
$stmt->execute([$studentId]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Applications | LaunchPad</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar"><div class="container nav-inner">
    <a class="brand" href="../index.php">launch<span>pad.</span></a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="internships.php">Find internships</a>
        <a href="../logout.php">Log out</a>
    </div>
</div></nav>

<main class="container section">
    <span class="eyebrow">YOUR PROGRESS</span>
    <div class="section-heading"><h2>My applications</h2><a class="btn btn-primary" href="internships.php">Find more ↗</a></div>

    <?php if (!$applications): ?>
        <div class="empty-state"><h3>No applications yet</h3><p>When you apply for an internship, its progress will appear here.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Position</th><th>Company</th><th>Location</th><th>Date applied</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= h($app["title"]) ?></td>
                        <td><?= h($app["company_name"]) ?></td>
                        <td><?= h($app["location"] ?: "Flexible") ?></td>
                        <td><?= h(date("d M Y", strtotime($app["applied_at"]))) ?></td>
                        <td><span class="status"><?= h($app["status"]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>