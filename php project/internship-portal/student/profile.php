
<?php
require __DIR__ . "/../config/auth.php";
requireRole("student");
require __DIR__ . "/../config/database.php";

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$stmt = $pdo->prepare(
    "SELECT u.name, u.email, s.phone, s.college, s.course, s.skills, s.resume
     FROM users u JOIN students s ON s.user_id = u.id
     WHERE u.id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$profile = $stmt->fetch();

if (!$profile) {
    exit("Student profile not found.");
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"] ?? "");
    $college = trim($_POST["college"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $resume = trim($_POST["resume"] ?? "");

    if (strlen($phone) > 20 || strlen($college) > 150 ||
        strlen($course) > 100 || strlen($resume) > 255 ||
        strlen($skills) > 10000) {
        $error = "One or more fields exceed the allowed length.";
    } elseif ($resume !== "" &&
        !preg_match('/^[a-zA-Z0-9_. -]+\.pdf$/i', $resume)) {
        $error = "Enter a PDF filename only, such as resume.pdf.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE students
             SET phone = ?, college = ?, course = ?, skills = ?, resume = ?
             WHERE user_id = ?"
        );
        $stmt->execute([
            $phone, $college, $course, $skills, $resume, $_SESSION["user_id"]
        ]);

        $profile["phone"] = $phone;
        $profile["college"] = $college;
        $profile["course"] = $course;
        $profile["skills"] = $skills;
        $profile["resume"] = $resume;
        $success = "Your profile has been updated.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Profile | LaunchPad</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar"><div class="container nav-inner">
    <a class="brand" href="../index.php">launch<span>pad.</span></a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="internships.php">Internships</a>
        <a href="applications.php">Applications</a>
        <a href="../logout.php">Log out</a>
    </div>
</div></nav>

<main class="container section">
    <section class="form-card" style="margin:auto">
        <span class="eyebrow">YOUR DETAILS</span>
        <h1>My profile</h1>
        <p class="muted"><?= h($profile["name"]) ?> · <?= h($profile["email"]) ?></p>

        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input class="form-control" id="phone" name="phone" maxlength="20"
                       value="<?= h($profile["phone"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label for="college">College</label>
                <input class="form-control" id="college" name="college" maxlength="150"
                       value="<?= h($profile["college"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label for="course">Course</label>
                <input class="form-control" id="course" name="course" maxlength="100"
                       value="<?= h($profile["course"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label for="skills">Skills</label>
                <textarea class="form-control" id="skills" name="skills"
                          maxlength="10000" placeholder="PHP, MySQL, HTML, CSS"><?= h($profile["skills"] ?? "") ?></textarea>
            </div>
            <div class="form-group">
                <label for="resume">Resume PDF filename (optional)</label>
                <input class="form-control" id="resume" name="resume" maxlength="255"
                       placeholder="resume.pdf" value="<?= h($profile["resume"] ?? "") ?>">
                <small class="muted">This field records a filename; it does not upload a resume.</small>
            </div>
            <button class="btn btn-primary full-width" type="submit">Save profile</button>
        </form>
    </section>
</main>
</body>
</html>