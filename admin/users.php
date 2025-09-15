<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$adminName  = $_SESSION['admin_name'] ?? 'Admin';
$adminPic   = $_SESSION['admin_picture'] ?? null;
$initials   = strtoupper(substr($adminName, 0, 1));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $_POST['id']]);
    $success = "User deleted successfully!";
}

$users = $db->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$userDetails = null;
$registrations = [];
if (isset($_GET['view'])) {
    $id = $_GET['view'];
    $stmt = $db->prepare("SELECT id, name, email, created_at FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT e.title, r.ticket_type, r.registration_date 
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = :id
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([':id' => $id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users - EventHub</title>
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
        <a href="dashboard.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="home" class="mr-2 h-5 w-5 text-gray-400"></i> Dashboard
        </a>
        <a href="events.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="calendar" class="mr-2 h-5 w-5 text-gray-400"></i> Events
        </a>
        <a href="users.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md bg-gray-100 text-indigo-600">
          <i data-feather="users" class="mr-2 h-5 w-5"></i> Users
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
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>

        <?php if (isset($success)): ?>
          <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="mt-6 bg-white shadow rounded-lg p-4 sm:p-6 overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-500 uppercase">User</th>
                <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-500 uppercase">Email</th>
                <th class="px-3 sm:px-6 py-3 text-left font-medium text-gray-500 uppercase">Joined</th>
                <th class="px-3 sm:px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($users as $user): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-3 sm:px-6 py-4 flex items-center">
                  <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-bold mr-3">
                    <?php echo strtoupper(substr($user['name'],0,1)); ?>
                  </div>
                  <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                </td>
                <td class="px-3 sm:px-6 py-4 text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-3 sm:px-6 py-4 text-gray-500"><?php echo date("M j, Y", strtotime($user['created_at'])); ?></td>
                <td class="px-3 sm:px-6 py-4 text-right space-x-2">
                  <a href="users.php?view=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                  <form method="POST" class="inline">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this user?');">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if ($userDetails): ?>
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
          <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
            <div class="px-6 py-4 border-b flex justify-between items-center">
              <h3 class="text-lg font-semibold">User Details</h3>
              <a href="users.php" class="text-gray-500 hover:text-gray-700 text-xl">&times;</a>
            </div>
            <div class="p-6 text-sm">
              <p><strong>Name:</strong> <?php echo htmlspecialchars($userDetails['name']); ?></p>
              <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
              <p><strong>Joined:</strong> <?php echo date("M j, Y", strtotime($userDetails['created_at'])); ?></p>
              <h4 class="mt-4 font-semibold">Registrations</h4>
              <?php if (!empty($registrations)): ?>
                <ul class="mt-2 divide-y divide-gray-200">
                  <?php foreach ($registrations as $reg): ?>
                  <li class="py-2 flex justify-between text-sm">
                    <span><?php echo htmlspecialchars($reg['title']); ?> (<?php echo ucfirst($reg['ticket_type']); ?>)</span>
                    <span class="text-gray-500"><?php echo date("M j, Y", strtotime($reg['registration_date'])); ?></span>
                  </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-gray-500 mt-2">No registrations yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    feather.replace();
    document.getElementById("profileMenuBtn").addEventListener("click", function(){
      document.getElementById("profileDropdown").classList.toggle("hidden");
    });
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