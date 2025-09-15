<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../includes/Database.php";
$db = (new Database())->getConnection();

$admin_id = $_SESSION['admin_id'];

$stmt = $db->prepare("SELECT * FROM admins WHERE id = :id");
$stmt->bindParam(':id', $admin_id);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$adminName  = $admin['name'] ?? 'Admin';
$adminPic   = $admin['profile_picture'] ?? null;
$initials   = strtoupper(substr($adminName, 0, 1));

$message = "";

if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];

    $profile_picture = $admin['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "../uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir,0777,true);
        $filename = time() . "_" . basename($_FILES['profile_picture']['name']);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile);
        $profile_picture = "../uploads/" . $filename;
    }

    $update = $db->prepare("UPDATE admins SET name=:name, username=:username, profile_picture=:profile_picture WHERE id=:id");
    $update->bindParam(':name', $name);
    $update->bindParam(':username', $username);
    $update->bindParam(':profile_picture', $profile_picture);
    $update->bindParam(':id', $admin_id);
    $update->execute();

    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_picture'] = $profile_picture;

    $message = "Profile updated successfully!";
}

if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($old_password, $admin['password'])) {
        $message = "Old password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE admins SET password=:password WHERE id=:id");
        $update->bindParam(':password', $hashed);
        $update->bindParam(':id', $admin_id);
        $update->execute();
        $message = "Password updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - EventHub</title>
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
        <a href="users.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
          <i data-feather="users" class="mr-2 h-5 w-5 text-gray-400"></i> Users
        </a>
        <a href="profile.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md bg-gray-100 text-indigo-600">
          <i data-feather="settings" class="mr-2 h-5 w-5"></i> Settings
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
              <?php if ($adminPic): ?>
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
    
      <div class="max-w-3xl mx-auto mt-6 sm:mt-10 bg-white shadow rounded-lg p-4 sm:p-6">
        <h1 class="text-xl sm:text-2xl font-bold mb-6">Admin Profile</h1>

        <?php if($message): ?>
          <div class="mb-4 p-3 bg-blue-100 text-blue-700 rounded text-sm sm:text-base"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="space-y-4 mb-8">
          <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
            <?php if (!empty($admin['profile_picture'])): ?>
              <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile" class="h-16 w-16 rounded-full mb-2 object-cover">
            <?php endif; ?>
            <input type="file" name="profile_picture" class="mt-1 block w-full text-sm">
          </div>
          <button type="submit" name="update_profile" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm sm:text-base">Update Profile</button>
        </form>

        <h2 class="text-lg sm:text-xl font-semibold mb-4">Change Password</h2>
        <form action="" method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Old Password</label>
            <input type="password" name="old_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="new_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" name="confirm_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
          </div>
          <button type="submit" name="change_password" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm sm:text-base">Change Password</button>
        </form>
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