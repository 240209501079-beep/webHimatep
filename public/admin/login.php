<?php
session_start();
// Require config from private
require_once '../../private/php/config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = trim($_POST['username']);
    $pass_input = trim($_POST['password']);

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$user_input]);
            $user = $stmt->fetch();

            // Verifikasi password hash
            if ($user && password_verify($pass_input, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nama'] = $user['nama_lengkap'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Username atau Password salah.';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    } else {
        $error = 'Koneksi database belum tersedia.';
    }
}

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-light': '#E2E8F0',
                    }
                }
            }
        }
    </script>
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen items-center justify-center font-sans relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-[-100px] left-[-100px] w-96 h-96 bg-himatep-light rounded-full blur-3xl opacity-30"></div>
    <div class="absolute bottom-[-100px] right-[-100px] w-96 h-96 bg-himatep-green rounded-full blur-3xl opacity-20"></div>

    <div class="bg-white p-10 rounded-3xl shadow-2xl max-w-md w-full m-4 relative z-10 border border-gray-200">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-50 rounded-full mb-4 shadow-inner border border-green-100">
                <svg class="w-10 h-10 text-himatep-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Login Admin</h2>
            <p class="text-gray-500 mt-2 text-sm">HIMATEP FIP UNM</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg text-sm" role="alert">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green focus:border-transparent transition-all"
                    required placeholder="Masukkan username">
            </div>
            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green focus:border-transparent transition-all"
                    required placeholder="••••••••">
            </div>
            <div class="flex flex-col gap-4">
                <button type="submit"
                    class="w-full bg-himatep-green hover:bg-blue-800 text-white font-bold py-4 px-8 rounded-xl transition-all shadow-lg transform hover:-translate-y-1">Login</button>
                <a href="../index.php" class="text-center text-sm text-gray-500 hover:text-himatep-green font-medium mt-2 transition-colors">&larr; Kembali ke Beranda</a>
            </div>
        </form>
    </div>
</body>
</html>

