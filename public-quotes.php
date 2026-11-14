
<?php
require_once 'includes/bootstrap.php';

requireLogin();

$quotes = $cards->publicAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Public Quotes | Quoteify</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f8f5ef;
            color: #3d3028;
            font-family: 'DM Sans', sans-serif;
        }

        .navbar {
            background: #fffefa;
            border-bottom: 1px solid #eee7dd;
            padding: 15px 5%;
        }

        .navbar-brand {
            color: #765640 !important;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
        }

        .nav-link {
            color: #76685d !important;
            font-size: 13px;
            font-weight: 600;
            margin: 0 7px;
        }

        .main-container {
            max-width: 1200px;
            margin: auto;
            padding: 40px 20px;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .subtitle {
            color: #9b8b7d;
            font-size: 13px;
        }

        .quote-card {
            min-height: 280px;
            padding: 30px 25px;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow-wrap: anywhere;
            transition: transform .2s, box-shadow .2s;
        }

        .quote-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(61, 48, 40, .10);
        }

        .quote-label {
            font-size: 10px;
            letter-spacing: 2px;
            margin-bottom: 20px;
            opacity: .7;
        }

        .quote-text {
    width: 100%;
    display: block;
    text-align: center !important;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

.quote-card {
    text-align: center;
}

        .quote-author {
            margin-top: 20px;
            font-size: 13px;
            opacity: .8;
        }

        .quote-info {
            font-size: 11px;
            color: #9b8b7d;
            margin-top: 15px;
        }

        .empty-state {
            background: #fffefa;
            border: 1px solid #eee7dd;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
        }

        @media(max-width: 576px) {
            .main-container {
                padding: 25px 15px;
            }

            .quote-card {
                min-height: 240px;
            }
        }
    </style>
</head>

<body>

<!-- NAVIGATION -->

<nav class="navbar navbar-expand-lg">

    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php">
            Quoteify.
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="public-quotes.php">Public Quotes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="studio.php">Create Quote</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="my-quotes.php">My Quotes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="profile.php">My Profile</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- PUBLIC QUOTES -->

<main class="main-container">

    <div class="mb-4">

        <div class="text-uppercase small text-secondary">
            QUOTEIFY / COMMUNITY
        </div>

        <h1 class="page-title mt-2">
            Public Quotes
        </h1>

        <p class="subtitle">
            Discover meaningful words and creative quote designs shared by others.
        </p>

    </div>


    <?php if (empty($quotes)): ?>

        <div class="empty-state">

            <h4 class="page-title">
                No Public Quotes Yet
            </h4>

            <p class="subtitle">
                Be the first to share your quote with the community.
            </p>

            <a href="studio.php" class="btn btn-dark mt-2">
                Create a Quote
            </a>

        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($quotes as $quote): ?>

                <?php
                    $background = $quote['background_color'] ?? '#f5e6d3';
                    $fontColor = $quote['font_color'] ?? '#3d3028';
                    $fontFamily = $quote['font_family'] ?? 'Georgia';
                    $fontSize = (int)($quote['font_size'] ?? 28);
                    $borderStyle = $quote['border_style'] ?? 'solid';
                    $layoutStyle = $quote['layout_style'] ?? 'classic';

                    $allowedFonts = [
                        'Georgia',
                        'Arial',
                        'Times New Roman',
                        'Verdana',
                        'Courier New',
                        'Trebuchet MS'
                    ];

                    if (!in_array($fontFamily, $allowedFonts, true)) {
                        $fontFamily = 'Georgia';
                    }

                    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $background)) {
                        $background = '#f5e6d3';
                    }

                    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $fontColor)) {
                        $fontColor = '#3d3028';
                    }

                    if (!in_array($borderStyle, [
                        'none', 'solid', 'double', 'dashed', 'dotted'
                    ], true)) {
                        $borderStyle = 'solid';
                    }

                    if ($fontSize < 14 || $fontSize > 60) {
                        $fontSize = 28;
                    }

                    $borderWidth = $borderStyle === 'none' ? '0' : '2px';
                    $borderColor = $fontColor;
                    $borderRadius = $layoutStyle === 'framed' ? '0' : '14px';
                ?>

                <div class="col-12 col-md-6 col-lg-4">

                    <div
                        class="quote-card"
                        style="
                            background-color: <?= e($background) ?>;
                            color: <?= e($fontColor) ?>;
                            font-family: <?= e($fontFamily) ?>;
                            border-style: <?= e($borderStyle) ?>;
                            border-width: <?= e($borderWidth) ?>;
                            border-color: <?= e($borderColor) ?>;
                            border-radius: <?= e($borderRadius) ?>;
                            <?= $layoutStyle === 'framed'
                                ? 'outline: 5px double ' . e($fontColor) . ';'
                                : '' ?>
                        "
                    >

                        <div class="quote-label">
                            A QUOTE TO REMEMBER
                        </div>

                        <div
                            class="quote-text"
                            style="font-size: <?= $fontSize ?>px;"
                        >
                            <?= e($quote['quote_text']) ?>
                        </div>

                        <?php if (!empty($quote['author'])): ?>

                            <div class="quote-author">
                                — <?= e($quote['author']) ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="quote-info">

                        Shared by:
                        <strong>
                            <?= e($quote['creator'] ?? 'Quoteify Member') ?>
                        </strong>

                        <span class="float-end">
                            <?= !empty($quote['created_at'])
                                ? date('M d, Y', strtotime($quote['created_at']))
                                : '' ?>
                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
