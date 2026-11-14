
<?php
require_once 'includes/bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please complete all fields.';
    } elseif (strlen($name) > 120) {
        $error = 'Name must not exceed 120 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $existingUser = $users->findByEmail($email);

            if ($existingUser) {
                $error = 'This email address is already registered.';
            } else {
                $users->setName($name);
                $users->setEmail($email);
                $users->setPassword($password);

                if ($users->create()) {
                    $success = 'Account created successfully! You can now log in.';
                } else {
                    $error = 'Unable to create your account. Please try again.';
                }
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
<title>Register | Quoteify</title>

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

.register-container {
    width: 100%;
    max-width: 460px;
    background: #fffefa;
    border: 1px solid #eee7dd;
    border-radius: 20px;
    padding: 38px 42px;
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
    margin-bottom: 30px;
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
    margin-bottom: 25px;
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
    margin-top: 10px;
    transition: .2s;
}

.btn-brown:hover {
    background: #5e4432;
    color: white;
}

.login-link {
    text-align: center;
    font-size: 13px;
    color: #9b8b7d;
    margin-top: 22px;
}

.login-link a {
    color: #765640;
    font-weight: 700;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

.back-link {
    display: block;
    text-align: center;
    color: #9b8b7d;
    font-size: 12px;
    text-decoration: none;
    margin-top: 18px;
}

.back-link:hover {
    color: #765640;
}

@media(max-width: 480px) {
    .register-container {
        padding: 30px 22px;
    }
}
</style>
</head>

<body>

<div class="register-container">

    <div class="logo">Quoteify.</div>
    <div class="logo-subtitle">Quote & Typography Studio</div>

    <h2>Create Account</h2>
    <p class="description">Join Quoteify and start designing your own quote cards.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 small">
            <?= e($success) ?>
            <a href="login.php" class="alert-link">Login here</a>.
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>

            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                placeholder="Enter your full name"
                value="<?= e($_POST['name'] ?? '') ?>"
                maxlength="120"
                autocomplete="name"
                required
            >
        </div>

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
                autocomplete="email"
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
                    placeholder="Create your password"
                    minlength="8"
                    autocomplete="new-password"
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

            <small class="text-secondary">
                Use at least 8 characters.
            </small>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">
                Confirm Password
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    class="form-control"
                    placeholder="Confirm your password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    onclick="togglePassword('confirm_password', this)"
                    aria-label="Show password"
                    aria-pressed="false"
                >👁</button>
            </div>
        </div>

        <button type="submit" class="btn btn-brown">
            Create My Account
        </button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="login.php">Login here</a>
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
