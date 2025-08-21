<?php
// feedback.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php'; // Your DB connection file

// AJAX handler
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $user_id = $_SESSION['user_id'] ?? null;

    if ($_GET['action'] === 'list') {
        // Fetch latest 50 feedbacks with username
        $stmt = $pdo->query("
            SELECT f.id, f.rating, f.comment, f.created_at, u.name
            FROM feedbacks f
            JOIN users u ON u.id = f.users_id
            ORDER BY f.created_at DESC
            LIMIT 50
        ");
        echo json_encode(['feedbacks' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($_GET['action'] === 'create') {
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['error' => 'You must be logged in to submit feedback']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $rating  = (int)($input['rating'] ?? 0);
        $comment = trim($input['comment'] ?? '');

        if ($rating < 1 || $rating > 5 || $comment === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Please provide a rating (1–5) and a comment']);
            exit;
        }

        // Insert feedback
        $ins = $pdo->prepare("INSERT INTO feedbacks (users_id, rating, comment) VALUES (?, ?, ?)");
        $ins->execute([$user_id, $rating, $comment]);

        // Return the created row
        $id = $pdo->lastInsertId();
        $row = $pdo->prepare("
            SELECT f.id, f.rating, f.comment, f.created_at, u.name
            FROM feedbacks f
            JOIN users u ON u.id = f.users_id
            WHERE f.id = ?
        ");
        $row->execute([$id]);

        echo json_encode(['success' => true, 'feedback' => $row->fetch(PDO::FETCH_ASSOC)]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Fit Life Planner</title>
<link rel="icon" href="heart-rate.png" type="image/x-icon" />
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet" />
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
      <li><a href="profile.php" class="hover:text-indigo-600 transition">Home</a></li>
      <li><a href="fitness.html" class="hover:text-indigo-600 transition">Fitness Activity</a></li>
      <li><a href="meal.html" class="hover:text-indigo-600 transition">Meal Intake</a></li>
      <li><a href="bmi_bmr.html" class="hover:text-indigo-600 transition">BMI/BMR</a></li>
      <li><a href="feedback.php" class="hover:text-indigo-600 transition">Feedback</a></li>
      <li><a href="index.html" class="hover:text-indigo-600 transition">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Spacer -->
<div class="h-[240px] sm:h-[160px] md:h-[120px] lg:h-[90px]"></div>

<main class="flex-grow px-4 sm:px-6 pb-10 max-w-3xl mx-auto">
<h1 class="text-2xl sm:text-3xl font-bold text-center text-indigo-600 mb-8">User Feedback</h1>

<section class="bg-white shadow-lg rounded-lg p-6 mb-10">
<h2 class="text-xl font-semibold mb-4 text-gray-700">Leave Your Feedback</h2>

<div class="flex items-center gap-2 mb-4">
<label class="text-gray-600">Rating:</label>
<div id="stars" class="flex gap-1 text-yellow-400 text-2xl cursor-pointer">
  <span data-value="1">☆</span>
  <span data-value="2">☆</span>
  <span data-value="3">☆</span>
  <span data-value="4">☆</span>
  <span data-value="5">☆</span>
</div>
</div>

<textarea id="feedbackInput" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Write your feedback..."></textarea>
<button id="submitBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
Submit Feedback
</button>

<p id="successMsg" class="text-green-600 mt-3 hidden">Thank you! Feedback submitted 🎉</p>
<p id="errorMsg" class="text-red-600 mt-3 hidden"></p>
<?php if (!isset($_SESSION['user_id'])): ?>
<p class="text-sm text-gray-500 mt-3">You must be logged in to submit feedback.</p>
<script>
window.addEventListener('DOMContentLoaded', ()=>{
    document.getElementById('submitBtn').disabled = true;
});
</script>
<?php endif; ?>
</section>

<section>
<h2 class="text-xl font-semibold mb-4 text-gray-700">What others are saying</h2>
<div id="feedbackList" class="space-y-4"></div>
</section>
</main>

<footer class="bg-gradient-to-br from-indigo-500 to-violet-400 text-white px-5 py-6 text-center rounded-t-[15px] shadow-md">
<p>&copy; 2025 FitLifePlanner. All rights reserved.</p>
</footer>

<script>
let selectedRating = 0;

function escapeHtml(str) {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

const starContainer = document.getElementById('stars');
const stars = starContainer.querySelectorAll('span');
function updateStars(count) { stars.forEach((s,i)=> s.textContent = i < count ? '★' : '☆'); }

stars.forEach((star, idx)=>{
    star.addEventListener('click', ()=>{ selectedRating = idx+1; updateStars(selectedRating); });
    star.addEventListener('mouseover', ()=> updateStars(idx+1));
    star.addEventListener('mouseout', ()=> updateStars(selectedRating || 0));
});

async function loadFeedback(){
    const container = document.getElementById("feedbackList");
    container.innerHTML = "<p class='text-gray-500'>Loading…</p>";
    try{
        const res = await fetch("feedback.php?action=list");
        const data = await res.json();
        const list = data.feedbacks || [];
        if(list.length===0){ container.innerHTML="<p class='text-gray-500'>No feedback yet. Be the first to leave one!</p>"; return; }
        container.innerHTML="";
        list.forEach(entry=>{
            const starsStr='★'.repeat(entry.rating)+'☆'.repeat(5-entry.rating);
            const div=document.createElement("div");
            div.className="bg-white border p-4 rounded shadow-sm";
            div.innerHTML=`
              <div class="flex justify-between items-center mb-1">
                <span class="font-semibold text-indigo-600">${escapeHtml(entry.name||'Unknown')}</span>
                <span class="text-yellow-400">${starsStr}</span>
              </div>
              <p class="text-gray-700 whitespace-pre-line">${escapeHtml(entry.comment)}</p>
              <p class="text-xs text-gray-400 mt-1">${new Date(entry.created_at).toLocaleString()}</p>
            `;
            container.appendChild(div);
        });
    }catch(e){
    console.error("Failed to load feedback:", e);
    // Optionally, you can show a minimal notice
    // container.innerHTML="<p class='text-red-600'>Could not load feedback.</p>";
}
}

async function submitFeedback(){
    const msgOk=document.getElementById("successMsg");
    const msgErr=document.getElementById("errorMsg");
    msgOk.classList.add("hidden");
    msgErr.classList.add("hidden");

    const feedback=document.getElementById('feedbackInput').value.trim();
    if(selectedRating===0 || feedback===""){ alert("Please provide a rating and feedback!"); return; }

    try{
        const res = await fetch("feedback.php?action=create", {
            method:"POST",
            headers:{"Content-Type":"application/json"},
            body: JSON.stringify({rating:selectedRating,comment:feedback})
        });
        const data = await res.json();
        if(!res.ok || !data.success){ throw new Error(data.error || "Failed to submit"); }

        const entry=data.feedback;
        const container=document.getElementById("feedbackList");
        const starsStr='★'.repeat(entry.rating)+'☆'.repeat(5-entry.rating);
        const div=document.createElement("div");
        div.className="bg-white border p-4 rounded shadow-sm";
        div.innerHTML=`
          <div class="flex justify-between items-center mb-1">
            <span class="font-semibold text-indigo-600">${escapeHtml(entry.name||'Unknown')}</span>
            <span class="text-yellow-400">${starsStr}</span>
          </div>
          <p class="text-gray-700 whitespace-pre-line">${escapeHtml(entry.comment)}</p>
          <p class="text-xs text-gray-400 mt-1">${new Date(entry.created_at).toLocaleString()}</p>
        `;
        container.prepend(div);

        document.getElementById("feedbackInput").value="";
        selectedRating=0; updateStars(0);
        msgOk.classList.remove("hidden");
        setTimeout(()=>msgOk.classList.add("hidden"),2500);
    }catch(e){
        msgErr.textContent=e.message;
        msgErr.classList.remove("hidden");
        console.error(e);
    }
}

document.getElementById('submitBtn').addEventListener('click', submitFeedback);
window.addEventListener('DOMContentLoaded', loadFeedback);
</script>
</body>
</html>
