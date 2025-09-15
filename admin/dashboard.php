<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../includes/Database.php";
$db = (new Database())->getConnection();

$event_count   = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
$user_count    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$tickets_sold  = $db->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

$revenue_sql = "
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
";
$revenue = $db->query($revenue_sql)->fetchColumn();

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$registrations = $db->query("
    SELECT u.name, u.email, r.ticket_type, e.title, r.registration_date
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registration_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EventHub - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../uploads/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="bg-gray-100">
  <div class="flex min-h-screen flex-col md:flex-row">
    <div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 md:static md:flex md:flex-col transition-transform duration-200 ease-in-out z-50">
      <div class="flex items-center px-4 py-4">
        <img src="../uploads/logo.png" alt="Logo" class="h-8 sm:h-10">
        <span class="ml-2 text-lg sm:text-xl font-bold text-gray-900">EventHub</span>
      </div>
      <nav class="px-2 space-y-1">
        <a href="dashboard.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md bg-gray-100 text-indigo-600">
          <i data-feather="home" class="mr-2 h-5 w-5"></i> Dashboard
        </a>
        <a href="events.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="calendar" class="mr-2 h-5 w-5 text-gray-400"></i> Events
        </a>
        <a href="users.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="users" class="mr-2 h-5 w-5 text-gray-400"></i> Users
        </a>
        <a href="profile.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="settings" class="mr-2 h-5 w-5 text-gray-400"></i> Settings
        </a>
      </nav>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div class="bg-white shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8 flex justify-between h-14 sm:h-16 items-center">
          <div class="flex items-center gap-2">
            <button id="hamburgerBtn" class="md:hidden p-2 rounded-md text-gray-600 hover:bg-gray-200">
              <i data-feather="menu" class="h-6 w-6"></i>
            </button>
            <a href="../index.php" target="_blank" class="p-1 rounded-full text-gray-400 hover:text-gray-600" title="Go to Homepage">
              <i data-feather="external-link" class="h-5 w-5 sm:h-6 sm:w-6"></i>
            </a>
          </div>
          <div class="relative">
            <?php
            $adminName = $_SESSION['admin_name'] ?? 'Admin';
            $adminPic  = $_SESSION['admin_picture'] ?? null;
            $initials  = strtoupper(substr($adminName,0,1));
            ?>
            <button id="profileMenuBtn" class="flex items-center focus:outline-none">
              <?php if (!empty($adminPic)): ?>
                <img class="h-8 w-8 rounded-full object-cover" src="<?php echo htmlspecialchars($adminPic); ?>" alt="Admin">
              <?php else: ?>
                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                  <?php echo $initials; ?>
                </div>
              <?php endif; ?>
            </button>
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-50">
              <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Go to Profile</a>
              <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        </div>
      </div>

      <div class="px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Dashboard</h1>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex items-center">
            <div class="bg-indigo-500 rounded-md p-2 sm:p-3">
              <i data-feather="calendar" class="h-5 w-5 sm:h-6 sm:w-6 text-white"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Events</p>
              <p class="text-lg sm:text-2xl font-semibold text-gray-900"><?php echo $event_count; ?></p>
            </div>
          </div>
          <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex items-center">
            <div class="bg-green-500 rounded-md p-2 sm:p-3">
              <i data-feather="users" class="h-5 w-5 sm:h-6 sm:w-6 text-white"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Users</p>
              <p class="text-lg sm:text-2xl font-semibold text-gray-900"><?php echo $user_count; ?></p>
            </div>
          </div>
          <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex items-center">
            <div class="bg-yellow-500 rounded-md p-2 sm:p-3">
              <i data-feather="dollar-sign" class="h-5 w-5 sm:h-6 sm:w-6 text-white"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Revenue</p>
              <p class="text-lg sm:text-2xl font-semibold text-gray-900">$<?php echo number_format($revenue,2); ?></p>
            </div>
          </div>
          <div class="bg-white shadow rounded-lg p-4 sm:p-6 flex items-center">
            <div class="bg-purple-500 rounded-md p-2 sm:p-3">
              <i data-feather="check-circle" class="h-5 w-5 sm:h-6 sm:w-6 text-white"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tickets Sold</p>
              <p class="text-lg sm:text-2xl font-semibold text-gray-900"><?php echo $tickets_sold; ?></p>
            </div>
          </div>
        </div>

        <div class="mt-8">
          <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="text-lg font-medium text-gray-900">Recent Events</h2>
            <a href="events.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all</a>
          </div>
          <div class="mt-4 bg-white shadow rounded-md overflow-hidden">
            <ul class="divide-y divide-gray-200">
              <?php foreach($events as $event): ?>
              <li>
                <div class="px-4 py-3 sm:px-6 flex justify-between items-center flex-wrap gap-2">
                  <p class="text-sm font-medium text-indigo-600 truncate"><?php echo htmlspecialchars($event['title']); ?></p>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?php echo (strtotime($event['event_date']) > time()) ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                    <?php echo (strtotime($event['event_date']) > time()) ? 'Upcoming' : 'Active'; ?>
                  </span>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="mt-8">
          <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="text-lg font-medium text-gray-900">Recent Registrations</h2>
            <a href="users.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all</a>
          </div>
          <div class="mt-4 bg-white shadow rounded-md overflow-hidden">
            <ul class="divide-y divide-gray-200">
              <?php foreach($registrations as $reg): ?>
              <li>
                <div class="px-4 py-3 sm:px-6 flex justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                  <div>
                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reg['name']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($reg['email']); ?></p>
                  </div>
                  <div class="text-left sm:text-right">
                    <p class="text-sm font-medium text-indigo-600"><?php echo htmlspecialchars($reg['title']); ?></p>
                    <p class="text-xs text-gray-500 capitalize"><?php echo $reg['ticket_type']; ?></p>
                    <p class="text-xs text-gray-400"><?php echo date("M j, Y", strtotime($reg['registration_date'])); ?></p>
                  </div>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    feather.replace();
    const profileBtn = document.getElementById('profileMenuBtn');
    if(profileBtn){
      const profileDropdown = document.getElementById('profileDropdown');
      profileBtn.addEventListener('click', () => {
        profileDropdown.classList.toggle('hidden');
      });
    }
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileSidebar = document.getElementById('mobileSidebar');
    if(hamburgerBtn && mobileSidebar){
      hamburgerBtn.addEventListener('click', () => {
        mobileSidebar.classList.toggle('-translate-x-full');
      });
    }
  </script>
</body>
</html>
