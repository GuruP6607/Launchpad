
<?php
require __DIR__ . "/../config/auth.php";
requireRole("student");
require __DIR__ . "/../config/database.php";

function h(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

$internshipId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$internshipId || $internshipId < 1) {
    http_response_code(400);
    exit("Invalid internship ID.");
}

$stmt = $pdo->prepare(
    "SELECT id FROM students WHERE user_id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$studentId = (int) $stmt->fetchColumn();

if (!$studentId) {
    exit("Student profile not found.");
}

$stmt = $pdo->prepare(
    "SELECT
        i.id,
        i.title,
        i.description,
        i.skills,
        i.location,
        i.deadline,
        c.company_name
     FROM internships i
     INNER JOIN companies c ON c.id = i.company_id
     WHERE i.id = ?
       AND i.status = 'open'
       AND (i.deadline IS NULL OR i.deadline >= CURDATE())"
);
$stmt->execute([$internshipId]);
$internship = $stmt->fetch();

if (!$internship) {
    http_response_code(404);
    exit("This internship is no longer available.");
}

$stmt = $pdo->prepare(
    "SELECT id FROM applications
     WHERE student_id = ? AND internship_id = ?"
);
$stmt->execute([$studentId, $internshipId]);
$alreadyApplied = (bool) $stmt->fetchColumn();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$alreadyApplied) {
    $coverLetter = trim($_POST["cover_letter"] ?? "");

    if (strlen($coverLetter) > 5000) {
        $error = "Your cover letter must be no longer than 5000 characters.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO applications
                    (student_id, internship_id, cover_letter)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $studentId,
                $internshipId,
                $coverLetter !== "" ? $coverLetter : null
            ]);

            $success = "Application submitted successfully.";
            $alreadyApplied = true;

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $alreadyApplied = true;
                $error = "You have already applied for this internship.";
            } else {
                error_log("Application submission failed: " . $e->getMessage());
                $error = "Unable to submit your application right now.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply | LaunchPad</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="../index.php">
            launch<span>pad.</span>
        </a>

        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="internships.php">Internships</a>
            <a href="applications.php">Applications</a>
            <a href="../logout.php">Log out</a>
        </div>
    </div>
</nav>

<main class="container section">
    <section class="form-card" style="margin:auto">
        <span class="eyebrow">INTERNSHIP APPLICATION</span>

        <h1><?= h($internship["title"]) ?></h1>

        <p class="muted">
            <?= h($internship["company_name"]) ?>
            ·
            <?= h($internship["location"] ?: "Location flexible") ?>
        </p>

        <p><?= nl2br(h($internship["description"])) ?></p>

        <p class="muted">
            Required skills: <?= h($internship["skills"] ?: "Not specified") ?>
        </p>

        <p class="muted">
            Deadline: <?= h($internship["deadline"] ?: "Not specified") ?>
        </p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <?php if ($alreadyApplied): ?>

            <p>You have already applied for this internship.</p>

            <a class="btn btn-primary" href="applications.php">
                Track application
            </a>

        <?php else: ?>

            <form method="POST"
                  action="apply.php?id=<?= (int) $internshipId ?>">

                <div class="form-group">
                    <label for="cover_letter">Cover letter (optional)</label>

                    <textarea
                        class="form-control"
                        id="cover_letter"
                        name="cover_letter"
                        maxlength="5000"
                        placeholder="Tell the company why you are interested..."><?= h($_POST["cover_letter"] ?? "") ?></textarea>
                </div>

                <button class="btn btn-primary full-width" type="submit">
                    Submit application ↗
                </button>
            </form>

        <?php endif; ?>
    </section>
</main>

<footer class="footer">
    LaunchPad · Your next chapter.
</footer>

</body>
</html>