<?php
// admin_dashboard.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // DB connection

// Make sure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle delete feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
    $stmt->execute([$deleteId]);
    $msg = "Feedback deleted successfully!";
}

// Fetch all feedbacks with user names
$stmt = $pdo->query("
    SELECT f.id, f.rating, f.comment, f.created_at, u.name
    FROM feedbacks f
    JOIN users u ON u.id = f.users_id
    ORDER BY f.created_at DESC
");
$feedbacks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Fit Life Planner</title>
<link rel="icon" type="image/x-icon" href="heart-rate.png">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Carter+One&display=swap" rel="stylesheet">
</head>
<body class="text-[#2c3e50] text-base overflow-x-hidden min-h-screen">

<!-- Navigation Bar -->
<nav class="w-full bg-white shadow-md fixed top-0 left-0 z-50 py-5 rounded-b-[15px]">
  <div class="w-full max-w-8xl px-5 flex flex-col items-start md:flex-row md:items-center justify-between">
    <div class="pl-20 text-3xl font-bold text-left text-indigo-600 flex-1" style="font-family: 'Dancing Script', cursive;">Fit Life Planner</div>
    <ul class="flex flex-col gap-5 w-full mt-2.5 text-gray-700 font-medium pr-0 md:flex-row md:space-x-10 md:pr-20 md:w-auto md:mt-0 justify-end">
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Welcome Section -->
<section class="pt-28 px-4 sm:px-10">
  <div class="bg-gradient-to-r from-white to-violet-100 text-indigo-700 rounded-2xl shadow-lg p-8 text-center">
    <h2 class="text-3xl md:text-4xl mb-2" style="font-family: 'Carter One', system-ui;">Welcome Admin, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
    <p class="text-gray-600 text-lg md:text-xl">Manage users, dietitians, and platform settings from your dashboard.</p>
  </div>
</section>

<!-- Dashboard Cards Section -->
<section class="mt-10 px-4 sm:px-10">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

    <!-- Card 1 -->
    <div class="bg-white rounded-2xl shadow-lg p-6 text-center flex flex-col items-center justify-between">
      <img src="dietitian.jpg" alt="Dietitian" class="w-40 h-40 object-cover rounded-full mb-4 shadow-md">
      <h3 class="text-xl font-bold text-indigo-700 mb-2">Manage Dietitians</h3>
      <p class="text-gray-600 mb-4">View, Edit or remove profiles.</p>
      <a href="dietitian_list.html">
        <button class="bg-indigo-500 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-600 transition">
          Go to Page
        </button>
      </a>
    </div>

    <!-- Card 2 -->
    <div class="bg-white rounded-2xl shadow-lg p-6 text-center flex flex-col items-center justify-between">
      <img src="user2.jpg" alt="Users" class="w-40 h-40 object-cover rounded-full mb-4 shadow-md">
      <h3 class="text-xl font-bold text-indigo-700 mb-2">Manage Users</h3>
      <p class="text-gray-600 mb-4">View or remove user profiles.</p>
      <a href="user_list.html">
        <button class="bg-indigo-500 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-600 transition">
          Go to Page
        </button>
      </a>
    </div>

  </div>
</section>

<!-- Feedback Table Section -->
<section class="mt-10 px-4 pb-10 sm:px-10">
  <div class="bg-white rounded-2xl shadow-lg overflow-x-auto">
    <h3 class="text-2xl font-bold text-indigo-700 text-center p-6 border-b">User Feedback</h3>

    <?php if(!empty($msg)): ?>
      <p class="text-green-600 text-center py-2"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <table class="min-w-full text-left text-sm text-gray-700">
      <thead class="bg-gradient-to-r from-indigo-400 to-indigo-700 text-white text-base">
        <tr>
          <th class="px-6 py-4">User Name</th>
          <th class="px-6 py-4">Rating</th>
          <th class="px-6 py-4">Feedback</th>
          <th class="px-6 py-4">Submitted At</th>
          <th class="px-6 py-4">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-indigo-100">
        <?php if ($feedbacks): ?>
          <?php foreach ($feedbacks as $fb): ?>
          <tr class="hover:bg-purple-50 transition">
            <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($fb['name']); ?></td>
            <td class="px-6 py-4"><?php echo str_repeat('⭐', round($fb['rating'])) . ' ' . number_format($fb['rating'], 1); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($fb['comment']); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($fb['created_at']); ?></td>
            <td class="px-6 py-4">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                <input type="hidden" name="delete_id" value="<?php echo $fb['id']; ?>" />
                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Clear</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No feedback available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<footer class="bg-gradient-to-br from-indigo-500 to-indigo-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
    <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

</body>
</html>
