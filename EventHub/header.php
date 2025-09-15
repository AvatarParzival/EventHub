<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/includes/Database.php";
$db = (new Database())->getConnection();

$isAdmin = isset($_SESSION['admin_id']);
$isUser  = isset($_SESSION['user_id']);

$currentPage = basename($_SERVER['PHP_SELF']);

$displayName = "Guest";
$userEmail   = "";
$profilePic  = null;
$initials    = "U";
$dashboardLink = "";
$profileLink   = "";

if ($isAdmin) {
    $stmt = $db->prepare("SELECT username, name, profile_picture, email FROM admins WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $displayName = $admin['name'] ?? $admin['username'];
        $userEmail   = $admin['email'] ?? "admin@eventhub.com";
        $profilePic  = $admin['profile_picture'] ?? null;
        $initials    = strtoupper(substr($displayName, 0, 1));
        $dashboardLink = "admin/dashboard.php";
        $profileLink   = "admin/profile.php";
    }
}
elseif ($isUser) {
    $stmt = $db->prepare("SELECT name, email, profile_picture FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $displayName = $user['name'];
        $userEmail   = $user['email'];
        $profilePic  = $user['profile_picture'] ?? null;
        $initials    = strtoupper(substr($displayName, 0, 1));
        $dashboardLink = "user-dashboard.php";
        $profileLink   = "profile.php";
    }
}

function navClass($page, $currentPage) {
    return $page === $currentPage
        ? "text-indigo-600 border-b-2 border-indigo-600"
        : "text-gray-600 hover:text-indigo-600";
}
?>
<link rel="icon" type="image/png" href="uploads/logo.png">

<nav class="bg-white shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <a href="index.php" class="flex items-center">
          <img src="uploads/logo.png" alt="EventHub Logo" class="h-10">
          <span class="ml-2 text-xl font-bold text-gray-900">EventHub</span>
        </a>
      </div>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center space-x-6">
        <a href="index.php" class="<?php echo navClass('index.php', $currentPage); ?>">Home</a>
        <a href="events.php" class="<?php echo navClass('events.php', $currentPage); ?>">Events</a>

        <?php if ($isAdmin || $isUser): ?>
          <a href="<?php echo $dashboardLink; ?>" class="<?php echo navClass(basename($dashboardLink), $currentPage); ?>">Dashboard</a>
          <!-- Profile Dropdown -->
          <div class="relative">
            <button id="profileMenuBtn" class="flex items-center focus:outline-none">
              <?php if (!empty($profilePic)): ?>
                <?php $picPath = str_replace('../', '', $profilePic); ?>
                <img class="h-8 w-8 rounded-full object-cover"
                     src="<?php echo htmlspecialchars($picPath); ?>"
                     alt="User">
              <?php else: ?>
                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                  <?php echo $initials; ?>
                </div>
              <?php endif; ?>
            </button>
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-50">
              <div class="px-4 py-2 border-b">
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($displayName); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userEmail); ?></p>
              </div>
              <a href="<?php echo $profileLink; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
              <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="<?php echo navClass('login.php', $currentPage); ?> px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Login</a>
          <a href="register.php" class="<?php echo navClass('register.php', $currentPage); ?> px-4 py-2 border border-indigo-600 text-indigo-600 rounded hover:bg-indigo-50">Register</a>
        <?php endif; ?>
      </div>

      <!-- Mobile Hamburger -->
      <div class="md:hidden flex items-center">
        <button id="mobileMenuBtn" class="text-gray-700 focus:outline-none">
          ☰
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobileMenu" class="hidden md:hidden px-4 pt-2 pb-4 space-y-2 bg-white border-t">
    <a href="index.php" class="block <?php echo navClass('index.php', $currentPage); ?>">Home</a>
    <a href="events.php" class="block <?php echo navClass('events.php', $currentPage); ?>">Events</a>

    <?php if ($isAdmin || $isUser): ?>
      <a href="<?php echo $dashboardLink; ?>" class="block <?php echo navClass(basename($dashboardLink), $currentPage); ?>">Dashboard</a>
      <a href="<?php echo $profileLink; ?>" class="block text-gray-600 hover:text-indigo-600">Profile</a>
      <a href="logout.php" class="block text-gray-600 hover:text-indigo-600">Logout</a>
    <?php else: ?>
      <a href="login.php" class="block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Login</a>
      <a href="register.php" class="block px-4 py-2 border border-indigo-600 text-indigo-600 rounded hover:bg-indigo-50">Register</a>
    <?php endif; ?>
  </div>
</nav>

<script>
  // Profile dropdown
  const profileBtn = document.getElementById('profileMenuBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  if(profileBtn){
    profileBtn.addEventListener('click', () => {
      profileDropdown.classList.toggle('hidden');
    });
  }

  // Mobile menu toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  if(mobileMenuBtn){
    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }
</script>
