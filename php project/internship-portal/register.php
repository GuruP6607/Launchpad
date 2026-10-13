
<?php
session_start();
require __DIR__ . "/config/database.php";

if (isset($_SESSION["user_id"])) {
    header("Location: " . (
        $_SESSION["role"] === "company"
            ? "company/dashboard.php"
            : "student/dashboard.php"
    ));
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "";

    if ($name === "" || strlen($name) > 100) {
        $error = "Please enter a name under 100 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Your password must contain at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "The passwords do not match.";
    } elseif (!in_array($role, ["student", "company"], true)) {
        $error = "Please select a valid account type.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role
            ]);

            $userId = (int) $pdo->lastInsertId();

            if ($role === "student") {
                $stmt = $pdo->prepare(
                    "INSERT INTO students (user_id) VALUES (?)"
                );
                $stmt->execute([$userId]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO companies (user_id, company_name)
                     VALUES (?, ?)"
                );
                $stmt->execute([$userId, $name]);
            }

            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION["user_id"] = $userId;
            $_SESSION["name"] = $name;
            $_SESSION["role"] = $role;

            header("Location: " . (
                $role === "student"
                    ? "student/dashboard.php"
                    : "company/dashboard.php"
            ));
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e->getCode() === "23000") {
                $error = "This email is already registered. Please log in.";
            } else {
                error_log("Registration failed: " . $e->getMessage());
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account | LaunchPad</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">launch<span>pad.</span></a>
        <div class="nav-links"><a href="login.php">Already registered? Log in</a></div>
    </div>
</nav>

<main class="form-section">
    <section class="form-card">
        <span class="eyebrow">START YOUR JOURNEY</span>
        <h1>Create your account</h1>
        <p class="muted">Join the community and take your next step.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" data-validate>
            <div class="form-group">
                <label for="name">Full name / Company name</label>
                <input class="form-control" id="name" name="name"
                       maxlength="100" required autocomplete="name"
                       value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input class="form-control" type="email" id="email" name="email"
                       maxlength="150" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="role">I am registering as</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="">Choose account type</option>
                    <option value="student" <?= ($_POST["role"] ?? "") === "student" ? "selected" : "" ?>>Student</option>
                    <option value="company" <?= ($_POST["role"] ?? "") === "company" ? "selected" : "" ?>>Company</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" type="password" id="password"
                       name="password" minlength="8" required
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input class="form-control" type="password" id="confirm_password"
                       name="confirm_password" minlength="8" required
                       autocomplete="new-password">
            </div>

            <button class="btn btn-primary full-width" type="submit">
                Create account ↗
            </button>
        </form>

        <p class="muted">Already have an account? <a class="text-link" href="login.php">Log in</a></p>
    </section>
</main>
<footer class="footer">© <?= date("Y") ?> LaunchPad</footer>
<script src="js/validation.js"></script>
</body>
</html>