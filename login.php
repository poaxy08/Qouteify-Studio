
<?php
require_once 'includes/bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $user = $users->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } catch (Throwable $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Quoteify</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    background: #f8f5ef;
    font-family: 'DM Sans', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 25px;
    color: #3d3028;
}

.login-container {
    width: 100%;
    max-width: 430px;
    background: #fffefa;
    border: 1px solid #eee7dd;
    border-radius: 20px;
    padding: 42px;
    box-shadow: 0 15px 45px rgba(75,55,38,.07);
}

.logo {
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    color: #765640;
}

.logo-subtitle {
    text-align: center;
    font-size: 10px;
    letter-spacing: 2px;
    color: #9b8b7d;
    text-transform: uppercase;
    margin-bottom: 35px;
}

h2 {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    text-align: center;
    margin-bottom: 8px;
}

.description {
    text-align: center;
    color: #9b8b7d;
    font-size: 13px;
    margin-bottom: 28px;
}

.form-label {
    color: #665548;
    font-size: 13px;
    font-weight: 600;
}

.form-control {
    background: #faf8f4;
    border: 1px solid #eee7dd;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
}

.form-control:focus {
    background: white;
    border-color: #b99a7c;
    box-shadow: 0 0 0 3px rgba(185,154,124,.13);
}

.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 48px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #765640;
    cursor: pointer;
    font-size: 19px;
    padding: 5px;
}

.toggle-password:hover {
    color: #3d3028;
}

.btn-brown {
    width: 100%;
    background: #765640;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 13px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 12px;
    transition: .2s;
}

.btn-brown:hover {
    background: #5e4432;
    color: white;
}

.register-link {
    text-align: center;
    font-size: 13px;
    color: #9b8b7d;
    margin-top: 25px;
}

.register-link a {
    color: #765640;
    font-weight: 700;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

.back-link {
    display: block;
    text-align: center;
    color: #9b8b7d;
    font-size: 12px;
    text-decoration: none;
    margin-top: 20px;
}

.back-link:hover {
    color: #765640;
}

@media(max-width: 480px) {
    .login-container {
        padding: 30px 22px;
    }
}
</style>
</head>

<body>

<div class="login-container">

    <div class="logo">Quoteify.</div>
    <div class="logo-subtitle">Quote & Typography Studio</div>

    <h2>Welcome Back!</h2>
    <p class="description">Log in to continue creating beautiful quote cards.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>

            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                placeholder="Enter your email"
                value="<?= e($_POST['email'] ?? '') ?>"
                maxlength="160"
                autocomplete="username"
                required
            >
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>

            <div class="password-wrapper">
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    onclick="togglePassword('password', this)"
                    aria-label="Show password"
                    aria-pressed="false"
                >👁</button>
            </div>
        </div>

        <button type="submit" class="btn btn-brown">
            Login to Quoteify
        </button>

    </form>

    <div class="register-link">
        Don't have an account?
        <a href="register.php">Create an account</a>
    </div>

    <a href="index.php" class="back-link">
        ← Back to Home
    </a>

</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);

    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
        button.setAttribute('aria-label', 'Hide password');
        button.setAttribute('aria-pressed', 'true');
    } else {
        input.type = 'password';
        button.textContent = '👁';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
    }
}
</script>

</body>
</html>
