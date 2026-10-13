
<?php
require __DIR__ . "/../config/auth.php";
requireRole("company");
require __DIR__ . "/../config/database.php";

function h(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$companyId = (int) $stmt->fetchColumn();

if (!$companyId) {
    exit("Company profile not found.");
}

$allowedStatuses = [
    "Applied",
    "Under Review",
    "Shortlisted",
    "Interview",
    "Selected",
    "Rejected"
];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $applicationId = filter_input(
        INPUT_POST,
        "application_id",
        FILTER_VALIDATE_INT
    );

    $newStatus = $_POST["status"] ?? "";

    if (
        !$applicationId ||
        !in_array($newStatus, $allowedStatuses, true)
    ) {
        $error = "Invalid application update.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE applications a
             INNER JOIN internships i ON i.id = a.internship_id
             SET a.status = ?
             WHERE a.id = ? AND i.company_id = ?"
        );

        $stmt->execute([
            $newStatus,
            $applicationId,
            $companyId
        ]);

        $check = $pdo->prepare(
            "SELECT a.id
             FROM applications a
             INNER JOIN internships i ON i.id = a.internship_id
             WHERE a.id = ? AND i.company_id = ?"
        );

        $check->execute([$applicationId, $companyId]);

        if (!$check->fetchColumn()) {
            $error = "Application not found.";
        }
    }
}

$filterId = filter_input(
    INPUT_GET,
    "internship_id",
    FILTER_VALIDATE_INT
);

$sql = "SELECT
            a.id AS application_id,
            a.status,
            a.applied_at,
            a.cover_letter,
            u.name,
            u.email,
            s.phone,
            s.college,
            s.course,
            s.skills,
            i.id AS internship_id,
            i.title
        FROM applications a
        INNER JOIN students s ON s.id = a.student_id
        INNER JOIN users u ON u.id = s.user_id
        INNER JOIN internships i ON i.id = a.internship_id
        WHERE i.company_id = ?";

$params = [$companyId];

if ($filterId && $filterId > 0) {
    $sql .= " AND i.id = ?";
    $params[] = $filterId;
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applicants = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT id, title
     FROM internships
     WHERE company_id = ?
     ORDER BY title"
);
$stmt->execute([$companyId]);
$companyInternships = $stmt->fetchAll();

$filterQuery = ($filterId && $filterId > 0)
    ? "?internship_id=" . (int) $filterId
    : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Applicants | LaunchPad</title>
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
            <a href="add_internship.php">Post internship</a>
            <a href="internships.php">My listings</a>
            <a href="applicants.php">Applicants</a>
            <a href="../logout.php">Log out</a>
        </div>
    </div>
</nav>

<main class="container section">
    <span class="eyebrow">TALENT PIPELINE</span>

    <div class="section-heading">
        <h2>Applicants</h2>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <form method="GET" action="applicants.php"
          class="inline-form" style="width:100%; margin-bottom:26px">

        <select class="form-control" name="internship_id">
            <option value="">All internships</option>

            <?php foreach ($companyInternships as $item): ?>
                <option
                    value="<?= (int) $item["id"] ?>"
                    <?= $filterId === (int) $item["id"] ? "selected" : "" ?>>
                    <?= h($item["title"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-primary" type="submit">
            Filter
        </button>

        <a class="btn btn-secondary" href="applicants.php">
            Clear
        </a>
    </form>

    <?php if (!$applicants): ?>

        <div class="empty-state">
            <h3>No applicants yet</h3>
            <p>
                Applications will appear here when students apply
                to your internship listings.
            </p>
        </div>

    <?php else: ?>

        <div class="grid">
            <?php foreach ($applicants as $app): ?>

                <article class="card">
                    <span class="eyebrow">
                        <?= h($app["title"]) ?>
                    </span>

                    <h3><?= h($app["name"]) ?></h3>

                    <p><?= h($app["email"]) ?></p>

                    <p class="muted">
                        <?= h($app["college"] ?: "College not provided") ?>
                        ·
                        <?= h($app["course"] ?: "Course not provided") ?>
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        <?= h($app["phone"] ?: "Not provided") ?>
                    </p>

                    <p>
                        <strong>Skills:</strong>
                        <?= h($app["skills"] ?: "Not provided") ?>
                    </p>

                    <p><strong>Cover letter</strong></p>

                    <p>
                        <?= nl2br(h(
                            $app["cover_letter"] ?: "No cover letter provided."
                        )) ?>
                    </p>

                    <p class="muted">
                        Applied:
                        <?= h(date(
                            "d M Y",
                            strtotime($app["applied_at"])
                        )) ?>
                    </p>

                    <p>
                        Current status:
                        <span class="status">
                            <?= h($app["status"]) ?>
                        </span>
                    </p>

                    <form method="POST"
                          action="applicants.php<?= h($filterQuery) ?>">

                        <input type="hidden"
                               name="application_id"
                               value="<?= (int) $app["application_id"] ?>">

                        <div class="form-group">
                            <label for="status-<?= (int) $app["application_id"] ?>">
                                Update application status
                            </label>

                            <select
                                class="form-control"
                                id="status-<?= (int) $app["application_id"] ?>"
                                name="status"
                                required>

                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option
                                        value="<?= h($status) ?>"
                                        <?= $app["status"] === $status ? "selected" : "" ?>>
                                        <?= h($status) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <button class="btn btn-primary full-width" type="submit">
                            Save status
                        </button>
                    </form>
                </article>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</main>

<footer class="footer">
    LaunchPad · Your next chapter.
</footer>

</body>
</html>