<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once "./includes/database.php";
$db = (new Database())->getConnection();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $reg_id = $_POST['reg_id'];
    $stmt = $db->prepare("DELETE FROM registrations WHERE id = :rid AND user_id = :uid");
    $stmt->execute([':rid' => $reg_id, ':uid' => $user_id]);
    header("Location: user-dashboard.php?cancelled=1");
    exit();
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userName  = $user['name'];
$userEmail = $user['email'];
$userPic   = $user['profile_picture'] ?? null;
$initials  = strtoupper(substr($userName, 0, 1));

$stmt = $db->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$registered_events = $stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT e.*, r.id as reg_id, r.ticket_type, r.registration_date 
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = :id AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
");
$stmt->execute([':id' => $user_id]);
$upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT e.*, r.ticket_type, r.registration_date 
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = :id AND e.event_date < CURDATE()
    ORDER BY e.event_date DESC
");
$stmt->execute([':id' => $user_id]);
$past_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT COALESCE(SUM(
        CASE r.ticket_type
            WHEN 'general' THEN e.price_general
            WHEN 'student' THEN e.price_student
            WHEN 'vip' THEN e.price_vip
            ELSE 0
        END
    ),0) as total
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = :id
");
$stmt->execute([':id' => $user_id]);
$total_spent = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventHub - User Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<?php include "header.php"; ?>
<body class="bg-gray-50">
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Dashboard</h1>
    <p class="mt-2 text-base sm:text-lg text-gray-600">Welcome back, <?php echo htmlspecialchars($userName); ?>!</p>

    <?php if (isset($_GET['cancelled'])): ?>
      <div class="mt-4 p-3 bg-green-100 text-green-800 rounded text-sm sm:text-base">
        Booking cancelled successfully.
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mt-6">
      <div class="bg-white shadow rounded-lg p-6 flex items-center">
        <div class="bg-indigo-500 p-3 rounded-md"><i data-feather="calendar" class="h-6 w-6 text-white"></i></div>
        <div class="ml-4"><p class="text-sm text-gray-500">Registered Events</p><p class="text-xl sm:text-2xl font-semibold"><?php echo $registered_events; ?></p></div>
      </div>
      <div class="bg-white shadow rounded-lg p-6 flex items-center">
        <div class="bg-green-500 p-3 rounded-md"><i data-feather="check-circle" class="h-6 w-6 text-white"></i></div>
        <div class="ml-4"><p class="text-sm text-gray-500">Upcoming</p><p class="text-xl sm:text-2xl font-semibold"><?php echo count($upcoming_events); ?></p></div>
      </div>
      <div class="bg-white shadow rounded-lg p-6 flex items-center">
        <div class="bg-yellow-500 p-3 rounded-md"><i data-feather="clock" class="h-6 w-6 text-white"></i></div>
        <div class="ml-4"><p class="text-sm text-gray-500">Past</p><p class="text-xl sm:text-2xl font-semibold"><?php echo count($past_events); ?></p></div>
      </div>
      <div class="bg-white shadow rounded-lg p-6 flex items-center">
        <div class="bg-purple-500 p-3 rounded-md"><i data-feather="dollar-sign" class="h-6 w-6 text-white"></i></div>
        <div class="ml-4"><p class="text-sm text-gray-500">Total Spent</p><p class="text-xl sm:text-2xl font-semibold">$<?php echo number_format($total_spent,2); ?></p></div>
      </div>
    </div>

    <div class="mt-10 bg-white shadow rounded-lg">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold">Upcoming Events</h2></div>
      <div class="p-4 sm:p-6 grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach($upcoming_events as $event): ?>
        <div class="border rounded-lg p-4 bg-gray-50">
          <div class="flex justify-between items-center flex-wrap gap-2">
            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs"><?php echo htmlspecialchars($event['category']); ?></span>
            <span class="text-xs text-gray-500"><?php echo date("M j, Y", strtotime($event['event_date'])); ?></span>
          </div>
          <h4 class="mt-2 font-semibold text-sm sm:text-base"><?php echo htmlspecialchars($event['title']); ?></h4>
          <p class="text-xs sm:text-sm text-gray-500"><?php echo htmlspecialchars($event['location']); ?></p>
          <div class="mt-3 flex justify-between items-center">
            <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="text-xs sm:text-sm text-indigo-600">View Details</a>
            <form method="post" onsubmit="return confirm('Cancel this booking?');">
              <input type="hidden" name="reg_id" value="<?php echo $event['reg_id']; ?>">
              <button type="submit" name="cancel_booking" class="text-xs sm:text-sm text-red-600">Cancel</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($upcoming_events)) echo "<p class='text-gray-500 text-sm'>No upcoming events.</p>"; ?>
      </div>
    </div>

    <div class="mt-10 bg-white shadow rounded-lg">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold">Past Events</h2></div>
      <div class="p-4 sm:p-6 grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach($past_events as $event): ?>
        <div class="border rounded-lg p-4 bg-gray-50">
          <div class="flex justify-between items-center flex-wrap gap-2">
            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs"><?php echo htmlspecialchars($event['category']); ?></span>
            <span class="text-xs text-gray-500"><?php echo date("M j, Y", strtotime($event['event_date'])); ?></span>
          </div>
          <h4 class="mt-2 font-semibold text-sm sm:text-base"><?php echo htmlspecialchars($event['title']); ?></h4>
          <p class="text-xs sm:text-sm text-gray-500"><?php echo htmlspecialchars($event['location']); ?></p>
          <div class="mt-3 flex justify-between items-center">
            <span class="text-xs sm:text-sm text-gray-600">Completed</span>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($past_events)) echo "<p class='text-gray-500 text-sm'>No past events.</p>"; ?>
      </div>
    </div>
  </div>
  <script>feather.replace();</script>
</body>
</html>