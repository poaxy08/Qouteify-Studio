
<?php
require_once 'includes/bootstrap.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$user = $users->findById($userId);

if (!$user) {
    header('Location: logout.php');
    exit;
}

$initial = strtoupper(substr($user['name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | Quoteify</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f8f5ef;
            color: #40352d;
            font-family: 'DM Sans', sans-serif;
        }

        .profile-container {
            max-width: 750px;
            margin: 55px auto;
        }

        .profile-card {
            background: #fffefa;
            border: 1px solid #eae2d8;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(70, 50, 30, .05);
        }

        .profile-header {
            background: #eee3d6;
            padding: 40px 20px;
            text-align: center;
        }

        .profile-picture {
            width: 95px;
            height: 95px;
            background: #80634b;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
            font-size: 42px;
            margin: auto auto 15px;
            border: 5px solid #fffefa;
        }

        .profile-header h2 {
            font-family: 'Playfair Display', serif;
        }

        .profile-header p {
            color: #80634b;
            margin: 0;
        }

        .profile-details {
            padding: 30px;
        }

        .detail-item {
            padding: 17px 0;
            border-bottom: 1px solid #eee7dc;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #988b7e;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: #40352d;
        }

        .profile-icon {
            color: #80634b;
            margin-right: 8px;
        }

        .back-btn {
            color: #80634b;
            text-decoration: none;
            font-size: 13px;
        }

        .back-btn:hover {
            color: #40352d;
        }
    </style>
</head>

<body>

<?php require_once 'includes/navbar.php'; ?>

<div class="container profile-container">

    <a href="dashboard.php" class="back-btn">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Dashboard
    </a>

    <div class="profile-card mt-3">

        <div class="profile-header">

            <div class="profile-picture">
                <?= e($initial) ?>
            </div>

            <h2>
                <?= e($user['name']) ?>
            </h2>

            <p>
                <i class="bi bi-flower1 me-1"></i>
                Quoteify Member
            </p>

        </div>

        <div class="profile-details">

            <h5 class="mb-3"
                style="font-family: Georgia, serif;">

                Account Information

            </h5>

            <div class="detail-item">

                <div class="detail-label">
                    <i class="bi bi-person profile-icon"></i>
                    Full Name
                </div>

                <div class="detail-value">
                    <?= e($user['name']) ?>
                </div>

            </div>

            <div class="detail-item">

                <div class="detail-label">
                    <i class="bi bi-envelope profile-icon"></i>
                    Email Address
                </div>

                <div class="detail-value">
                    <?= e($user['email']) ?>
                </div>

            </div>

            <div class="detail-item">

                <div class="detail-label">
                    <i class="bi bi-shield-check profile-icon"></i>
                    Account Role
                </div>

                <div class="detail-value">
                    <?= e($user['role']) ?>
                </div>

            </div>

            <div class="detail-item">

                <div class="detail-label">
                    <i class="bi bi-calendar3 profile-icon"></i>
                    Member Since
                </div>

                <div class="detail-value">
                    <?= !empty($user['created_at'])
                        ? date('F d, Y', strtotime($user['created_at']))
                        : 'Not available' ?>
                </div>

            </div>

            <div class="text-center mt-4">

                <a href="my-quotes.php"
                   class="btn rounded-pill px-4"
                   style="background:#80634b;color:white;">

                    <i class="bi bi-journal-heart me-1"></i>
                    View My Quotes

                </a>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
