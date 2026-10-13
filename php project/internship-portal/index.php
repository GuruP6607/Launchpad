
<?php
session_start();

$loggedIn = isset($_SESSION["user_id"]);
$role = $_SESSION["role"] ?? "";

$dashboard = match ($role) {
    "student" => "student/dashboard.php",
    "company" => "company/dashboard.php",
    default => "login.php"
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaunchPad | Find Your Internship</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">launch<span>pad.</span></a>

        <div class="nav-links">
            <a href="#opportunities">Opportunities</a>
            <a href="#how-it-works">How it works</a>

            <?php if ($loggedIn): ?>
                <a href="<?= htmlspecialchars($dashboard) ?>">Dashboard</a>
                <a class="btn btn-primary" href="logout.php">Log out</a>
            <?php else: ?>
                <a href="./login.php">Log in</a>
                <a class="btn btn-primary" href="./register.php">Get started ↗</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow">✦ YOUR NEXT CHAPTER STARTS HERE</span>

                <h1>Turn your ambition into <span>experience.</span></h1>

                <p>
                    Discover internships, build real-world skills, and connect
                    with companies ready to meet the next generation of talent.
                </p>

                <div class="hero-actions">
                    <?php if ($loggedIn): ?>
                        <a class="btn btn-primary"
                           href="<?= htmlspecialchars($dashboard) ?>">
                            Go to dashboard ↗
                        </a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="./register.php">
                            Explore opportunities ↗
                        </a>

                        <a class="btn btn-secondary" href="./login.php">
                            I have an account
                        </a>
                    <?php endif; ?>
                </div>

                <p class="muted">
                    For students looking to grow and companies looking for talent.
                </p>
            </div>

            <aside class="hero-panel">
                <span class="panel-label">
                    A glimpse of your next opportunity
                </span>

                <div class="opportunity">
                    <span class="tag">TECH</span>
                    <strong>Software Development Intern</strong>
                    <span class="muted">Build, learn, and ship real projects.</span>
                </div>

                <div class="opportunity">
                    <span class="tag">DESIGN</span>
                    <strong>UI/UX Design Intern</strong>
                    <span class="muted">Turn ideas into useful experiences.</span>
                </div>

                <div class="opportunity">
                    <span class="tag">DATA</span>
                    <strong>Data Analytics Intern</strong>
                    <span class="muted">Find insights hidden in data.</span>
                </div>

                <p class="muted">
                    Illustrative examples — sign in to view actual listings.
                </p>
            </aside>
        </div>
    </section>

    <section class="section" id="opportunities">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">BUILT FOR YOUR FUTURE</span>
                    <h2>One platform. More possibilities.</h2>
                </div>
            </div>

            <div class="grid">
                <article class="card">
                    <div class="card-icon">⌕</div>
                    <h3>Find your fit</h3>
                    <p>Explore internship listings by role, skills, and location.</p>
                </article>

                <article class="card">
                    <div class="card-icon">↗</div>
                    <h3>Apply with confidence</h3>
                    <p>Create your profile and keep track of every application.</p>
                </article>

                <article class="card">
                    <div class="card-icon">✳</div>
                    <h3>Grow together</h3>
                    <p>Companies can post openings and manage candidates in one place.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section" id="how-it-works">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">SIMPLE BY DESIGN</span>
                    <h2>Your journey in three steps.</h2>
                </div>
            </div>

            <div class="grid">
                <article class="card">
                    <span class="eyebrow">01</span>
                    <h3>Create your account</h3>
                    <p>Register as a student or a company.</p>
                </article>

                <article class="card">
                    <span class="eyebrow">02</span>
                    <h3>Take your next step</h3>
                    <p>Students apply; companies publish internship openings.</p>
                </article>

                <article class="card">
                    <span class="eyebrow">03</span>
                    <h3>Track your progress</h3>
                    <p>Follow application statuses and manage candidates.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> LaunchPad. Built for what's next.
    </div>
</footer>

<script src="js/validation.js"></script>
</body>
</html>
