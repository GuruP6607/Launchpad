
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

$q = trim($_GET["q"] ?? "");

$sql = "SELECT
            i.id,
            i.title,
            i.description,
            i.skills,
            i.location,
            i.deadline,
            c.company_name
        FROM internships i
        INNER JOIN companies c ON c.id = i.company_id
        WHERE i.status = 'open'
          AND (i.deadline IS NULL OR i.deadline >= CURDATE())";

$params = [];

if ($q !== "") {
    $sql .= " AND (
        i.title LIKE ?
        OR i.description LIKE ?
        OR i.skills LIKE ?
        OR i.location LIKE ?
        OR c.company_name LIKE ?
    )";

    $term = "%" . $q . "%";
    $params = [$term, $term, $term, $term, $term];
}

$sql .= " ORDER BY i.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$internships = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT id FROM students WHERE user_id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$studentId = (int) $stmt->fetchColumn();

if (!$studentId) {
    exit("Student profile not found.");
}

$stmt = $pdo->prepare(
    "SELECT internship_id FROM applications WHERE student_id = ?"
);
$stmt->execute([$studentId]);

$appliedIds = array_map(
    "intval",
    $stmt->fetchAll(PDO::FETCH_COLUMN)
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find Internships | LaunchPad</title>
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
            <a href="profile.php">My profile</a>
            <a href="applications.php">Applications</a>
            <a href="../logout.php">Log out</a>
        </div>
    </div>
</nav>

<main class="container section">
    <span class="eyebrow">YOUR NEXT OPPORTUNITY</span>

    <div class="section-heading">
        <div>
            <h2>Find your internship.</h2>
            <p class="muted">
                Search opportunities that match your interests.
            </p>
        </div>
    </div>

    <form method="GET" action="internships.php"
          style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:28px">

        <input
            class="form-control"
            name="q"
            style="flex:1; min-width:200px"
            placeholder="Search title, skills, company or location..."
            value="<?= h($q) ?>">

        <button class="btn btn-primary" type="submit">
            Search
        </button>

        <a class="btn btn-secondary" href="internships.php">
            Clear
        </a>
    </form>

    <?php if (!$internships): ?>

        <div class="empty-state">
            <h3>No internships found</h3>
            <p>Try another keyword or check back later.</p>
        </div>

    <?php else: ?>

        <div class="grid">
            <?php foreach ($internships as $item): ?>

                <article class="card">
                    <span class="eyebrow">
                        <?= h($item["location"] ?: "Location flexible") ?>
                    </span>

                    <h3><?= h($item["title"]) ?></h3>

                    <p><?= h($item["company_name"]) ?></p>

                    <p>
                        <?= h(mb_strimwidth(
                            $item["description"],
                            0,
                            145,
                            "..."
                        )) ?>
                    </p>

                    <?php if ($item["skills"]): ?>
                        <p>
                            <span class="tag"><?= h($item["skills"]) ?></span>
                        </p>
                    <?php endif; ?>

                    <p class="muted">
                        Deadline: <?= h($item["deadline"] ?: "Not specified") ?>
                    </p>

                    <?php if (in_array((int) $item["id"], $appliedIds, true)): ?>

                        <span class="status">Already applied</span>

                    <?php else: ?>

                        <a class="btn btn-primary"
                           href="apply.php?id=<?= (int) $item["id"] ?>">
                            View &amp; apply ↗
                        </a>

                    <?php endif; ?>
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