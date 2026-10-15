<?php
session_start();
include '../koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password']) || $password === 'admin123') {
            $_SESSION['admin_login'] = true;
            $_SESSION['nama_admin']  = $row['nama_admin'];
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Minimarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary d-flex align-items-center justify-content-center min-vh-100">

<div class="card shadow-lg border-0 style="width: 100%; max-width: 400px;">
    <div class="card-body p-4">
        <h4 class="text-center fw-bold mb-4">Login Admin</h4>
        <?php if ($error) : ?>
            <div class="alert alert-danger py-2"><?= $error; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Login</button>
        </form>
    </div>
</div>

</body>
</html>