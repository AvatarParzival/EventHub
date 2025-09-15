<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once "./includes/database.php";
$db = (new Database())->getConnection();

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = "";

if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $profile_picture = $user['profile_picture'] ?? null;
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir,0777,true);
        $filename = time() . "_" . basename($_FILES['profile_picture']['name']);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile);
        $profile_picture = $targetFile;
    }

    $update = $db->prepare("UPDATE users SET name=:name, email=:email, profile_picture=:profile_picture WHERE id=:id");
    $update->execute([
        ':name' => $name,
        ':email' => $email,
        ':profile_picture' => $profile_picture,
        ':id' => $user_id
    ]);

    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_picture;

    $message = "Profile updated successfully!";
}

if (isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($old, $user['password'])) {
        $message = "Old password incorrect.";
    } elseif ($new !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password=:password WHERE id=:id");
        $update->execute([':password' => $hashed, ':id' => $user_id]);
        $message = "Password changed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile - EventHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php include "header.php"; ?>
<body class="bg-gray-50">
  <div class="max-w-3xl mx-auto mt-6 sm:mt-10 bg-white p-4 sm:p-6 shadow rounded-lg">
    <h1 class="text-xl sm:text-2xl font-bold mb-6">My Profile</h1>

    <?php if($message): ?>
      <div class="mb-4 p-3 text-sm sm:text-base bg-blue-100 text-blue-700 rounded"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="space-y-4 mb-8">
      <div>
        <label class="block text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
        <?php if (!empty($user['profile_picture'])): ?>
          <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="h-16 w-16 rounded-full mb-2 object-cover">
        <?php else: ?>
          <div class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold mb-2">
            <?php echo strtoupper(substr($user['name'],0,1)); ?>
          </div>
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

  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <script>feather.replace();</script>
</body>
</html>