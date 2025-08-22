<?php
require 'config.php';
session_start();

// Check dietitian login
if(!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}
$dietitian_id = $_SESSION['user_id'];

// Fetch all users
$stmt = $pdo->query("SELECT id, name FROM users WHERE role='user' ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle plan submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $calories = $_POST['calories'] ?? '';
    $breakfast = $_POST['breakfast'] ?? '';
    $lunch = $_POST['lunch'] ?? '';
    $dinner = $_POST['dinner'] ?? '';
    $snacks = $_POST['snacks'] ?? '';
    $exercise = $_POST['exercise'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO suggested_plans 
        (user_id, calories, breakfast, lunch, dinner, snacks, exercise)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $calories, $breakfast, $lunch, $dinner, $snacks, $exercise]);

    $success = "Plan submitted successfully!";
}

// Handle grocery list submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal'])) {
    $goal = $_POST['goal'];
    $items = $_POST['items'];

    $stmt = $pdo->prepare("INSERT INTO grocery_lists (goal, items) VALUES (?, ?)");
    $stmt->execute([$goal, $items]);

    $grocery_success = "Grocery list saved for all users!";
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
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Carter+One&display=swap" rel="stylesheet">
</head>

<body class="bg-sky-50">
<!-- Navigation Bar -->
<nav class="w-full bg-white shadow-md fixed top-0 left-0 z-50 py-5 rounded-b-[15px]">
  <div class="w-full max-w-8xl px-5 flex flex-col items-start md:flex-row md:items-center justify-between">
    <div class="pl-20 text-3xl font-bold text-left text-indigo-600 flex-1" style="font-family: 'Dancing Script', cursive;">
      Fit Life Planner
    </div>
    <ul class="flex flex-col gap-5 w-full mt-2.5 text-gray-700 font-medium md:flex-row md:space-x-10 md:pr-20 md:w-auto md:mt-0 justify-end">
      <li><a href="dietitian_dashboard.php" class="hover:text-indigo-600 transition">Home</a></li>
      <li><a href="SuggestPlans.php" class="hover:text-indigo-600 transition">Suggest plans</a></li>
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>
<main class="max-w-5xl mx-auto mt-20 px-6 py-10 space-y-12">

<!-- Suggest Plan Form -->
<section class="bg-white p-6 rounded-xl shadow-md">
  <h2 class="text-xl font-semibold mb-4 text-indigo-600">Suggest Plan for User</h2>

  <?php if(isset($success)) echo "<p class='text-green-600 mb-2'>$success</p>"; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block font-medium mb-1">Select User</label>
      <select name="user_id" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
        <option value="">--Select User--</option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block mb-1 font-medium">Daily Calorie Intake</label>
      <input type="text" name="calories" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="e.g. 1800 kcal" required />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-medium">Breakfast</label>
        <textarea name="breakfast" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="2"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-medium">Lunch</label>
        <textarea name="lunch" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="2"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-medium">Dinner</label>
        <textarea name="dinner" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="2"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-medium">Snacks</label>
        <textarea name="snacks" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="2"></textarea>
      </div>
    </div>

    <div>
      <label class="block mb-1 font-medium">Exercise Plan</label>
      <textarea name="exercise" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="3"></textarea>
    </div>

    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
      Submit Plan
    </button>
  </form>
</section>

<!-- Grocery List Section -->
<section class="bg-white p-6 rounded-xl shadow-md">
  <h2 class="text-xl font-semibold mb-4 text-indigo-600">Create Grocery List for All Users</h2>

  <?php if(isset($grocery_success)) echo "<p class='text-green-600 mb-2'>$grocery_success</p>"; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block mb-1 font-medium">Select Goal</label>
      <select name="goal" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
        <option value="">--Select Goal--</option>
        <option>Weight Loss</option>
        <option>Muscle Gain</option>
        <option>Healthy Living</option>
      </select>
    </div>

    <div>
      <label class="block mb-1 font-medium">Grocery List</label>
      <textarea name="items" class="w-full border border-gray-300 rounded-lg px-4 py-2" rows="5" placeholder="Enter grocery items..." required></textarea>
    </div>

    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold">
      Save Grocery List
    </button>
  </form>
</section>

</main>

 <!-- Footer Section -->
<footer class="bg-gradient-to-br from-indigo-500 to-indigo-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
        <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

</body>
</html>
