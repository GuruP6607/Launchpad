
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

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");

    if ($title === "" || strlen($title) > 150) {
        $error = "Enter a title (up to 150 characters).";
    } elseif ($description === "" || strlen($description) > 10000) {
        $error = "Enter a description under 10000 characters.";
    } elseif (strlen($skills) > 255 || strlen($location) > 150) {
        $error = "Skills or location exceeds the allowed length.";
    } elseif ($deadline !== "" &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) ||
         !checkdate((int)substr($deadline, 5, 2), (int)substr($deadline, 8, 2), (int)substr($deadline, 0, 4)) ||
         $deadline < date("Y-m-d"))) {
        $error = "Please choose a valid deadline today or later.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO internships
             (company_id, title, description, skills, location, deadline, status)
             VALUES (?, ?, ?, ?, ?, ?, 'open')"
        );
        $stmt->execute([
            $companyId,
            $title,
            $description,
            $skills ?: null,
            $location ?: null,
            $deadline ?: null
        ]);

        header("Location: internships.php?created=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Post Internship | LaunchPad</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar"><div class="container nav-inner">
    <a class="brand" href="../index.php">launch<span>pad.</span></a>
    <div class="nav-links"><a href="dashboard.php">Dashboard</a><a href="internships.php">My listings</a><a href="applicants.php">Applicants</a></div>
</div></nav>

<main class="container section">
    <section class="form-card" style="margin:auto">
        <span class="eyebrow">NEW OPPORTUNITY</span>
        <h1>Post an internship</h1>
        <p class="muted">Share the role, requirements, and deadline with students.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

        <form method="POST" action="add_internship.php">
            <div class="form-group"><label for="title">Internship title *</label>
                <input class="form-control" id="title" name="title" maxlength="150" required value="<?= h($_POST["title"] ?? "") ?>">
            </div>
            <div class="form-group"><label for="description">Description *</label>
                <textarea class="form-control" id="description" name="description" maxlength="10000" required><?= h($_POST["description"] ?? "") ?></textarea>
            </div>
            <div class="form-group"><label for="skills">Required skills</label>
                <input class="form-control" id="skills" name="skills" maxlength="255" placeholder="PHP, MySQL, HTML" value="<?= h($_POST["skills"] ?? "") ?>">
            </div>
            <div class="form-group"><label for="location">Location</label>
                <input class="form-control" id="location" name="location" maxlength="150" placeholder="Remote / Chennai" value="<?= h($_POST["location"] ?? "") ?>">
            </div>
            <div class="form-group"><label for="deadline">Application deadline</label>
                <input class="form-control" type="date" id="deadline" name="deadline" min="<?= date("Y-m-d") ?>" value="<?= h($_POST["deadline"] ?? "") ?>">
            </div>
            <button class="btn btn-primary full-width" type="submit">Publish internship ↗</button>
        </form>
    </section>
</main>
</body>
</html>