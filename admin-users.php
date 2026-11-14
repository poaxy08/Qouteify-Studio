
<?php
require_once 'includes/bootstrap.php';
requireAdmin();

$message = '';
$error = '';

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = (int)($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'Member';

    if ($id <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid name and email.';
    } elseif (!in_array($role, ['Admin', 'Member'], true)) {
        $error = 'Invalid user role.';
    } else {
        try {
            $stmt = $db->prepare(
                "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id"
            );
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':id' => $id
            ]);
            $message = 'User information updated successfully.';
        } catch (PDOException $e) {
            $error = 'Unable to update user. The email may already be in use.';
        }
    }
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)($_POST['user_id'] ?? 0);

    if ($id === (int)$_SESSION['user_id']) {
        $error = 'You cannot delete your own administrator account.';
    } elseif ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message = 'User deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Unable to delete this user.';
        }
    }
}

// Get users
$stmt = $db->query(
    "SELECT id, name, email, role, created_at
     FROM users ORDER BY created_at DESC"
);
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$totalUsers = count($allUsers);
$totalAdmins = count(array_filter($allUsers, fn($u) => $u['role'] === 'Admin'));
$totalMembers = count(array_filter($allUsers, fn($u) => $u['role'] === 'Member'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Users | Quoteify</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --cream: #f8f5ef;
    --white: #fffefa;
    --brown: #765640;
    --dark: #3d3028;
    --muted: #9b8b7d;
    --line: #eee7dd;
}
* { box-sizing: border-box; }

body {
    margin: 0;
    background: var(--cream);
    color: var(--dark);
    font-family: 'DM Sans', sans-serif;
}

.sidebar {
    width: 250px;
    min-height: 100vh;
    background: var(--white);
    border-right: 1px solid var(--line);
    padding: 30px 20px;
    position: fixed;
    top: 0;
    left: 0;
}

.brand {
    font-family: 'Playfair Display', serif;
    font-size: 27px;
    font-weight: 700;
    color: var(--brown);
}

.brand-sub {
    color: var(--muted);
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 45px;
}

.nav-label {
    font-size: 10px;
    color: #ad9d8e;
    letter-spacing: 1.5px;
    font-weight: 700;
    margin: 25px 12px 12px;
}

.side-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    margin-bottom: 7px;
    border-radius: 10px;
    color: #76685d;
    text-decoration: none;
    font-size: 14px;
    transition: .2s;
}

.side-link:hover, .side-link.active {
    background: #f1e9df;
    color: var(--brown);
}

.side-link.active { font-weight: 700; }

.admin-profile {
    position: absolute;
    bottom: 25px;
    left: 20px;
    right: 20px;
    border-top: 1px solid var(--line);
    padding-top: 18px;
}

.avatar {
    width: 39px;
    height: 39px;
    background: #e9d9c7;
    color: var(--brown);
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 700;
}

.main {
    margin-left: 250px;
    padding: 38px 45px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.eyebrow {
    color: var(--muted);
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

h1, h2, h3 {
    font-family: 'Playfair Display', serif;
}

h1 { font-size: 34px; margin: 5px 0; }

.subtitle {
    color: var(--muted);
    font-size: 14px;
}

.date-pill {
    background: white;
    border: 1px solid var(--line);
    padding: 10px 16px;
    border-radius: 30px;
    color: var(--brown);
    font-size: 13px;
}

.welcome {
    background: #e9dfd2;
    border-radius: 18px;
    padding: 25px 30px;
    margin-bottom: 25px;
}

.welcome h2 {
    font-size: 24px;
    margin: 5px 0;
}

.welcome p {
    color: #786758;
    margin: 0;
    font-size: 13px;
}

.stat-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 15px;
    padding: 20px;
    height: 100%;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: #f2e9df;
    color: var(--brown);
    border-radius: 12px;
    display: grid;
    place-items: center;
    margin-bottom: 14px;
    font-size: 19px;
}

.stat-number {
    font-size: 27px;
    font-weight: 700;
}

.stat-label {
    color: var(--muted);
    font-size: 12px;
}

.table-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 17px;
    padding: 24px;
}

.table-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.table-heading h3 {
    font-size: 21px;
    margin: 0;
}

.search-box, .filter-box {
    background: #faf8f4;
    border: 1px solid var(--line);
    border-radius: 9px;
    padding: 10px 13px;
    font-size: 13px;
    color: var(--dark);
}

.search-box {
    min-width: 220px;
}

.search-box:focus, .filter-box:focus {
    outline: none;
    border-color: #b99a7c;
    box-shadow: 0 0 0 3px rgba(185,154,124,.12);
}

.table {
    --bs-table-bg: transparent;
}

.table th {
    color: #a08f80;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom-color: var(--line);
    padding: 14px 10px;
}

.table td {
    color: #65564b;
    font-size: 13px;
    vertical-align: middle;
    padding: 15px 10px;
    border-bottom-color: #f2ede6;
}

.table tr:last-child td {
    border-bottom: 0;
}

.user-avatar {
    width: 35px;
    height: 35px;
    display: inline-grid;
    place-items: center;
    border-radius: 50%;
    background: #e9d9c7;
    color: var(--brown);
    font-weight: 700;
    margin-right: 9px;
}

.role-badge {
    display: inline-block;
    padding: 6px 11px;
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

.action-btn {
    border: 1px solid var(--line);
    background: white;
    border-radius: 8px;
    padding: 7px 11px;
    color: var(--brown);
    font-size: 12px;
}

.action-btn:hover {
    background: #f1e9df;
}

.delete-btn {
    color: #a65f55;
}

.delete-btn:hover {
    background: #f8e9e5;
}

.modal-content {
    border: none;
    border-radius: 17px;
    background: var(--white);
}

.modal-header, .modal-footer {
    border-color: var(--line);
}

.modal-title {
    font-family: 'Playfair Display', serif;
}

.form-label {
    color: #786758;
    font-size: 12px;
    font-weight: 600;
}

.form-control, .form-select {
    border-color: var(--line);
    background: #faf8f4;
    padding: 11px;
    font-size: 13px;
}

.form-control:focus, .form-select:focus {
    border-color: #b99a7c;
    box-shadow: 0 0 0 3px rgba(185,154,124,.12);
}

.btn-brown {
    background: var(--brown);
    color: white;
    border-radius: 9px;
    padding: 10px 18px;
    font-size: 13px;
}

.btn-brown:hover {
    background: #5e4432;
    color: white;
}

.empty-state {
    color: var(--muted);
    text-align: center;
    padding: 30px;
}

@media(max-width: 900px) {
    .sidebar {
        width: 75px;
        padding: 25px 10px;
    }

    .brand {
        font-size: 17px;
        text-align: center;
    }

    .brand-sub, .nav-label, .side-link span,
    .admin-profile .profile-info {
        display: none;
    }

    .side-link {
        justify-content: center;
        font-size: 20px;
    }

    .admin-profile {
        left: 10px;
        right: 10px;
    }

    .main {
        margin-left: 75px;
        padding: 25px 18px;
    }
}

@media(max-width: 576px) {
    .main { padding: 20px 12px; }
    .topbar { align-items: flex-start; flex-direction: column; gap: 12px; }
    .table-card { padding: 15px; }
    .table-heading { align-items: flex-start; flex-direction: column; }
    .search-box { min-width: 100%; }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="brand">Quoteify.</div>
    <div class="brand-sub">Quote & Typography Studio</div>

    <div class="nav-label">WORKSPACE</div>
    <a href="dashboard.php" class="side-link">
        <span>⌂</span><span>Dashboard</span>
    </a>
    <a href="admin-users.php" class="side-link active">
        <span>♙</span><span>Manage Users</span>
    </a>
    <a href="public-quotes.php" class="side-link">
        <span>▤</span><span>Public Quotes</span>
    </a>

    <div class="nav-label">ACCOUNT</div>
    <a href="profile.php" class="side-link">
        <span>◎</span><span>My Profile</span>
    </a>
    <a href="logout.php" class="side-link">
        <span>↪</span><span>Logout</span>
    </a>

    <div class="admin-profile d-flex align-items-center gap-2">
        <div class="avatar">
            <?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="profile-info">
            <div class="fw-bold small"><?= e($_SESSION['name'] ?? 'Admin') ?></div>
            <div class="text-secondary" style="font-size:11px">Administrator</div>
        </div>
    </div>
</aside>

<main class="main">

    <div class="topbar">
        <div>
            <div class="eyebrow">Quoteify / Workspace</div>
            <h1>Manage Users</h1>
            <div class="subtitle">Keep your creative community organized.</div>
        </div>
        <div class="date-pill">✦ <?= date('F d, Y') ?></div>
    </div>

    <section class="welcome">
        <div class="eyebrow">COMMUNITY MANAGEMENT</div>
        <h2>People make Quoteify special.</h2>
        <p>View account details, manage user roles, and keep track of your members.</p>
    </section>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon">♙</div>
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon">✧</div>
                <div class="stat-number"><?= $totalAdmins ?></div>
                <div class="stat-label">Administrators</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon">♡</div>
                <div class="stat-number"><?= $totalMembers ?></div>
                <div class="stat-label">Members</div>
            </div>
        </div>
    </div>

    <section class="table-card">
        <div class="table-heading">
            <div>
                <h3>Community Members</h3>
                <div class="subtitle mt-1">A list of all registered accounts.</div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <input type="text" id="searchUser" class="search-box"
                       placeholder="Search name or email...">

                <select id="roleFilter" class="filter-box">
                    <option value="">All Roles</option>
                    <option value="Admin">Admin</option>
                    <option value="Member">Member</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Date Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($allUsers as $person): ?>
                    <tr class="user-row">
                        <td>
                            <span class="user-avatar">
                                <?= strtoupper(substr($person['name'], 0, 1)) ?>
                            </span>
                            <span class="fw-semibold"><?= e($person['name']) ?></span>
                        </td>

                        <td><?= e($person['email']) ?></td>

                        <td>
                            <span class="role-badge <?= $person['role'] === 'Admin' ? 'role-admin' : 'role-member' ?>">
                                <?= e($person['role']) ?>
                            </span>
                        </td>

                        <td><?= date('M d, Y', strtotime($person['created_at'])) ?></td>

                        <td>
                            <button type="button" class="action-btn edit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= (int)$person['id'] ?>"
                                data-name="<?= e($person['name']) ?>"
                                data-email="<?= e($person['email']) ?>"
                                data-role="<?= e($person['role']) ?>">
                                Edit
                            </button>

                            <?php if ((int)$person['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this user? Their quote cards will also be deleted.');">
                                    <input type="hidden" name="user_id"
                                           value="<?= (int)$person['id'] ?>">
                                    <button type="submit" name="delete_user"
                                            class="action-btn delete-btn">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$allUsers): ?>
                    <tr>
                        <td colspan="5" class="empty-state">No registered users yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div id="noResults" class="empty-state" style="display:none;">
                No matching users found.
            </div>
        </div>
    </section>

    <div class="text-center mt-4 text-secondary" style="font-size:11px">
        © <?= date('Y') ?> Quoteify Studio · Made for creativity.
    </div>

</main>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Details</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editId">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="editName"
                               class="form-control" maxlength="120" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="editEmail"
                               class="form-control" maxlength="160" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">User Role</label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="Member">Member</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user"
                            class="btn btn-brown">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Fill edit modal
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editEmail').value = this.dataset.email;
        document.getElementById('editRole').value = this.dataset.role;
    });
});

// Search and filter users
const searchInput = document.getElementById('searchUser');
const roleFilter = document.getElementById('roleFilter');
const rows = document.querySelectorAll('.user-row');
const noResults = document.getElementById('noResults');

function filterUsers() {
    const search = searchInput.value.toLowerCase();
    const role = roleFilter.value;
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowRole = row.querySelector('.role-badge').innerText.trim();

        const matchesSearch = text.includes(search);
        const matchesRole = !role || rowRole === role;

        if (matchesSearch && matchesRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

searchInput.addEventListener('input', filterUsers);
roleFilter.addEventListener('change', filterUsers);
</script>

</body>
</html>
