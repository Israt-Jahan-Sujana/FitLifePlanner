<?php
// dietitian_dashboard.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
session_start();

// Get logged-in dietitian ID from session
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Unauthorized access. Please log in.");
}

try {
    // Fetch common user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'dietitian'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Dietitian not found or not authorized.");
    }

    // Fetch dietitian-specific info (if exists)
    $stmt = $pdo->prepare("SELECT * FROM dietitians WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $dietitian = $stmt->fetch();

    if (!$dietitian) {
        // Insert a new empty record for first-time login
        $insert = $pdo->prepare("INSERT INTO dietitians (user_id) VALUES (?)");
        $insert->execute([$user_id]);

        $stmt = $pdo->prepare("SELECT * FROM dietitians WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $dietitian = $stmt->fetch();
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name']);
    $email          = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password       = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $contact        = trim($_POST['contact']);
    $specialization = trim($_POST['specialization']);
    $years_exp      = $_POST['years_experience'] ?? null;

    if ($name && $email) {
        // Update users table
        $updateUser = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $updateUser->execute([$name, $email, $password, $user_id]);

        // Update dietitians table
        $updateDietitian = $pdo->prepare("UPDATE dietitians SET contact = ?, specialization = ?, years_experience = ? WHERE user_id = ?");
        $updateDietitian->execute([$contact, $specialization, $years_exp, $user_id]);

        header("Location: dietitian_dashboard.php");
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
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Carter+One&display=swap" rel="stylesheet">
</head>

<body>
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

<main>
<!-- Welcome Section -->
<section class="pt-28 px-4 sm:px-10">
  <div class="bg-gradient-to-r from-white to-violet-100 text-indigo-700 rounded-2xl shadow-lg p-8 text-center">
    <h2 class="text-3xl md:text-4xl mb-2" style="font-family: 'Carter One', system-ui;">
      Welcome Dietitian, <?= htmlspecialchars($user['name']) ?>
    </h2>
    <p class="text-gray-600 text-lg md:text-xl">
      Manage your clients, suggest personalized diet and fitness plans, and communicate directly to help them achieve their health goals.
    </p>
  </div>
</section>

<!-- Dashboard Content -->
<div class="max-w-7xl mx-auto px-4 py-10 space-y-12 ">
<section class="flex flex-col lg:flex-row gap-10 items-start">
  <!-- Profile Image -->
  <div class="flex justify-center lg:justify-start w-full lg:w-1/3">
    <img src="dietitian1.jpg" alt="Dietitian Photo" class="w-full h-full pt-10" />
  </div>

  <!-- Profile Form -->
  <div class="w-full lg:w-2/3 bg-indigo-50 rounded-2xl shadow-lg p-8">
    <h3 class="text-2xl text-center font-semibold text-indigo-700 mb-6">Your Profile</h3>

    <?php if(!empty($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>

    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-800">
      <!-- Name -->
      <div>
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Email -->
      <div>
        <label class="block font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Password -->
      <div>
        <label class="block font-medium mb-1">Password</label>
        <input type="password" name="password" placeholder="Enter new password to change"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Contact -->
      <div>
        <label class="block font-medium mb-1">Contact</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($dietitian['contact']) ?>"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Specialization -->
      <div>
        <label class="block font-medium mb-1">Specialization</label>
        <input type="text" name="specialization" value="<?= htmlspecialchars($dietitian['specialization']) ?>"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Years of Experience -->
      <div>
        <label class="block font-medium mb-1">Years of Experience</label>
        <input type="number" name="years_experience" value="<?= htmlspecialchars($dietitian['years_experience']) ?>"
          class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>

      <!-- Save -->
      <div class="md:col-span-2 flex justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition duration-200">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</section>
</div>


<section>
  <!-- Search User -->
<section class="bg-white mx-90 mt-10">
  <div class="flex flex-col md:flex-row items-center gap-4">
    <label class="text-2xl font-semibold text-indigo-600 whitespace-nowrap">Search User</label>
    <input type="text" placeholder="Search by name..."
      class="flex-1 p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-400 w-full md:w-auto" />
  </div>
</section>


  <!-- User Details Table -->
<section class="bg-white rounded-xl shadow-lg drop-shadow-[0_4px_12px_rgba(165,180,252,0.6)] overflow-x-auto p-6 mt-10">
  <h2 class="text-2xl font-semibold text-indigo-600 mb-4">User Details</h2>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
      <thead class="bg-indigo-100 text-indigo-700">
        <tr>
          <th class="px-6 py-3 font-medium">Name</th>
          <th class="px-6 py-3 font-medium">Age</th>
          <th class="px-6 py-3 font-medium">Gender</th>
          <th class="px-6 py-3 font-medium">Email</th>
          <th class="px-6 py-3 font-medium">Height</th>
          <th class="px-6 py-3 font-medium">Weight</th>
          <th class="px-6 py-3 font-medium">Goal</th>
          <th class="px-6 py-3 font-medium">Goal Weight</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
        <tr class="hover:bg-violet-50 transition">
          <td class="px-6 py-4">Israt Jahan</td>
          <td class="px-6 py-4">22</td>
          <td class="px-6 py-4">Female</td>
          <td class="px-6 py-4">israt@example.com</td>
          <td class="px-6 py-4">160 cm</td>
          <td class="px-6 py-4">55 kg</td>
          <td class="px-6 py-4">Weight Loss</td>
          <td class="px-6 py-4">50 kg</td>
        </tr>
        <!-- Add more <tr> blocks for more users if needed -->
      </tbody>
    </table>
  </div>
</section>

<!-- Message Section -->
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl drop-shadow-md mb-10 mt-20">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Messages</h2>
    <!--message display-->
    <div class="border rounded-lg h-48 overflow-y-auto p-3 bg-gray-50" id="messageBox">
      <div class="mb-2">
        <p class="text-sm"><strong>User:</strong> I need help with a diet plan.</p>
        <p class="text-xs text-gray-500">Just now</p>
      </div>
    </div>
    <form onsubmit="sendMessage(event)" class="mt-4 flex">
      <input
        type="text"
        id="replyInput"
        placeholder="Type your reply..."
        class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-300"
        required
      />
      <button
        type="submit"
        class="bg-indigo-500 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-600"
      >
        Send
      </button>
    </form>
  </div>

</main>
 
 <!-- Footer Section -->
<footer class="bg-gradient-to-br from-indigo-500 to-indigo-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
        <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

  <!-- Scripts -->
  <script>

    // Message Send
    function sendMessage(e) {
      e.preventDefault();
      const replyInput = document.getElementById("replyInput");
      const messageBox = document.getElementById("messageBox");

      const reply = document.createElement("div");
      reply.innerHTML = `
        <p class="text-sm text-right"><strong>You:</strong> ${replyInput.value}</p>
        <p class="text-xs text-gray-500 text-right">Just now</p>
      `;
      messageBox.appendChild(reply);
      replyInput.value = "";
      messageBox.scrollTop = messageBox.scrollHeight;
    }
  </script>
</body>
</html>