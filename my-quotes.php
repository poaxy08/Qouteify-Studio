
<?php
require_once 'includes/bootstrap.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');
$message = '';
$error = '';

// Delete quote
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_quote_id'])) {

    $quoteId = (int) $_POST['delete_quote_id'];

    try {
        $cards->delete($quoteId, $userId);
        header('Location: my-quotes.php?deleted=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Unable to delete this quote.';
    }
}

if (isset($_GET['deleted'])) {
    $message = 'Your quote has been deleted successfully.';
}

// Get user's quotes
$myQuotes = $cards->all($userId);

// Search quotes
if ($search !== '') {
    $myQuotes = array_filter($myQuotes, function ($quote) use ($search) {
        return stripos($quote['quote_text'], $search) !== false
            || stripos($quote['author'] ?? '', $search) !== false;
    });
}

// Count public and private quotes
$totalQuotes = count($cards->all($userId));
$publicCount = 0;
$privateCount = 0;

foreach ($cards->all($userId) as $quote) {
    if (($quote['visibility'] ?? 'Private') === 'Public') {
        $publicCount++;
    } else {
        $privateCount++;
    }
}

// Validate text alignment
function quoteAlignment($alignment) {
    $allowed = ['left', 'center', 'right', 'justify'];

    return in_array($alignment, $allowed, true)
        ? $alignment
        : 'center';
}

// Validate font color
function quoteFontColor($color) {
    $color = trim((string) $color);

    // Accept HEX colors: #fff or #ffffff
    if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/', $color)) {
        return $color;
    }

    // Accept RGB and RGBA colors
    if (preg_match('/^rgba?\(\s*[\d.%\s,]+\)$/i', $color)) {
        return $color;
    }

    // Default color
    return '#40352d';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Quotes | Quoteify</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --cream: #f8f5ef;
            --white: #fffefa;
            --brown: #80634b;
            --dark: #40352d;
            --muted: #988b7e;
            --border: #eae2d8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--cream);
            color: var(--dark);
            font-family: 'DM Sans', sans-serif;
        }

        .page-header {
            text-align: center;
            padding: 55px 15px 30px;
        }

        .page-label {
            color: var(--brown);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 5vw, 52px);
            margin: 13px 0;
        }

        .page-header p {
            color: var(--muted);
            max-width: 500px;
            margin: auto;
            line-height: 1.8;
        }

        .header-icon {
            color: #c6a98b;
            font-size: 25px;
            margin-top: 15px;
        }

        .content-area {
            padding-bottom: 65px;
        }

        .create-btn {
            background: var(--brown);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 11px 22px;
            text-decoration: none;
            display: inline-block;
            transition: .2s;
        }

        .create-btn:hover {
            background: #634b38;
            color: white;
            transform: translateY(-2px);
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 20px;
            height: 100%;
        }

        .stat-icon {
            color: var(--brown);
            font-size: 21px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 29px;
            font-weight: 600;
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
        }

        .search-panel {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 15px;
            margin: 30px 0;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-box i {
            color: var(--muted);
            font-size: 18px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            flex: 1;
            min-width: 0;
            padding: 8px;
        }

        .search-box button {
            background: var(--brown);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 9px 20px;
        }

        .section-heading {
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            margin-bottom: 20px;
        }

        .quote-card {
            height: 100%;
            min-height: 285px;
            padding: 26px;
            border-radius: 17px;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transition: .25s;
            box-shadow: 0 5px 18px rgba(70, 50, 30, .04);
        }

        .quote-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(70, 50, 30, .10);
        }

        .quote-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .quote-mark {
            font-family: 'Playfair Display', serif;
            font-size: 58px;
            line-height: .8;
            color: rgba(128, 99, 75, .35);
        }

        .visibility {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .visibility.public {
            background: #e9eee4;
            color: #697b5c;
        }

        .visibility.private {
            background: #eee8e2;
            color: #897766;
        }

       .quote-text {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    text-align: center !important;
    white-space: normal;
    overflow-wrap: break-word;
    margin: 0 auto;
    padding: 0 15px;
    box-sizing: border-box;
}

.quote-text p,
.quote-text span {
    width: 100%;
    text-align: center !important;
}

        

        .quote-author {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .quote-date {
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
            font-weight: 400;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            border-top: 1px solid rgba(128, 99, 75, .15);
            padding-top: 15px;
        }

        .edit-btn,
        .delete-btn {
            border-radius: 25px;
            font-size: 12px;
            padding: 7px 15px;
        }

        .edit-btn {
            background: var(--brown);
            color: white;
            text-decoration: none;
            border: 1px solid var(--brown);
        }

        .edit-btn:hover {
            background: #634b38;
            color: white;
        }

        .delete-btn {
            background: transparent;
            color: #a16b61;
            border: 1px solid #d8bcb5;
        }

        .delete-btn:hover {
            background: #a16b61;
            color: white;
        }

        .empty-state {
            background: var(--white);
            border: 1px dashed #d8cabb;
            border-radius: 17px;
            text-align: center;
            padding: 65px 20px;
        }

        .empty-state i {
            color: #c6a98b;
            font-size: 48px;
        }

        .empty-state h4 {
            font-family: 'Playfair Display', serif;
            margin-top: 15px;
        }

        .empty-state p {
            color: var(--muted);
        }

        .alert {
            border-radius: 12px;
        }

        footer {
            text-align: center;
            padding: 25px;
            background: #eee7dc;
            color: #877767;
            font-size: 13px;
        }

        @media (max-width: 576px) {
            .page-header {
                padding-top: 40px;
            }

            .quote-card {
                min-height: 250px;
            }
        }
    </style>
</head>

<body>

<?php require_once 'includes/navbar.php'; ?>


<!-- Page Header -->
<section class="page-header">

    <div class="page-label">
        Your Personal Collection
    </div>

    <h1>My Quotes.</h1>

    <p>
        A collection of words that inspire you, thoughts
        that matter, and memories worth keeping.
    </p>

    <div class="header-icon">
        <i class="bi bi-flower1"></i>
    </div>

</section>


<div class="container content-area">

    <!-- Alerts -->
    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>


    <!-- Top Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h5 class="mb-1">
                Hello, <?= e($_SESSION['name'] ?? 'Member') ?>!
            </h5>

            <small class="text-secondary">
                Keep creating something beautiful.
            </small>
        </div>

        <a href="studio.php" class="create-btn">
            <i class="bi bi-plus-lg me-1"></i>
            Create New Quote
        </a>

    </div>


    <!-- Statistics -->
    <div class="row g-3">

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-journal-heart"></i>
                </div>

                <div class="stat-number">
                    <?= $totalQuotes ?>
                </div>

                <div class="stat-label">
                    Total Quotes
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-globe"></i>
                </div>

                <div class="stat-number">
                    <?= $publicCount ?>
                </div>

                <div class="stat-label">
                    Public Quotes
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-lock"></i>
                </div>

                <div class="stat-number">
                    <?= $privateCount ?>
                </div>

                <div class="stat-label">
                    Private Quotes
                </div>
            </div>
        </div>

    </div>


    <!-- Search -->
    <div class="search-panel">

        <form method="GET" action="my-quotes.php">

            <div class="search-box">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="search"
                    placeholder="Find a quote or author..."
                    value="<?= e($search) ?>"
                >

                <button type="submit">
                    Search
                </button>

            </div>

        </form>

    </div>


    <!-- Quote Collection -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="section-heading mb-0">
            My Collection
        </h4>

        <span class="text-secondary small">
            <?= count($myQuotes) ?> found
        </span>

    </div>


    <?php if (!empty($myQuotes)): ?>

        <div class="row g-4">

            <?php foreach ($myQuotes as $quote): ?>

                <?php
                    $background = $quote['background_color'] ?? '#fffefa';
                    $font = $quote['font_family'] ?? 'Georgia';
                    $fontSize = (int) ($quote['font_size'] ?? 28);
                    $borderStyle = $quote['border_style'] ?? 'none';

                    // Get saved alignment
                    $alignment = quoteAlignment(
                        $quote['text_alignment'] ?? 'center'
                    );

                    // Get saved font color
                    $fontColor = quoteFontColor(
                        $quote['font_color'] ?? '#40352d'
                    );

                    // Validate font size
                    if ($fontSize < 10 || $fontSize > 100) {
                        $fontSize = 28;
                    }

                    // Validate border style
                    $allowedBorders = [
                        'none',
                        'solid',
                        'dashed',
                        'dotted',
                        'double'
                    ];

                    if (!in_array($borderStyle, $allowedBorders, true)) {
                        $borderStyle = 'none';
                    }

                    $isPublic = ($quote['visibility'] ?? 'Private') === 'Public';
                ?>

                <div class="col-md-6 col-lg-4">

                    <div class="quote-card"
                         style="
                            background-color: <?= e($background) ?>;
                            font-family: <?= e($font) ?>;
                            border-style: <?= e($borderStyle) ?>;
                         ">

                        <div class="quote-top">

                            <div class="quote-mark">
                                “
                            </div>

                            <span class="visibility <?= $isPublic ? 'public' : 'private' ?>">

                                <?php if ($isPublic): ?>
                                    <i class="bi bi-globe2"></i>
                                    Public
                                <?php else: ?>
                                    <i class="bi bi-lock"></i>
                                    Private
                                <?php endif; ?>

                            </span>

                        </div>


                        <!-- Quote Text: Font Color and Alignment -->
                        <div class="quote-text"
                             style="
                                font-family: <?= e($font) ?>;
                                font-size: <?= $fontSize ?>px;
                                color: <?= e($fontColor) ?>;
                                text-align: <?= e($alignment) ?>;
                                width: 100%;
                                box-sizing: border-box;
                             ">

                            <?= e($quote['quote_text'] ?? '') ?>

                        </div>


                        <div class="quote-line"></div>


                        <!-- Author -->
                        <div class="quote-author"
                             style="
                                color: <?= e($fontColor) ?>;
                                text-align: <?= e($alignment) ?>;
                             ">

                            <?= !empty($quote['author'])
                                ? '— ' . e($quote['author'])
                                : '— Unknown Author' ?>

                            <div class="quote-date">

                                <i class="bi bi-calendar3 me-1"></i>

                                <?= !empty($quote['created_at'])
                                    ? date('M d, Y', strtotime($quote['created_at']))
                                    : 'Recently created' ?>

                            </div>

                        </div>


                        <!-- Actions -->
                        <div class="card-actions">

                            <a href="studio.php?id=<?= (int)$quote['id'] ?>"
                               class="edit-btn">

                                <i class="bi bi-pencil me-1"></i>
                                Edit

                            </a>


                            <form method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this quote?');">

                                <input
                                    type="hidden"
                                    name="delete_quote_id"
                                    value="<?= (int)$quote['id'] ?>"
                                >

                                <button type="submit" class="delete-btn">

                                    <i class="bi bi-trash me-1"></i>
                                    Delete

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <!-- Empty State -->
        <div class="empty-state">

            <i class="bi bi-journal-heart"></i>

            <h4>
                <?= $search !== ''
                    ? 'No Quotes Found'
                    : 'Your Collection is Waiting' ?>
            </h4>

            <p>
                <?= $search !== ''
                    ? 'Try another keyword or author name.'
                    : 'You have not created any quote cards yet. Start your collection today.' ?>
            </p>

            <?php if ($search === ''): ?>

                <a href="studio.php" class="create-btn">
                    <i class="bi bi-plus-circle me-1"></i>
                    Create Your First Quote
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>


<!-- Footer -->
<footer>

    <strong>QUOTEIFY</strong>

    <p class="mb-0 mt-1">
        Your words. Your style. Your story.
        &copy; <?= date('Y') ?> Quoteify.
    </p>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
