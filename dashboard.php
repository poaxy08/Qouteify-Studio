
<?php
require_once 'includes/bootstrap.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'Member';

$isAdmin = ($userRole === 'Admin');

// =====================================
// MEMBER DASHBOARD DATA
// =====================================

if (!$isAdmin) {
    $myQuotes = $cards->all($userId);

    $totalQuotes = count($myQuotes);
    $publicQuotes = 0;
    $privateQuotes = 0;

    foreach ($myQuotes as $quote) {
        if (($quote['visibility'] ?? 'Private') === 'Public') {
            $publicQuotes++;
        } else {
            $privateQuotes++;
        }
    }
}

// =====================================
// ADMIN DASHBOARD DATA
// =====================================

if ($isAdmin) {

    $totalUsers = (int) $db->query(
        "SELECT COUNT(*) FROM users"
    )->fetchColumn();

    $totalQuotes = (int) $db->query(
        "SELECT COUNT(*) FROM quote_cards"
    )->fetchColumn();

    $publicQuotes = (int) $db->query(
        "SELECT COUNT(*) FROM quote_cards WHERE visibility = 'Public'"
    )->fetchColumn();

    $privateQuotes = (int) $db->query(
        "SELECT COUNT(*) FROM quote_cards WHERE visibility = 'Private'"
    )->fetchColumn();

    // Recent users
    $recentUsers = $db->query(
        "SELECT id, name, email, role, created_at
         FROM users
         ORDER BY created_at DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    // =====================================
    // RECENT PUBLIC QUOTES ONLY
    // Private quotes are excluded.
    // =====================================

    $recentQuotes = $db->query(
        "SELECT q.quote_text, q.author, q.visibility,
                q.created_at, u.name AS creator
         FROM quote_cards q
         INNER JOIN users u ON q.user_id = u.id
         WHERE q.visibility = 'Public'
         ORDER BY q.created_at DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// =====================================
// HELPER
// =====================================

function dashboardDate($date) {
    if (empty($date)) {
        return 'N/A';
    }

    return date('M d, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $isAdmin ? 'Admin Dashboard' : 'Member Dashboard' ?> | Quoteify
    </title>

    <!-- BOOTSTRAP -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- GOOGLE FONTS -->
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --cream: #f8f5ef;
            --white: #fffefa;
            --brown: #765640;
            --dark: #3d3028;
            --muted: #9b8b7d;
            --line: #eee7dd;
            --light-brown: #e9dfd2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--dark);
            font-family: 'DM Sans', sans-serif;
        }

        /* NAVBAR */

        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--line);
            padding: 15px 5%;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            color: var(--brown) !important;
            font-size: 28px;
            font-weight: 700;
        }

        .nav-link {
            color: #76685d !important;
            font-size: 13px;
            font-weight: 600;
            margin: 0 7px;
            transition: .2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--brown) !important;
        }

        .profile-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1e9df;
            color: var(--brown);
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #d7c1a9;
            color: white;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .dropdown-menu {
            border-radius: 12px;
            padding: 8px;
        }

        .dropdown-item {
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 7px;
        }

        /* MAIN CONTENT */

        .main-container {
            max-width: 1250px;
            margin: auto;
            padding: 45px 25px;
        }

        .eyebrow {
            color: var(--muted);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin: 6px 0;
            font-weight: 700;
        }

        .subtitle {
            color: var(--muted);
            font-size: 13px;
        }

        /* WELCOME BANNER */

        .welcome-banner {
            background: var(--light-brown);
            border-radius: 20px;
            padding: 35px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner h2 {
            font-family: 'Playfair Display', serif;
            font-size: 29px;
            margin: 8px 0;
            font-weight: 700;
        }

        .welcome-banner p {
            color: #786758;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .welcome-decoration {
            position: absolute;
            right: 45px;
            top: -10px;
            font-family: 'Playfair Display', serif;
            font-size: 130px;
            color: rgba(118, 86, 64, .13);
            pointer-events: none;
        }

        /* BUTTONS */

        .btn-brown {
            display: inline-block;
            background: var(--brown);
            color: white;
            border: 1px solid var(--brown);
            border-radius: 9px;
            padding: 11px 19px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: .2s;
        }

        .btn-brown:hover {
            background: #5e4432;
            color: white;
            border-color: #5e4432;
        }

        .btn-outline-brown {
            display: inline-block;
            background: transparent;
            border: 1px solid var(--brown);
            color: var(--brown);
            border-radius: 9px;
            padding: 10px 18px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: .2s;
        }

        .btn-outline-brown:hover {
            background: var(--brown);
            color: white;
        }

        /* STATISTICS */

        .stat-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 23px;
            height: 100%;
            transition: .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(75, 55, 38, .06);
        }

        .stat-icon {
            width: 43px;
            height: 43px;
            background: #f2e9df;
            color: var(--brown);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
        }

        /* CONTENT PANELS */

        .section-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 17px;
            padding: 25px;
            height: 100%;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 21px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .view-link {
            color: var(--brown);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        /* QUOTE CARDS */

        .quote-card {
            background: #faf8f4;
            border: 1px solid var(--line);
            border-radius: 13px;
            padding: 19px;
            margin-top: 14px;
        }

        .quote-card-text {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            line-height: 1.5;
            color: var(--dark);
            overflow-wrap: anywhere;
        }

        .quote-author {
            font-size: 12px;
            color: var(--muted);
            margin-top: 10px;
        }

        .visibility {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            margin-top: 10px;
        }

        .public {
            background: #e9f0e5;
            color: #58734f;
        }

        .private {
            background: #f2e9df;
            color: #8a684b;
        }

        /* QUICK ACTIONS */

        .action-item {
            background: #faf8f4;
            border: 1px solid var(--line);
            border-radius: 13px;
            padding: 17px;
            margin-top: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .action-item strong {
            font-size: 13px;
        }

        .action-description {
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
        }

        /* TABLE */

        .table th {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom-color: var(--line);
            white-space: nowrap;
        }

        .table td {
            color: #65564b;
            font-size: 12px;
            vertical-align: middle;
            border-bottom-color: var(--line);
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .role-admin {
            background: #eee2d3;
            color: #765640;
        }

        .role-member {
            background: #edf0e8;
            color: #63704f;
        }

        /* EMPTY STATE */

        .empty-state {
            text-align: center;
            padding: 35px 15px;
            color: var(--muted);
        }

        .empty-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        /* FOOTER */

        .footer {
            text-align: center;
            color: var(--muted);
            font-size: 11px;
            padding: 30px 0;
        }

        /* RESPONSIVE */

        @media (max-width: 768px) {

            .main-container {
                padding: 30px 17px;
            }

            .page-title {
                font-size: 29px;
            }

            .welcome-banner {
                padding: 25px;
            }

            .welcome-banner h2 {
                font-size: 24px;
            }

            .welcome-decoration {
                display: none;
            }

            .profile-pill {
                margin-top: 12px;
                width: fit-content;
            }

        }

    </style>

</head>

<body>

<!-- =====================================
     NAVIGATION BAR
====================================== -->

<nav class="navbar navbar-expand-lg">

    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php">
            Quoteify.
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="public-quotes.php">
                        Public Quotes
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="studio.php">
                        Create Quote
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="my-quotes.php">
                        My Quotes
                    </a>
                </li>

                <?php if ($isAdmin): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="admin-users.php">
                            Manage Users
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

            <!-- USER PROFILE MENU -->

            <div class="dropdown">

                <button
                    class="btn p-0 border-0"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <div class="profile-pill">

                        <div class="profile-avatar">
                            <?= e(strtoupper(substr($userName, 0, 1))) ?>
                        </div>

                        <?= e($userName) ?>

                        ▾

                    </div>

                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                    <li>
                        <a class="dropdown-item" href="profile.php">
                            My Profile
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="my-quotes.php">
                            My Quotes
                        </a>
                    </li>

                    <?php if ($isAdmin): ?>
                        <li>
                            <a class="dropdown-item" href="admin-users.php">
                                Manage Users
                            </a>
                        </li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            Logout
                        </a>
                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>


<!-- =====================================
     MAIN DASHBOARD
====================================== -->

<main class="main-container">

    <div class="eyebrow">
        QUOTEIFY / WORKSPACE
    </div>

    <h1 class="page-title">
        <?= $isAdmin ? 'Admin Dashboard' : 'My Dashboard' ?>
    </h1>

    <p class="subtitle">
        Your creative space for meaningful words and beautiful designs.
    </p>


    <!-- =====================================
         WELCOME BANNER
    ====================================== -->

    <section class="welcome-banner">

        <div class="welcome-decoration">
            Q.
        </div>

        <div class="eyebrow">
            WELCOME TO YOUR CREATIVE SPACE
        </div>

        <h2>
            Welcome back, <?= e($userName) ?>!
        </h2>

        <p>

            <?php if ($isAdmin): ?>

                Manage your Quoteify community and keep everything organized.

            <?php else: ?>

                Your words, your style, your creativity.
                Continue creating beautiful quote cards.

            <?php endif; ?>

        </p>

        <a href="studio.php" class="btn-brown">
            + Create a Quote
        </a>

        <a href="my-quotes.php" class="btn-outline-brown ms-2">
            View My Quotes
        </a>

    </section>


    <!-- =====================================
         STATISTICS CARDS
    ====================================== -->

    <div class="row g-3 mb-4">

        <?php if ($isAdmin): ?>

            <!-- ADMIN STATISTICS -->

            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">♙</div>

                    <div class="stat-number">
                        <?= $totalUsers ?>
                    </div>

                    <div class="stat-label">
                        Total Users
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">▤</div>

                    <div class="stat-number">
                        <?= $totalQuotes ?>
                    </div>

                    <div class="stat-label">
                        Total Quote Cards
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">✧</div>

                    <div class="stat-number">
                        <?= $publicQuotes ?>
                    </div>

                    <div class="stat-label">
                        Public Quotes
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">♡</div>

                    <div class="stat-number">
                        <?= $privateQuotes ?>
                    </div>

                    <div class="stat-label">
                        Private Quotes
                    </div>

                </div>

            </div>


        <?php else: ?>

            <!-- MEMBER STATISTICS -->

            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">▤</div>

                    <div class="stat-number">
                        <?= $totalQuotes ?>
                    </div>

                    <div class="stat-label">
                        My Quote Cards
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">✧</div>

                    <div class="stat-number">
                        <?= $publicQuotes ?>
                    </div>

                    <div class="stat-label">
                        My Public Quotes
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">♡</div>

                    <div class="stat-number">
                        <?= $privateQuotes ?>
                    </div>

                    <div class="stat-label">
                        My Private Quotes
                    </div>

                </div>

            </div>


            <div class="col-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">♙</div>

                    <div class="stat-number" style="font-size: 23px;">
                        Member
                    </div>

                    <div class="stat-label">
                        Account Type
                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- =====================================
         ADMIN CONTENT
    ====================================== -->

    <?php if ($isAdmin): ?>

        <div class="row g-4">

            <!-- RECENT USERS -->

            <div class="col-12 col-xl-6">

                <section class="section-card">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h3 class="section-title">
                                Recent Users
                            </h3>

                            <p class="subtitle mb-0">
                                Latest registered accounts.
                            </p>

                        </div>

                        <a href="admin-users.php" class="view-link">
                            VIEW ALL →
                        </a>

                    </div>


                    <div class="table-responsive">

                        <table class="table">

                            <thead>

                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($recentUsers as $person): ?>

                                    <tr>

                                        <td>

                                            <strong>
                                                <?= e($person['name']) ?>
                                            </strong>

                                            <br>

                                            <small class="text-secondary">
                                                <?= e($person['email']) ?>
                                            </small>

                                        </td>

                                        <td>

                                            <span class="role-badge <?= $person['role'] === 'Admin' ? 'role-admin' : 'role-member' ?>">

                                                <?= e($person['role']) ?>

                                            </span>

                                        </td>

                                        <td>
                                            <?= dashboardDate($person['created_at']) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                                <?php if (empty($recentUsers)): ?>

                                    <tr>
                                        <td colspan="3">
                                            No users found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </section>

            </div>


            <!-- LATEST PUBLIC QUOTES -->

            <div class="col-12 col-xl-6">

                <section class="section-card">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h3 class="section-title">
                                Latest Quotes
                            </h3>

                            <p class="subtitle mb-0">
                                Recently shared public quote cards.
                            </p>

                        </div>

                        <a href="public-quotes.php" class="view-link">
                            EXPLORE →
                        </a>

                    </div>


                    <?php foreach ($recentQuotes as $quote): ?>

                        <div class="quote-card">

                            <div class="quote-card-text">
                                “<?= e($quote['quote_text']) ?>”
                            </div>

                            <div class="quote-author">

                                By <?= e($quote['creator']) ?>

                                <?php if (!empty($quote['author'])): ?>

                                    · <?= e($quote['author']) ?>

                                <?php endif; ?>

                            </div>

                            <span class="visibility public">
                                Public
                            </span>

                        </div>

                    <?php endforeach; ?>


                    <?php if (empty($recentQuotes)): ?>

                        <div class="empty-state">
                            <div class="empty-icon">✧</div>
                            <h5>No public quotes yet.</h5>
                            <p>Public quote cards will appear here when users share them.</p>
                        </div>

                    <?php endif; ?>

                </section>

            </div>

        </div>


    <!-- =====================================
         MEMBER CONTENT
    ====================================== -->

    <?php else: ?>

        <div class="row g-4">

            <!-- MY RECENT QUOTES -->

            <div class="col-12 col-xl-6">

                <section class="section-card">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h3 class="section-title">
                                My Recent Quotes
                            </h3>

                            <p class="subtitle mb-0">
                                Your latest created quote cards.
                            </p>

                        </div>

                        <a href="my-quotes.php" class="view-link">
                            VIEW ALL →
                        </a>

                    </div>


                    <?php if (!empty($myQuotes)): ?>

                        <?php foreach (array_slice($myQuotes, 0, 5) as $quote): ?>

                            <div class="quote-card">

                                <div class="quote-card-text">
                                    “<?= e($quote['quote_text']) ?>”
                                </div>

                                <div class="quote-author">

                                    By

                                    <?= !empty($quote['author'])
                                        ? e($quote['author'])
                                        : 'Unknown Author'
                                    ?>

                                </div>

                                <span class="visibility <?= ($quote['visibility'] ?? 'Private') === 'Public' ? 'public' : 'private' ?>">

                                    <?= e($quote['visibility'] ?? 'Private') ?>

                                </span>

                            </div>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                ✧
                            </div>

                            <h5>No quote cards yet.</h5>

                            <p>
                                Start creating your first quote card today!
                            </p>

                            <a href="studio.php" class="btn-brown">
                                Create My First Quote
                            </a>

                        </div>

                    <?php endif; ?>

                </section>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="col-12 col-xl-6">

                <section class="section-card">

                    <h3 class="section-title">
                        Quick Actions
                    </h3>

                    <p class="subtitle mb-3">
                        Manage your Quoteify account and creations.
                    </p>


                    <!-- CREATE QUOTE -->

                    <div class="action-item">

                        <div>

                            <strong>
                                Create a Quote
                            </strong>

                            <div class="action-description">
                                Design your own quote card.
                            </div>

                        </div>

                        <a href="studio.php" class="btn-brown">
                            Create
                        </a>

                    </div>


                    <!-- MY QUOTES -->

                    <div class="action-item">

                        <div>

                            <strong>
                                My Quotes
                            </strong>

                            <div class="action-description">
                                View and manage your quote cards.
                            </div>

                        </div>

                        <a href="my-quotes.php" class="btn-outline-brown">
                            View
                        </a>

                    </div>


                    <!-- PUBLIC QUOTES -->

                    <div class="action-item">

                        <div>

                            <strong>
                                Public Quotes
                            </strong>

                            <div class="action-description">
                                Explore quotes shared by the community.
                            </div>

                        </div>

                        <a href="public-quotes.php" class="btn-outline-brown">
                            Explore
                        </a>

                    </div>


                    <!-- PROFILE -->

                    <div class="action-item">

                        <div>

                            <strong>
                                My Profile
                            </strong>

                            <div class="action-description">
                                View your account information.
                            </div>

                        </div>

                        <a href="profile.php" class="btn-outline-brown">
                            Profile
                        </a>

                    </div>

                </section>

            </div>

        </div>

    <?php endif; ?>


    <!-- FOOTER -->

    <footer class="footer">

        © <?= date('Y') ?> Quoteify Studio.
        Made for creativity.

    </footer>

</main>


<!-- BOOTSTRAP JAVASCRIPT -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
