<?php
require 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}
$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    die("User not found.");
}

// Fetch the latest plan suggested for this user
$stmt = $pdo->prepare("SELECT * FROM suggested_plans WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$plan = $stmt->fetch();

// Fetch grocery lists (shown to all users)
$stmt = $pdo->query("SELECT * FROM grocery_lists ORDER BY id DESC");
$grocery_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sending message to dietitian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        // Insert message with sender='user'
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, sender, message) VALUES (?, 'user', ?)");
        $stmt->execute([$user_id, $message]);

        $msg_success = "Message sent to dietitian!";
    }
}

// Fetch chat history for this user (all messages between user and dietitian)
$stmt = $pdo->prepare("SELECT sender, message, created_at FROM messages WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$chat_history = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Fit Life Planner</title>
  <link rel="icon" type="image/x-icon" href="heart-rate.png">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet" />
</head>
<body class="bg-sky-50">

<!-- Navigation Bar -->
<nav class="w-full bg-white shadow-md fixed top-0 left-0 z-50 py-5 rounded-b-[15px]">
  <div class="w-full max-w-8xl px-5 flex flex-col items-start md:flex-row md:items-center justify-between">
    <div class="pl-20 text-3xl font-bold text-left text-indigo-600 flex-1" style="font-family: 'Dancing Script', cursive;">
      Fit Life Planner
    </div>
    <ul class="flex flex-col gap-5 w-full mt-2.5 text-gray-700 font-medium md:flex-row md:space-x-10 md:pr-20 md:w-auto md:mt-0 justify-end">
      <li><a href="profile.php" class="hover:text-indigo-600 transition">Home</a></li>
      <li><a href="fitness.html" class="hover:text-indigo-600 transition">Fitness Activity</a></li>
      <li><a href="meal.html" class="hover:text-indigo-600 transition">Meal Intake</a></li>
      <li><a href="bmi_bmr.html" class="hover:text-indigo-600 transition">BMI/BMR</a></li>
      <li><a href="feedback.php" class="hover:text-indigo-600 transition">Feedback</a></li>
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>

<main class="mt-20">

  <!-- Hero Section -->
  <section class="px-8 pt-12 bg-gradient-to-tr from-white to-violet-100">
    <div class="max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between">
      <div class="lg:w-1/2 space-y-6">
        <h2 class="text-4xl font-bold text-indigo-600">Your Personalized Health Plan</h2>
        <p class="text-lg text-gray-700">
          Explore your custom plan including daily meal suggestions, exercise recommendations, and goal-based grocery lists. 
          Stay on track and connect with your dietitian whenever needed!
        </p>
      </div>
      <div class="lg:w-1/2 mt-10 lg:mt-0">
        <img src="dietPlan.png" alt="Health Plan" class="w-full max-w-md mx-auto drop-shadow-xl">
      </div>
    </div>
  </section>

  <!-- User’s Suggested Plan -->
  <section class="max-w-6xl mx-auto px-6 py-12">
    <?php if ($plan): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-xl font-semibold text-indigo-600 mb-3">Calorie Intake Recommendation</h3>
          <p class="text-gray-700">Your daily intake should be around <span class="font-bold"><?= htmlspecialchars($plan['calories']) ?></span>.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-xl font-semibold text-indigo-600 mb-3">Exercise Suggestions</h3>
          <p class="text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($plan['exercise'])) ?></p>
        </div>
      </div>

      <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-pink-600 mb-3">Breakfast</h3>
          <p class="text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($plan['breakfast'])) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-green-600 mb-3">Lunch</h3>
          <p class="text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($plan['lunch'])) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-yellow-600 mb-3">Dinner & Snacks</h3>
          <p class="text-gray-700 whitespace-pre-line">
            <?= nl2br(htmlspecialchars($plan['dinner'])) ?><br>
            <?= nl2br(htmlspecialchars($plan['snacks'])) ?>
          </p>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-600">No plan has been suggested by your dietitian yet.</p>
    <?php endif; ?>
  </section>

  <!-- Grocery List Section -->
  <section class="bg-violet-100 px-6 py-12">
    <div class="max-w-6xl mx-auto">
      <h3 class="text-2xl font-semibold text-indigo-700 mb-6">Grocery Lists by Goal</h3>
      <div class="grid md:grid-cols-3 gap-6">
        <?php foreach($grocery_lists as $list): ?>
          <div class="bg-white rounded-lg shadow p-5">
            <h4 class="font-bold text-indigo-600 mb-2"><?= htmlspecialchars($list['goal']) ?></h4>
            <p class="text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($list['items'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Message Dietitian Section -->

  <section class="px-6 py-12">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow flex flex-col h-[400px]">
    <h3 class="text-xl font-semibold text-indigo-600 mb-4">Chat with Dietitian</h3>

    <!-- Chat history (scrollable) -->
    <div class="flex-1 overflow-y-auto border border-gray-200 rounded p-3 mb-4">
      <?php if (!empty($chat_history)): ?>
        <?php foreach ($chat_history as $chat): ?>
          <div class="mb-2">
    <span class="font-bold <?php echo $chat['sender'] === 'user' ? 'text-blue-600' : 'text-green-600'; ?>">
       <?php echo $chat['sender'] === 'user' ? htmlspecialchars($user['name']) : 'Dietitian'; ?>:
    </span>

            <span><?php echo htmlspecialchars($chat['message']); ?></span>
            <div class="text-xs text-gray-500">
              <?php echo date("M d, Y H:i", strtotime($chat['created_at'])); ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500">No messages yet. Start the conversation below!</p>
      <?php endif; ?>
    </div>

    <!-- Input form -->
    <form action="" method="POST" class="flex gap-2">
      <textarea name="message" rows="2" placeholder="Write your message..." class="flex-1 border border-gray-300 rounded p-3 focus:outline-none focus:ring-2 focus:ring-indigo-400" required></textarea>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Send</button>
    </form>
  </div>
</section>


</main>

<!-- Footer -->
<footer class="bg-gradient-to-br from-indigo-500 to-violet-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
  <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

</body>
</html>
