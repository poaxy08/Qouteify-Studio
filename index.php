
<?php

require_once "includes/bootstrap.php";

if (!empty($_SESSION["user_id"])) {

    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Quoteify | Quote & Typography Studio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <a class="navbar-brand brand" href="index.php">
            Quoteify
        </a>

        <div class="ms-auto">

            <a href="login.php"
               class="btn btn-outline-dark me-2">

                Login

            </a>

            <a href="register.php"
               class="btn btn-dark">

                Get Started

            </a>

        </div>

    </div>

</nav>

<section class="container hero">

    <span class="eyebrow">
        YOUR WORDS, YOUR STYLE
    </span>

    <h1>
        Turn meaningful words
        <br>
        into beautiful art.
    </h1>

    <p>
        Create, customize, and save typography quote cards
        with your own colors, fonts, and layouts.
    </p>

    <a href="register.php"
       class="btn btn-dark btn-lg">

        Create Your First Quote

    </a>

</section>

</body>

</html>
