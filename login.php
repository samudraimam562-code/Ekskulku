
<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Periksa username dan password di tabel users
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM users WHERE username = ? AND password = ?"
    );

    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($user) {
        session_regenerate_id(true);

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: murid.php");
        }

        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EkskulKu</title>
    <link rel="icon" href="images.png">
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="login-icon">🎓</div>

        <h2>Login</h2>
        <p class="subtitle">
            Sistem Pendaftaran Ekstrakurikuler Sekolah
        </p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" name="login" class="btn-primary">
                Masuk ke Akun →
            </button>
        </form>
    </div>
</body>
</html>
