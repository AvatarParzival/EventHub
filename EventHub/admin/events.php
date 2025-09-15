<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$uploadDir = "../uploads/events/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("event_", true) . "." . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
            $imagePath = "uploads/events/" . $filename;
        }
    }

    if (isset($_POST['save_event'])) {
        if (!empty($_POST['id'])) {
            $query = "UPDATE events SET title=:title, description=:description, category=:category,
                      event_date=:event_date, location=:location,
                      price_general=:price_general, price_student=:price_student, price_vip=:price_vip";
            if ($imagePath) {
                $query .= ", image_url=:image_url";
            }
            $query .= " WHERE id=:id";

            $stmt = $db->prepare($query);
            $params = [
                ':id' => $_POST['id'],
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':event_date' => $_POST['event_date'],
                ':location' => $_POST['location'],
                ':price_general' => $_POST['price_general'],
                ':price_student' => $_POST['price_student'],
                ':price_vip' => $_POST['price_vip']
            ];
            if ($imagePath) {
                $params[':image_url'] = $imagePath;
            }
            $stmt->execute($params);
            $success = "Event updated successfully!";
        } else {
            $query = "INSERT INTO events (title, description, category, event_date, location, image_url,
                      price_general, price_student, price_vip)
                      VALUES (:title,:description,:category,:event_date,:location,:image_url,
                      :price_general,:price_student,:price_vip)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':event_date' => $_POST['event_date'],
                ':location' => $_POST['location'],
                ':image_url' => $imagePath ?? "",
                ':price_general' => $_POST['price_general'],
                ':price_student' => $_POST['price_student'],
                ':price_vip' => $_POST['price_vip']
            ]);
            $success = "Event added successfully!";
        }
    } elseif (isset($_POST['delete_event'])) {
        $stmt = $db->prepare("DELETE FROM events WHERE id=:id");
        $stmt->execute([':id' => $_POST['id']]);
        $success = "Event deleted successfully!";
    }
}

$events = $db->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminPic  = $_SESSION['admin_picture'] ?? null;
$initials  = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Events - EventHub</title>
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
        <a href="events.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md bg-gray-100 text-indigo-600">
          <i data-feather="calendar" class="mr-2 h-5 w-5"></i> Events
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
        <div class="flex flex-wrap justify-between items-center gap-3">
          <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manage Events</h1>
          <button id="openModalBtn" class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700 text-sm sm:text-base">+ Add Event</button>
        </div>

        <?php if (isset($success)): ?>
          <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-sm sm:text-base"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="mt-6 bg-white shadow rounded-lg p-4 sm:p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Event</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Category</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-4 py-2"></th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($events as $event): ?>
                <tr>
                  <td class="px-4 py-3 flex items-center whitespace-nowrap">
                    <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover mr-3" src="../<?php echo $event['image_url']; ?>" alt="">
                    <div>
                      <div class="font-medium text-gray-900"><?php echo $event['title']; ?></div>
                      <div class="text-xs text-gray-500"><?php echo $event['location']; ?></div>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800"><?php echo $event['category']; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">$<?php echo number_format($event['price_general'],2); ?></td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3 editBtn text-sm" data-event='<?php echo json_encode($event); ?>'>Edit</button>
                    <form method="POST" class="inline">
                      <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                      <button type="submit" name="delete_event" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Delete this event?');">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 sm:w-full max-w-2xl p-4 sm:p-6">
      <h2 id="modalTitle" class="text-lg sm:text-xl font-semibold mb-4">Add Event</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" id="eventId">
        <input type="text" name="title" id="eventTitle" placeholder="Event Title" required class="w-full border rounded p-2">
        <textarea name="description" id="eventDescription" rows="3" placeholder="Description" required class="w-full border rounded p-2"></textarea>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <select name="category" id="eventCategory" required class="border rounded p-2">
            <option value="">Select Category</option>
            <option>Technology</option><option>Business</option><option>Music</option>
            <option>Art</option><option>Sports</option><option>Education</option>
          </select>
          <input type="date" name="event_date" id="eventDate" required class="border rounded p-2">
        </div>
        <input type="text" name="location" id="eventLocation" placeholder="Location" required class="w-full border rounded p-2">
        <input type="file" name="image_file" id="eventImage" accept="image/*" class="w-full border rounded p-2">
        <img id="imagePreview" class="mt-2 h-24 hidden object-cover rounded border">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <input type="number" step="0.01" name="price_general" id="priceGeneral" placeholder="General Price" required class="border rounded p-2">
          <input type="number" step="0.01" name="price_student" id="priceStudent" placeholder="Student Price" required class="border rounded p-2">
          <input type="number" step="0.01" name="price_vip" id="priceVip" placeholder="VIP Price" required class="border rounded p-2">
        </div>
        <div class="flex justify-end">
          <button type="button" id="closeModalBtn" class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
          <button type="submit" name="save_event" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    feather.replace();
    const profileBtn = document.getElementById('profileMenuBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    profileBtn.addEventListener('click', () => profileDropdown.classList.toggle('hidden'));
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
