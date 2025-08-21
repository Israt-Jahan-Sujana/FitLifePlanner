<?php
// profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
session_start();

// Get logged-in user ID from session
$user_id = $_SESSION['user_id'] ?? 1; // For testing, default 1

try {
    // Fetch user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']);
    $email        = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password     = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $gender       = $_POST['gender'] ?? '';
    $height       = $_POST['height'] ?? NULL;
    $weight       = $_POST['weight'] ?? NULL;
    $age          = $_POST['age'] ?? NULL;
    $goal_weight  = $_POST['goal_weight'] ?? NULL;
    $fitness_goal = $_POST['fitness_goal'] ?? '';

    if ($name && $email) {
        $updateStmt = $pdo->prepare("UPDATE users SET 
            name = ?, email = ?, password = ?, gender = ?, height = ?, weight = ?, age = ?, goal_weight = ?, fitness_goal = ? 
            WHERE id = ?");
        $updateStmt->execute([
            $name, $email, $password, $gender, $height, $weight, $age, $goal_weight, $fitness_goal, $user_id
        ]);

        header("Location: profile.php"); // refresh page
        exit;
    } else {
        $error = "Name and email cannot be empty";
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<!-- Navigation Bar -->
<nav class="w-full bg-white shadow-md fixed top-0 left-0 z-50 py-5 rounded-b-[15px]">
  <div class="w-full max-w-8xl px-5 flex flex-col items-start md:flex-row md:items-center justify-between">
    <!-- Logo on the far left -->
    <div
      class="pl-20 text-3xl font-bold text-left text-indigo-600 flex-1"
      style="font-family: 'Dancing Script', cursive;"
    >
      Fit Life Planner
    </div>
    <!-- Navigation links on the far right -->
    <ul class="flex flex-col gap-5 w-full mt-2.5 text-gray-700 font-medium pr-0 md:flex-row md:space-x-10 md:pr-20 md:w-auto md:mt-0 justify-end">
      <li><a href="profile.html" class="hover:text-indigo-600 transition">Home</a></li>
      <li><a href="fitness.html" class="hover:text-indigo-600 transition">Fitness Activity</a></li>
      <li><a href="meal.html" class="hover:text-indigo-600 transition">Meal Intake</a></li>
      <li><a href="bmi_bmr.html" class="hover:text-indigo-600 transition">BMI/BMR</a></li>
      <li><a href="feedback.html" class="hover:text-indigo-600 transition">Feedback</a></li>
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Spacer to offset fixed navbar height dynamically -->
<div class="h-[260px] md:h-[150px] lg:h-[100px] xl:h-[80px]"></div>


  <!-- Body content  -->
<div class="flex flex-col lg:flex-row items-center 
     pt-44 px-20 sm:pt-56 md:pt-28 lg:pt-24  py-16 
     bg-gradient-to-r from-white to-violet-100">


  
  <!-- Left Section -->
  <div class="lg:w-1/2 text-center lg:text-left space-y-6">
    <!-- Heading -->
    <h1 class="text-4xl md:text-5xl font-bold text-indigo-500">
      Welcome User
    </h1>

    <!-- Paragraph -->
    <p class="text-gray-600 text-lg leading-relaxed">
      We’re excited to help you begin your journey toward a healthier, more active lifestyle! Plan your meals, track your workouts, and stay motivated every step of the way.
    </p>

    <a href="userGetPlan.html" class="inline-block bg-gradient-to-r from-pink-400 to-indigo-500 text-white px-6 py-3 rounded-full shadow hover:scale-105 transition-all duration-300">
      Get Your Plan →
    </a>


  </div>

  <!-- Right Section -->
  <div class="lg:w-1/2 mt-10 lg:mt-0 flex justify-end">
    <img src="user1.png" alt="Workout App Preview" class="w-full max-w-md drop-shadow-xl">
  </div>
</div>

<section class="w-full px-4 py-10 bg-gray-100">
  <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-2xl font-semibold text-indigo-600 mb-6"> Your Profile</h2>

    <?php if(!empty($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>

    <form class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700" method="POST">
      <!-- Name -->
      <div>
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Email -->
      <div>
        <label class="block font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Password -->
      <div>
        <label class="block font-medium mb-1">Password</label>
        <input type="password" name="password" placeholder="Enter new password to change" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Gender -->
      <div>
        <label class="block font-medium mb-1">Gender</label>
        <select name="gender" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <option value="" disabled <?= $user['gender']==''?'selected':'' ?>>Select gender</option>
          <option value="Female" <?= $user['gender']=='Female'?'selected':'' ?>>Female</option>
          <option value="Male" <?= $user['gender']=='Male'?'selected':'' ?>>Male</option>
          <option value="Other" <?= $user['gender']=='Other'?'selected':'' ?>>Other</option>
        </select>
      </div>

      <!-- Height -->
      <div>
        <label class="block font-medium mb-1">Height (cm)</label>
        <input type="number" name="height" value="<?= htmlspecialchars($user['height']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Weight -->
      <div>
        <label class="block font-medium mb-1">Weight (kg)</label>
        <input type="number" name="weight" value="<?= htmlspecialchars($user['weight']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Age -->
      <div>
        <label class="block font-medium mb-1">Age</label>
        <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Goal Weight -->
      <div>
        <label class="block font-medium mb-1">Goal Weight (kg)</label>
        <input type="number" name="goal_weight" value="<?= htmlspecialchars($user['goal_weight']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <!-- Fitness Goal -->
      <div class="md:col-span-2">
        <label class="block font-medium mb-1">Fitness Goal</label>
        <select name="fitness_goal" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <option value="" disabled <?= $user['fitness_goal']==''?'selected':'' ?>>Select your goal</option>
          <option value="Weight Loss" <?= $user['fitness_goal']=='Weight Loss'?'selected':'' ?>>Weight Loss</option>
          <option value="Muscle Gain" <?= $user['fitness_goal']=='Muscle Gain'?'selected':'' ?>>Muscle Gain</option>
          <option value="Healthy Living" <?= $user['fitness_goal']=='Healthy Living'?'selected':'' ?>>Healthy Living</option>
        </select>
      </div>

      <div class="md:col-span-2 flex justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">Save Changes</button>
      </div>
    </form>
  </div>
</section>


<!-- Progress Chart Section -->
<section class="w-full px-4 py-10 bg-white">
  <div class="max-w-4xl mx-auto bg-gradient-to-br from-violet-100 to-white rounded-xl shadow-lg p-8">
    <h2 class="text-2xl font-semibold text-indigo-600 mb-6">Progress Overview</h2>

    <canvas id="progressChart" class="w-full h-64"></canvas>
  </div>
</section>

<script>
  const ctx = document.getElementById('progressChart').getContext('2d');

  const progressChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'], // X-axis
      datasets: [{
        label: 'Weight (kg)',
        data: [69, 68, 67.6, 66.2, 64.8], // Y-axis values
        borderColor: '#6366F1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        borderWidth: 3,
        pointRadius: 5,
        pointBackgroundColor: '#6366F1',
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: false
        }
      }
    }
  });
</script>

<!-- Footer Section -->
    <footer class="bg-gradient-to-br from-indigo-500 to-violet-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
        <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
    </footer>

</body>
</html>
