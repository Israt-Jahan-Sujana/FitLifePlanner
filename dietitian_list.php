<?php
require 'config.php'; // your DB connection

// Handle Edit
if(isset($_POST['edit_id']) && isset($_POST['contact_number'])) {
    $stmt = $pdo->prepare("UPDATE dietitians SET contact = ? WHERE id = ?");
    if($stmt->execute([$_POST['contact_number'], $_POST['edit_id']])) {
        echo "<script>alert('Contact updated successfully!');</script>";
    } else {
        echo "<script>alert('Failed to update contact.');</script>";
    }
}

// Handle Delete
if(isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM dietitians WHERE id = ?");
    if($stmt->execute([$_POST['delete_id']])) {
        echo "<script>alert('Dietitian deleted successfully!');</script>";
    } else {
        echo "<script>alert('Failed to delete dietitian.');</script>";
    }
}

// Fetch all dietitians with name/email from users table
$stmt = $pdo->query("SELECT d.id, d.contact, u.name, u.email 
                     FROM dietitians d 
                     JOIN users u ON d.user_id = u.id");
$dietitians = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Fit Life Planner</title>
<link rel="icon" type="image/x-icon" href="heart-rate.png">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Carter+One&display=swap" rel="stylesheet">
</head>

<body class="text-[#2c3e50] text-base overflow-x-hidden min-h-screen flex flex-col">

<!-- Navigation Bar -->
<nav class="w-full bg-white shadow-md fixed top-0 left-0 z-50 py-5 rounded-b-[15px]">
  <div class="w-full max-w-8xl px-5 flex flex-col items-start md:flex-row md:items-center justify-between">
    <div class="pl-20 text-3xl font-bold text-left text-indigo-600 flex-1" style="font-family: 'Dancing Script', cursive;">
      Fit Life Planner
    </div>
    <ul class="flex flex-col gap-5 w-full mt-2.5 text-gray-700 font-medium pr-0 md:flex-row md:space-x-10 md:pr-20 md:w-auto md:mt-0 justify-end">
      <li><a href="admin_dashboard.php" class="hover:text-indigo-600 transition">Home</a></li>
      <li><a href="user_list.php" class="hover:text-indigo-600 transition">User</a></li>
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Welcome Section -->
<section class="pt-28 px-4 sm:px-10">
  <div class="bg-gradient-to-r from-white to-violet-100 text-indigo-700 rounded-2xl shadow-lg p-8 text-center">
    <h2 class="text-3xl md:text-4xl mb-2" style="font-family: 'Carter One', system-ui; font-weight: 400; font-style: normal;">
      Welcome Admin, Israt
    </h2>
    <p class="text-gray-600 text-lg md:text-xl">Manage users, dietitians, and platform settings from your dashboard.</p>
  </div>
</section>

<main class="flex-grow pt-10">
  <!-- Dietitian List Section -->
  <section class="mt-10 px-4 pb-10 sm:px-10 ">
    <div class="bg-white rounded-2xl drop-shadow-[0_4px_12px_rgba(165,180,252,0.6)] overflow-x-auto">
      <h3 class="text-2xl font-bold text-black text-center p-6 border-b">Dietitian List</h3>
      <table class="min-w-full text-left text-sm text-gray-700">
        <thead class="bg-gradient-to-r from-indigo-700 via-indigo-400 to-indigo-700 text-white text-base">
          <tr>
            <th class="px-6 py-4">Dietitian Name</th>
            <th class="px-6 py-4">Email</th>
            <th class="px-6 py-4">Contact Number</th>
            <th class="px-6 py-4">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-indigo-100">
          <?php foreach($dietitians as $dietitian): ?>
          <tr class="hover:bg-purple-50 transition">
            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($dietitian['name']) ?></td>
            <td class="px-6 py-4"><?= htmlspecialchars($dietitian['email']) ?></td>
            <td class="px-6 py-4">
              <form method="POST" class="flex items-center gap-2">
                <input type="hidden" name="edit_id" value="<?= $dietitian['id'] ?>">
                <input type="text" name="contact_number" 
                       value="<?= htmlspecialchars($dietitian['contact']) ?>" 
                       class="border border-indigo-300 rounded-md px-2 py-1 w-full focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                <button type="submit" 
                        class="px-4 py-2 bg-yellow-400 text-white font-bold rounded-full transition duration-300 ease-in-out hover:scale-110">
                  Edit
                </button>
              </form>
            </td>
            <td class="px-6 py-4">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this dietitian?');">
                <input type="hidden" name="delete_id" value="<?= $dietitian['id'] ?>">
                <button type="submit" 
                        class="px-4 py-2 bg-red-500 text-white font-bold rounded-full transition duration-300 ease-in-out hover:scale-110">
                  Delete
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<footer class="bg-gradient-to-br from-indigo-500 to-indigo-400 text-white px-5 py-6 text-center rounded-t-[15px] shadow-md">
  <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

</body>
</html>
