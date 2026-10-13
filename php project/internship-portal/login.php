
<?php
session_start();
require __DIR__ . "/config/database.php";

if (isset($_SESSION["user_id"])) {
    $target = $_SESSION["role"] === "company"
        ? "company/dashboard.php"
        : "student/dashboard.php";
    header("Location: " . $target);
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
        $error = "Enter your email and password.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            if (!in_array($user["role"], ["student", "company", "admin"], true)) {
                $error = "This account has an invalid role.";
            } else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int) $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "company") {
                    header("Location: company/dashboard.php");
                } elseif ($user["role"] === "admin") {
                    // An admin dashboard is not part of this project yet.
                    header("Location: index.php");
                } else {
                    header("Location: student/dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | LaunchPad</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">launch<span>pad.</span></a>
        <div class="nav-links"><a href="register.php">Create an account</a></div>
    </div>
</nav>

<main class="form-section">
    <section class="form-card">
        <span class="eyebrow">WELCOME BACK</span>
        <h1>Good to see you.</h1>
        <p class="muted">Log in to continue your journey.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" data-validate>
            <div class="form-group">
                <label for="email">Email address</label>
                <input class="form-control" type="email" id="email" name="email"
                       required autocomplete="email"
                       value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" type="password" id="password"
                       name="password" required autocomplete="current-password">
            </div>

            <button class="btn btn-primary full-width" type="submit">Log in ↗</button>
        </form>

        <p class="muted">New to LaunchPad? <a class="text-link" href="register.php">Create an account</a></p>
    </section>
</main>
<footer class="footer">© <?= date("Y") ?> LaunchPad</footer>
<script src="js/validation.js"></script>
</body>
</html>