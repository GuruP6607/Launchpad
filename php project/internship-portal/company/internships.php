
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

$stmt = $pdo->prepare(
    "SELECT id FROM companies WHERE user_id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$companyId = (int) $stmt->fetchColumn();

if (!$companyId) {
    exit("Company profile not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $internshipId = filter_input(
        INPUT_POST,
        "internship_id",
        FILTER_VALIDATE_INT
    );

    $newStatus = $_POST["status"] ?? "";

    if (
        !$internshipId ||
        !in_array($newStatus, ["open", "closed"], true)
    ) {
        $error = "Invalid listing update.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE internships
             SET status = ?
             WHERE id = ? AND company_id = ?"
        );

        $stmt->execute([
            $newStatus,
            $internshipId,
            $companyId
        ]);

        $check = $pdo->prepare(
            "SELECT id FROM internships
             WHERE id = ? AND company_id = ?"
        );
        $check->execute([$internshipId, $companyId]);

        if (!$check->fetchColumn()) {
            $error = "Internship listing not found.";
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT
        i.id,
        i.title,
        i.location,
        i.deadline,
        i.status,
        i.created_at,
        COUNT(a.id) AS applicant_count
     FROM internships i
     LEFT JOIN applications a ON a.internship_id = i.id
     WHERE i.company_id = ?
     GROUP BY
        i.id, i.title, i.location, i.deadline,
        i.status, i.created_at
     ORDER BY i.created_at DESC"
);

$stmt->execute([$companyId]);
$internships = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Listings | LaunchPad</title>
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
    <div class="section-heading">
        <div>
            <span class="eyebrow">COMPANY SPACE</span>
            <h2>My internship listings</h2>
        </div>

        <a class="btn btn-primary" href="add_internship.php">
            + Add listing
        </a>
    </div>

    <?php if (isset($_GET["created"])): ?>
        <div class="alert alert-success">
            Internship published successfully.
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!$internships): ?>

        <div class="empty-state">
            <h3>No listings yet</h3>
            <p>Post your first internship to start receiving applications.</p>
            <a class="btn btn-primary" href="add_internship.php">
                Post an internship
            </a>
        </div>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Deadline</th>
                        <th>Applicants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($internships as $item): ?>
                        <tr>
                            <td><?= h($item["title"]) ?></td>
                            <td><?= h($item["location"] ?: "Flexible") ?></td>
                            <td><?= h($item["deadline"] ?: "Not set") ?></td>
                            <td><?= (int) $item["applicant_count"] ?></td>

                            <td>
                                <span class="status">
                                    <?= h(ucfirst($item["status"])) ?>
                                </span>
                            </td>

                            <td>
                                <a class="text-link"
                                   href="applicants.php?internship_id=<?= (int) $item["id"] ?>">
                                    Applicants
                                </a>

                                <form class="inline-form"
                                      method="POST"
                                      action="internships.php">

                                    <input type="hidden"
                                           name="internship_id"
                                           value="<?= (int) $item["id"] ?>">

                                    <input type="hidden"
                                           name="status"
                                           value="<?= $item["status"] === "open" ? "closed" : "open" ?>">

                                    <button class="btn btn-secondary" type="submit">
                                        <?= $item["status"] === "open" ? "Close" : "Reopen" ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</main>

<footer class="footer">
    LaunchPad · Your next chapter.
</footer>

</body>
</html>