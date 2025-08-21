<?php
// register.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session

require 'config.php'; // Make sure config.php is in the same folder

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');

    if (!$name || !$email || !$password || !$role) {
        $errorMessage = 'All fields are required';
    } elseif (!$email) {
        $errorMessage = 'Invalid email format';
    } else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {
                $errorMessage = 'Email already exists';
            } else {
                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

                // Get the new user ID
                $userId = $pdo->lastInsertId();

                // Log the user in by setting session
                $_SESSION['user_id'] = $userId; // Use 'user_id' since your feedback.php uses it
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                $successMessage = 'Registration successful! Redirecting...';

                // Redirect based on role
                if ($role === 'user') header("Location: profile.php");
                elseif ($role === 'dietitian') header("Location: dietitian_dashboard.php");
                elseif ($role === 'admin') header("Location: admin_dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $errorMessage = 'Server error: ' . $e->getMessage();
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
    @theme {
      --color-clifford: #da373d;
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet" />
</head>

<body class="min-h-screen bg-gradient-to-b from-indigo-300 to-white flex flex-col items-center justify-center px-4 py-8">

  <!-- Top Header -->
  <div class="absolute top-4 left-6 sm:left-8 sm:top-8">
    <a href="index.php">
      <button class="bg-opacity-10 text-white font-bold px-4 py-2 rounded-xl border border-white drop-shadow-xl">
        Back
      </button>
    </a>
  </div>

  <!-- Logo -->
  <div class="mb-4 w-full max-w-xl px-4 sm:px-0">
    <h1 style="font-family: 'Dancing Script', cursive;" class="text-center pt-6 text-3xl sm:text-5xl font-bold text-shadow-lg text-white">
      Fit Life Planner
    </h1>
  </div>

  <!-- Card -->
  <div class="bg-white w-full max-w-md rounded-3xl shadow-lg px-6 py-8 sm:px-8 sm:py-10">
    <h2 class="text-xl sm:text-2xl font-semibold text-center mb-6">Create An Account</h2>

    <!-- Display messages -->
    <?php if ($successMessage): ?>
      <p class="text-green-600 text-center mb-4"><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
      <p class="text-red-600 text-center mb-4"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Form -->
    <form class="space-y-4" method="POST" action="register.php">
      <input type="text" name="name" placeholder="Name" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300" required />
      <input type="email" name="email" placeholder="Email Address" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300" required />
      <input type="password" name="password" placeholder="Password" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300" required />
      <select name="role" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-300 text-gray-700" required>
        <option value="" disabled selected>Select Role</option>
        <option value="user">User</option>
        <option value="dietitian">Dietitian</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit" class="block text-center w-full bg-indigo-400 text-white font-semibold py-3 rounded-xl hover:bg-indigo-500 transition text-sm sm:text-base">
        Create An Account
      </button>
    </form>
  </div>

  <!-- Already have account text -->
  <div class="text-center mt-4 text-sm text-gray-600">
    Already have an account?
    <a href="login.php" class="text-indigo-500 font-semibold hover:underline">Sign In</a>
  </div>

</body>
</html>
