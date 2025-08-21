<?php
// login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // database connection

session_start();

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (!$email || !$password || !$role) {
        $error = "All fields are required.";
    } else {
        // Check user in database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // login success, store session if needed
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($role === 'user') header("Location: profile.php");
            elseif ($role === 'dietitian') header("Location: dietitian_dashboard.html");
            elseif ($role === 'admin') header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid email, password, or role.";
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Fit Life Planner</title>
  <link rel="icon" type="image/x-icon" href="heart-rate.png">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style type="text/tailwindcss">
    @theme { --color-clifford: #da373d; }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap"
    rel="stylesheet"
  />
</head>

<body class="min-h-screen bg-gradient-to-b from-indigo-300 to-white flex flex-col items-center justify-center px-4 py-8">

  <!-- Back Button -->
  <div class="absolute left-4 top-12 sm:left-8 sm:top-8">
    <a href="index.html">
      <button class="bg-opacity-10 text-white font-bold px-4 py-2 rounded-xl border border-white drop-shadow-xl">
        Back
      </button>
    </a>
  </div>

  <!-- Logo -->
  <div class="mb-4 w-full max-w-xl px-4 sm:px-0">
    <h1 style="font-family: 'Dancing Script', cursive;"
        class="text-center pt-6 text-3xl sm:text-5xl font-bold text-shadow-lg text-white">
      Fit Life Planner
    </h1>
  </div>

  <!-- Card -->
  <div class="bg-white w-full max-w-md rounded-3xl shadow-lg px-6 py-8 sm:px-8 sm:py-10">
    <h2 class="text-xl sm:text-2xl font-semibold text-center mb-1">Welcome back!</h2>
    <p class="text-gray-500 text-center mb-6 text-sm sm:text-base">Log in with your account</p>

    <!-- Error Message -->
    <?php if (isset($error)): ?>
      <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Form -->
    <form class="space-y-4" method="POST" action="login.php">
      <input type="email" name="email" placeholder="Email Address"
        class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300" required/>

      <div class="relative">
        <input type="password" name="password" placeholder="Password"
          class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300" required/>
        <span class="absolute right-3 top-3.5 text-gray-400"></span>
      </div>

      <!-- Role Selection -->
      <div class="relative">
        <select id="roleSelect" name="role"
          class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300 text-gray-700" required>
          <option value="" disabled selected>Select Role</option>
          <option value="user">User</option>
          <option value="dietitian">Dietitian</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button type="submit"
        class="block text-center w-full bg-indigo-400 text-white font-semibold py-3 rounded-xl hover:bg-indigo-500 transition text-sm sm:text-base">
        Login
      </button>
    </form>
  </div>

  <div class="text-center mt-4 text-sm text-gray-600">
    Don't have an account?
    <a href="register.php" class="text-indigo-500 font-semibold hover:underline">Sign Up</a>
  </div>

</body>
</html>
