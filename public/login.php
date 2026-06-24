<?php
require_once __DIR__ . '/../config/auth-functions.php';
auth_boot();

// Already logged in? go to dashboard.
if (auth_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (auth_attempt($username, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Calapan City FMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#ea580c', accent: '#1d4ed8' } } } };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-orange-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-orange-600 px-8 py-7 text-center text-white">
                <i class="fas fa-fish text-4xl mb-2"></i>
                <h1 class="text-xl font-bold leading-tight">Fisheries Management Office</h1>
                <p class="text-sm text-orange-100">City Government of Calapan</p>
            </div>
            <form method="POST" action="login.php" class="px-8 py-8 space-y-5">
                <h2 class="text-lg font-semibold text-gray-700 text-center">Sign in to continue</h2>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i><span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1" for="username">Username</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg">
                            <i class="fas fa-user"></i>
                        </span>
                        <input id="username" name="username" type="text" required autofocus autocomplete="username"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1" for="password">Password</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-primary hover:bg-orange-700 text-white font-semibold py-2.5 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-right-to-bracket"></i> Sign In
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-gray-400 mt-4">Fisherfolk ID Database &middot; Authorized users only</p>
    </div>
</body>
</html>
