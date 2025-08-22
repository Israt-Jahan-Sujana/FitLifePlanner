<?php
require 'config.php';  // Make sure this sets up $pdo
session_start();

// Get logged-in dietitian ID
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Unauthorized access.");

// Fetch dietitian info for profile
$stmt = $pdo->prepare("SELECT u.*, d.contact, d.specialization, d.years_experience 
                       FROM users u 
                       LEFT JOIN dietitians d ON u.id=d.user_id 
                       WHERE u.id=? AND u.role='dietitian'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) die("Dietitian not found.");

// Handle profile update
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $name           = trim($_POST['name']);
    $email          = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password       = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $contact        = trim($_POST['contact']);
    $specialization = trim($_POST['specialization']);
    $years_exp      = $_POST['years_experience'] ?? null;

    if ($name && $email) {
        $pdo->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?")
            ->execute([$name, $email, $password, $user_id]);
        $pdo->prepare("INSERT INTO dietitians (user_id) VALUES (?) ON DUPLICATE KEY UPDATE contact=?, specialization=?, years_experience=?")
            ->execute([$user_id, $contact, $specialization, $years_exp]);
        header("Location: dietitian_dashboard.php");
        exit;
    } else {
        $error = "Name and email cannot be empty";
    }
}

// Fetch all registered users (role = user)
$stmt = $pdo->query("SELECT id, name, age, gender, email, height, weight, fitness_goal AS goal, goal_weight FROM users WHERE role='user' ORDER BY name ASC");
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle fetching messages for a selected user (via AJAX)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'messages' && isset($_GET['user_id'])) {
    header('Content-Type: application/json');
    $uid = intval($_GET['user_id']);
    $stmt = $pdo->prepare("SELECT m.*, u.name as sender_name 
                           FROM messages m 
                           JOIN users u ON m.sender_id = u.id 
                           WHERE (m.sender_id = :uid AND m.receiver_id = :did) 
                              OR (m.sender_id = :did AND m.receiver_id = :uid)
                           ORDER BY m.created_at ASC");
    $stmt->execute(['uid'=>$uid, 'did'=>$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Handle sending reply
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['send_message'])) {
    $to_user = intval($_POST['to_user']);
    $msg = trim($_POST['message']);
    if ($msg) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $to_user, $msg]);
    }
    header("Location: dietitian_dashboard.php?open_user=".$to_user);
    exit;
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
<body class="flex flex-col min-h-screen">

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

<main class="flex-grow pt-28 px-4 sm:px-10">

<!-- Welcome Section -->
<section class="mb-12">
  <div class="bg-gradient-to-r from-white to-violet-100 text-indigo-700 rounded-2xl shadow-lg p-8 text-center">
    <h2 class="text-3xl md:text-4xl mb-2" style="font-family: 'Carter One', system-ui;">
      Welcome Dietitian, <?= htmlspecialchars($user['name']) ?>
    </h2>
    <p class="text-gray-600 text-lg md:text-xl">
      Manage your clients, suggest personalized diet and fitness plans, and communicate directly to help them achieve their health goals.
    </p>
  </div>
</section>

<!-- Profile Section -->
<section class="max-w-7xl mx-auto px-4 py-10 space-y-12 flex flex-col lg:flex-row gap-10 items-start">
  <div class="flex justify-center lg:justify-start w-full lg:w-1/3">
    <img src="dietitian1.jpg" alt="Dietitian Photo" class="w-full h-full pt-10" />
  </div>
  <div class="w-full lg:w-2/3 bg-indigo-50 rounded-2xl shadow-lg p-8">
    <h3 class="text-2xl text-center font-semibold text-indigo-700 mb-6">Your Profile</h3>
    <?php if(!empty($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-800">
      <div>
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div>
        <label class="block font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div>
        <label class="block font-medium mb-1">Password</label>
        <input type="password" name="password" placeholder="Enter new password to change" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div>
        <label class="block font-medium mb-1">Contact</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div>
        <label class="block font-medium mb-1">Specialization</label>
        <input type="text" name="specialization" value="<?= htmlspecialchars($user['specialization']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div>
        <label class="block font-medium mb-1">Years of Experience</label>
        <input type="number" name="years_experience" value="<?= htmlspecialchars($user['years_experience']) ?>" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
      </div>
      <div class="md:col-span-2 flex justify-end">
        <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition duration-200">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</section>

<!-- User Table with Search -->
<section class="bg-white mx-0 md:mx-20 mt-2 p-6 rounded-xl shadow-lg">
  <h2 class="text-2xl font-semibold text-indigo-600 mb-4">User Details</h2>
  <div class="mb-4 relative">
    <input type="text" id="userSearch" placeholder="Search by name..." 
      class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
    <ul id="suggestions" class="absolute bg-white border border-gray-200 w-full mt-1 rounded-lg shadow-lg hidden"></ul>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm text-left" id="userTable">
      <thead class="bg-indigo-100 text-indigo-700">
        <tr>
          <th class="px-6 py-3">Name</th>
          <th class="px-6 py-3">Age</th>
          <th class="px-6 py-3">Gender</th>
          <th class="px-6 py-3">Email</th>
          <th class="px-6 py-3">Height</th>
          <th class="px-6 py-3">Weight</th>
          <th class="px-6 py-3">Goal</th>
          <th class="px-6 py-3">Goal Weight</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100 text-gray-700" id="userBody">
        <?php foreach($allUsers as $u): ?>
        <tr class="hover:bg-violet-50 transition">
          <td class="px-6 py-4"><?= htmlspecialchars($u['name']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['age']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['gender']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['email']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['height']) ?> cm</td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['weight']) ?> kg</td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['goal']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($u['goal_weight']) ?> kg</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Messages Section -->
<section class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-lg mt-12 mb-10">
  <h2 class="text-2xl font-semibold text-indigo-600 mb-4">Messages</h2>
  <div id="messageContainer" class="space-y-6">
    <?php
    $stmt = $pdo->prepare("SELECT DISTINCT u.id, u.name 
                           FROM messages m 
                           JOIN users u ON m.sender = u.id 
                           WHERE m.receiver_id = ? 
                           ORDER BY u.name ASC");
    $stmt->execute([$user_id]);
    $senders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($senders):
      foreach($senders as $s):
    ?>
    <div class="border rounded-lg p-4">
      <h3 class="text-lg font-semibold text-gray-700 mb-2">Conversation with <?= htmlspecialchars($s['name']) ?></h3>
      <div class="h-48 overflow-y-auto bg-gray-50 p-3 mb-3" id="chatBox-<?= $s['id'] ?>"></div>
      <form method="POST" class="flex gap-2">
        <input type="hidden" name="to_user" value="<?= $s['id'] ?>"/>
        <input type="text" name="message" placeholder="Type your reply..." 
          class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300" required/>
        <button type="submit" name="send_message" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600">Send</button>
      </form>
    </div>
    <?php
      endforeach;
    else:
      echo "<p class='text-gray-500'>No messages from users yet.</p>";
    endif;
    ?>
  </div>
</section>

</main>

<!-- Footer -->
<footer class="bg-gradient-to-br from-indigo-500 to-indigo-400 text-white px-5 py-8 text-center rounded-t-[15px] shadow-md">
  <p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

<script>
// --- Autocomplete search ---
const users = <?= json_encode(array_column($allUsers,'name')) ?>;
const searchInput = document.getElementById("userSearch");
const suggestions = document.getElementById("suggestions");
const tableBody = document.getElementById("userBody");
const fullTable = tableBody.innerHTML;

searchInput.addEventListener("input", function() {
  const val = this.value.toLowerCase();
  suggestions.innerHTML="";
  if (!val) {
    suggestions.classList.add("hidden");
    tableBody.innerHTML = fullTable;
    return;
  }
  const matches = users.filter(u=>u.toLowerCase().includes(val));
  if (matches.length) {
    matches.forEach(m=>{
      const li=document.createElement("li");
      li.textContent=m;
      li.className="px-3 py-1 hover:bg-indigo-100 cursor-pointer";
      li.onclick=()=>{searchInput.value=m; suggestions.classList.add("hidden"); filterTable(m);};
      suggestions.appendChild(li);
    });
    suggestions.classList.remove("hidden");
  } else {
    suggestions.classList.add("hidden");
  }
});

function filterTable(name) {
  let rows=[...tableBody.querySelectorAll("tr")];
  rows.forEach(r=>{
    if(r.children[0].textContent.trim().toLowerCase()===name.toLowerCase()){
      r.style.display="";
    } else {
      r.style.display="none";
    }
  });
}

// --- Load messages via AJAX ---
<?php if(!empty($senders)):
      foreach($senders as $s): ?>
fetch("dietitian_dashboard.php?ajax=messages&user_id=<?= $s['id']?>")
.then(r => r.json())
.then(data => {
  const box = document.getElementById("chatBox-<?= $s['id']?>");
  box.innerHTML = "";
  data.forEach(m => {
    let align = m.sender_id == <?= $user_id ?> ? "text-right" : "text-left";
    let who = m.sender_id == <?= $user_id ?> ? "You" : m.sender_name;
    box.innerHTML += `<div class="mb-2 ${align}"><p class="text-sm"><strong>${who}:</strong> ${m.message}</p><p class="text-xs text-gray-500">${m.created_at}</p></div>`;
  });
  box.scrollTop = box.scrollHeight;
});
<?php endforeach; endif; ?>
</script>

</body>
</html>
