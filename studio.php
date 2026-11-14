
<?php
require_once 'includes/bootstrap.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$error = '';
$editing = false;
$quoteId = 0;

$quote = [
    'quote_text' => '',
    'author' => '',
    'background_color' => '#f5e6d3',
    'font_color' => '#3d3028',
    'font_family' => 'Georgia',
    'font_size' => 28,
    'border_style' => 'solid',
    'layout_style' => 'classic',
    'visibility' => 'Private',
    'text_alignment' => 'center'
];

// LOAD QUOTE FOR EDITING
if (isset($_GET['id'])) {
    $quoteId = (int) $_GET['id'];

    if ($quoteId > 0) {
        $existingQuote = $cards->findOwnedById($quoteId, $userId);

        if (!$existingQuote) {
            exit('Quote not found or you do not have permission to edit it.');
        }

        $quote = array_merge($quote, $existingQuote);
        $editing = true;
    }
}

// SAVE OR UPDATE QUOTE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quote = array_merge($quote, $_POST);

    $quoteId = (int) ($_POST['quote_id'] ?? 0);
    $editing = $quoteId > 0;

    try {
        if ($editing) {
            $existingQuote = $cards->findOwnedById($quoteId, $userId);

            if (!$existingQuote) {
                throw new RuntimeException('Quote not found or access denied.');
            }

            $cards->update($quoteId, $userId, $_POST);

            header('Location: my-quotes.php?updated=1');
            exit;
        } else {
            $cards->create($userId, $_POST);

            header('Location: my-quotes.php?created=1');
            exit;
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'Unable to save your quote. Please check your information.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $editing ? 'Edit Quote' : 'Create Quote' ?> | Quoteify</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
        }

        body {
            background: var(--cream);
            color: var(--dark);
            font-family: 'DM Sans', sans-serif;
        }

        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--line);
            padding: 15px 5%;
        }

        .navbar-brand {
            color: var(--brown) !important;
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

        .page-title,
        .section-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .subtitle {
            color: var(--muted);
            font-size: 13px;
        }

        .editor-card,
        .preview-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 17px;
            padding: 25px;
            height: 100%;
        }

        .section-title {
            font-size: 21px;
            margin-bottom: 20px;
        }

        .form-label {
            color: var(--brown);
            font-size: 12px;
            font-weight: 700;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--line);
            border-radius: 9px;
            padding: 11px 13px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brown);
            box-shadow: 0 0 0 3px rgba(118, 86, 64, .10);
        }

        textarea {
            resize: vertical;
        }

        .color-input {
            width: 100%;
            height: 45px;
            padding: 5px;
            cursor: pointer;
        }

        .btn-brown {
            background: var(--brown);
            color: white;
            border: 1px solid var(--brown);
            border-radius: 9px;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-brown:hover {
            background: #5e4432;
            color: white;
        }

        .btn-outline-brown {
            background: transparent;
            color: var(--brown);
            border: 1px solid var(--brown);
            border-radius: 9px;
            padding: 11px 20px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-outline-brown:hover {
            background: var(--brown);
            color: white;
        }

        .quote-preview {
            min-height: 350px;
            padding: 35px 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 12px;
            border: 2px solid var(--brown);
            background: #f5e6d3;
            transition: .2s;
            overflow-wrap: anywhere;
        }

        .preview-quote {
            font-family: Georgia, serif;
            font-size: 28px;
            color: #3d3028;
            line-height: 1.5;
            white-space: pre-wrap;
            width: 100%;
        }

        .preview-author {
            margin-top: 22px;
            font-size: 13px;
            opacity: .8;
        }

        .preview-label {
            font-size: 11px;
            letter-spacing: 2px;
            margin-bottom: 18px;
            opacity: .7;
        }

        .color-help {
            font-size: 11px;
            color: var(--muted);
        }

        .alignment-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .alignment-button {
            flex: 1;
            min-width: 55px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
            color: var(--brown);
            padding: 10px 5px;
            font-size: 12px;
        }

        .alignment-button.active {
            background: var(--brown);
            color: white;
            border-color: var(--brown);
        }

        @media(max-width: 768px) {
            .main-container {
                padding: 25px 15px;
            }

            .editor-card,
            .preview-card {
                padding: 20px;
            }

            .quote-preview {
                min-height: 280px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php">Quoteify.</a>

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
                    <a class="nav-link" href="public-quotes.php">Public Quotes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="studio.php">Create Quote</a>
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


<main class="main-container">

    <div class="mb-4">
        <div class="text-uppercase small text-secondary">
            QUOTEIFY / STUDIO
        </div>

        <h1 class="page-title mt-2">
            <?= $editing ? 'Edit Your Quote' : 'Create Your Quote' ?>
        </h1>

        <p class="subtitle">
            Write your own words and customize your quote card.
        </p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>


    <form
        method="POST"
        action="studio.php<?= $editing ? '?id=' . $quoteId : '' ?>"
    >

        <input
            type="hidden"
            name="quote_id"
            value="<?= $editing ? $quoteId : 0 ?>"
        >

        <div class="row g-4">

            <!-- QUOTE EDITOR -->

            <div class="col-12 col-lg-6">

                <section class="editor-card">

                    <h3 class="section-title">Quote Details</h3>

                    <div class="mb-3">
                        <label class="form-label" for="quote_text">
                            Your Quote *
                        </label>

                        <textarea
                            class="form-control"
                            name="quote_text"
                            id="quote_text"
                            rows="5"
                            maxlength="1000"
                            placeholder="Write your own meaningful words..."
                            required
                        ><?= e($quote['quote_text']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="author">Author</label>

                        <input
                            type="text"
                            class="form-control"
                            name="author"
                            id="author"
                            maxlength="150"
                            value="<?= e($quote['author']) ?>"
                            placeholder="Enter author name"
                        >
                    </div>


                    <h3 class="section-title">Customize Design</h3>

                    <!-- BACKGROUND COLOR -->

                    <div class="mb-3">
                        <label class="form-label" for="background_color">
                            Background Color
                        </label>

                        <input
                            type="color"
                            class="form-control form-control-color color-input"
                            name="background_color"
                            id="background_color"
                            value="<?= e($quote['background_color']) ?>"
                        >
                    </div>


                    <!-- FONT COLOR -->

                    <div class="mb-3">
                        <label class="form-label" for="font_color">
                            Font Color
                        </label>

                        <input
                            type="color"
                            class="form-control form-control-color color-input"
                            name="font_color"
                            id="font_color"
                            value="<?= e($quote['font_color']) ?>"
                        >

                        <small class="color-help">
                            Choose the color of your quote text.
                        </small>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php
                            $fontColors = [
                                '#3D3028',
                                '#765640',
                                '#A65D45',
                                '#8B5E83',
                                '#526B5D',
                                '#34495E',
                                '#B07D35',
                                '#FFFFFF'
                            ];
                            ?>

                            <?php foreach ($fontColors as $color): ?>
                                <button
                                    type="button"
                                    class="rounded-circle border"
                                    style="background:<?= e($color) ?>;width:27px;height:27px;"
                                    data-font-color="<?= e($color) ?>"
                                    title="<?= e($color) ?>"
                                    aria-label="Choose font color <?= e($color) ?>"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                    </div>


                    <!-- FONT FAMILY -->

                    <div class="mb-3">
                        <label class="form-label" for="font_family">
                            Font Style
                        </label>

                        <select
                            class="form-select"
                            name="font_family"
                            id="font_family"
                        >
                            <?php
                            $fonts = [
                                'Georgia',
                                'Arial',
                                'Times New Roman',
                                'Verdana',
                                'Courier New',
                                'Trebuchet MS'
                            ];
                            ?>

                            <?php foreach ($fonts as $font): ?>
                                <option
                                    value="<?= e($font) ?>"
                                    <?= $quote['font_family'] === $font ? 'selected' : '' ?>
                                >
                                    <?= e($font) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- FONT SIZE -->

                    <div class="mb-3">
                        <label class="form-label" for="font_size">
                            Font Size
                        </label>

                        <select
                            class="form-select"
                            name="font_size"
                            id="font_size"
                        >
                            <?php foreach ([14, 18, 22, 26, 28, 32, 36, 42, 48, 54, 60] as $size): ?>
                                <option
                                    value="<?= $size ?>"
                                    <?= (int)$quote['font_size'] === $size ? 'selected' : '' ?>
                                >
                                    <?= $size ?> px
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- BORDER STYLE -->

                    <div class="mb-3">
                        <label class="form-label" for="border_style">
                            Border Style
                        </label>

                        <select
                            class="form-select"
                            name="border_style"
                            id="border_style"
                        >
                            <?php foreach (['none', 'solid', 'double', 'dashed', 'dotted'] as $border): ?>
                                <option
                                    value="<?= e($border) ?>"
                                    <?= $quote['border_style'] === $border ? 'selected' : '' ?>
                                >
                                    <?= ucfirst($border) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- CARD LAYOUT -->

                    <div class="mb-3">
                        <label class="form-label" for="layout_style">
                            Card Layout
                        </label>

                        <select
                            class="form-select"
                            name="layout_style"
                            id="layout_style"
                        >
                            <?php foreach (['classic', 'minimal', 'framed'] as $layout): ?>
                                <option
                                    value="<?= e($layout) ?>"
                                    <?= $quote['layout_style'] === $layout ? 'selected' : '' ?>
                                >
                                    <?= ucfirst($layout) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- TEXT ALIGNMENT -->

                    <div class="mb-3">
                        <label class="form-label" for="text_alignment">
                            Text Alignment
                        </label>

                        <select
                            class="form-select"
                            name="text_alignment"
                            id="text_alignment"
                        >
                            <option value="left"
                                <?= $quote['text_alignment'] === 'left' ? 'selected' : '' ?>>
                                Left
                            </option>

                            <option value="center"
                                <?= $quote['text_alignment'] === 'center' ? 'selected' : '' ?>>
                                Center
                            </option>

                            <option value="right"
                                <?= $quote['text_alignment'] === 'right' ? 'selected' : '' ?>>
                                Right
                            </option>

                            <option value="justify"
                                <?= $quote['text_alignment'] === 'justify' ? 'selected' : '' ?>>
                                Justify
                            </option>
                        </select>

                        <div class="alignment-buttons mt-2">

                            <button type="button" class="alignment-button" data-align="left" title="Left">
                                <strong>Left</strong>
                            </button>

                            <button type="button" class="alignment-button" data-align="center" title="Center">
                                <strong>Center</strong>
                            </button>

                            <button type="button" class="alignment-button" data-align="right" title="Right">
                                <strong>Right</strong>
                            </button>

                            <button type="button" class="alignment-button" data-align="justify" title="Justify">
                                <strong>Justify</strong>
                            </button>

                        </div>
                    </div>


                    <!-- VISIBILITY -->

                    <div class="mb-4">
                        <label class="form-label" for="visibility">
                            Quote Visibility
                        </label>

                        <select
                            class="form-select"
                            name="visibility"
                            id="visibility"
                        >
                            <option
                                value="Private"
                                <?= $quote['visibility'] === 'Private' ? 'selected' : '' ?>
                            >
                                Private - Only you can see it
                            </option>

                            <option
                                value="Public"
                                <?= $quote['visibility'] === 'Public' ? 'selected' : '' ?>
                            >
                                Public - Share with everyone
                            </option>
                        </select>
                    </div>


                    <div class="d-flex flex-wrap gap-2">

                        <button type="submit" class="btn-brown flex-grow-1">
                            <?= $editing ? 'Update Quote' : 'Save Quote' ?>
                        </button>

                        <a href="my-quotes.php" class="btn-outline-brown text-center">
                            Cancel
                        </a>

                    </div>

                </section>
            </div>


            <!-- LIVE PREVIEW -->

            <div class="col-12 col-lg-6">

                <section class="preview-card">

                    <h3 class="section-title">Live Preview</h3>

                    <p class="subtitle">
                        Your design updates as you customize it.
                    </p>

                    <div class="quote-preview" id="quotePreview">

                        <div class="preview-label">
                            A QUOTE TO REMEMBER
                        </div>

                        <div class="preview-quote" id="previewQuote"><?= e($quote['quote_text'] ?: 'Your quote will appear here.') ?></div>

                        <div class="preview-author" id="previewAuthor">
                            <?= e($quote['author'] ? '— ' . $quote['author'] : '') ?>
                        </div>

                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-secondary">Selected Font Color</small>
                            <small id="colorCode"><?= e($quote['font_color']) ?></small>
                        </div>

                        <div
                            id="colorSample"
                            style="height:12px;border-radius:20px;background:<?= e($quote['font_color']) ?>;"
                        ></div>
                    </div>

                    <p class="subtitle mt-4 mb-0">
                        Tip: Choose a font color that contrasts with your background.
                    </p>

                </section>
            </div>

        </div>
    </form>

</main>


<script>
const quoteInput = document.getElementById('quote_text');
const authorInput = document.getElementById('author');

const backgroundInput = document.getElementById('background_color');
const fontColorInput = document.getElementById('font_color');
const fontFamilyInput = document.getElementById('font_family');
const fontSizeInput = document.getElementById('font_size');
const borderInput = document.getElementById('border_style');
const layoutInput = document.getElementById('layout_style');
const alignmentInput = document.getElementById('text_alignment');

const preview = document.getElementById('quotePreview');
const previewQuote = document.getElementById('previewQuote');
const previewAuthor = document.getElementById('previewAuthor');

const colorCode = document.getElementById('colorCode');
const colorSample = document.getElementById('colorSample');

function updatePreview() {

    previewQuote.textContent =
        quoteInput.value || 'Your quote will appear here.';

    previewAuthor.textContent =
        authorInput.value ? '— ' + authorInput.value : '';

    preview.style.backgroundColor = backgroundInput.value;

    previewQuote.style.color = fontColorInput.value;
    previewQuote.style.fontFamily = fontFamilyInput.value;
    previewQuote.style.fontSize = fontSizeInput.value + 'px';

    // TEXT ALIGNMENT
    previewQuote.style.textAlign = alignmentInput.value;

    // FONT COLOR
    previewAuthor.style.color = fontColorInput.value;

    // BORDER
    preview.style.borderStyle = borderInput.value;
    preview.style.borderColor = fontColorInput.value;
    preview.style.borderWidth =
        borderInput.value === 'none' ? '0' : '2px';

    // LAYOUT
    preview.style.padding =
        layoutInput.value === 'minimal' ? '20px 15px' : '35px 25px';

    preview.style.borderRadius =
        layoutInput.value === 'framed' ? '0' : '12px';

    preview.style.outline =
        layoutInput.value === 'framed'
            ? '5px double ' + fontColorInput.value
            : 'none';

    colorCode.textContent = fontColorInput.value.toUpperCase();
    colorSample.style.backgroundColor = fontColorInput.value;

    // ACTIVE ALIGNMENT BUTTON
    document.querySelectorAll('[data-align]').forEach(function(button) {
        button.classList.toggle(
            'active',
            button.dataset.align === alignmentInput.value
        );
    });
}


// UPDATE PREVIEW WHEN INPUT CHANGES
[
    quoteInput,
    authorInput,
    backgroundInput,
    fontColorInput,
    fontFamilyInput,
    fontSizeInput,
    borderInput,
    layoutInput,
    alignmentInput
].forEach(function(element) {
    element.addEventListener('input', updatePreview);
    element.addEventListener('change', updatePreview);
});


// FONT COLOR PRESETS
document.querySelectorAll('[data-font-color]').forEach(function(button) {
    button.addEventListener('click', function() {
        fontColorInput.value = this.dataset.fontColor;
        updatePreview();
    });
});


// ALIGNMENT BUTTONS
document.querySelectorAll('[data-align]').forEach(function(button) {
    button.addEventListener('click', function() {
        alignmentInput.value = this.dataset.align;
        updatePreview();
    });
});


// INITIAL PREVIEW
updatePreview();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
