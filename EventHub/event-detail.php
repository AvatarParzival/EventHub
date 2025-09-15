<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('events.php');
}

$event_id = $_GET['id'];
$event = getEventById($event_id);

if (!$event) {
    redirect('events.php');
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("DELETE FROM registrations WHERE user_id = :uid AND event_id = :eid");
        $stmt->execute([
            ':uid' => $_SESSION['user_id'],
            ':eid' => $event_id
        ]);
        header("Location: user-dashboard.php?cancelled=1");
        exit();
    } else {
        $error = "You must be logged in to cancel.";
    }
}

$registration = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = :uid AND event_id = :eid");
    $stmt->execute([
        ':uid' => $_SESSION['user_id'],
        ':eid' => $event_id
    ]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['title']; ?> - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<?php include "header.php"; ?>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img class="w-full h-48 sm:h-64 object-cover" src="<?php echo $event['image_url']; ?>" alt="<?php echo $event['title']; ?>">
            
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                        <?php echo $event['category']; ?>
                    </span>
                    <span class="text-sm text-gray-500"><?php echo date('F j, Y', strtotime($event['event_date'])); ?></span>
                </div>
                
                <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900"><?php echo $event['title']; ?></h1>
                <p class="mt-2 text-gray-600 text-sm sm:text-base"><?php echo $event['location']; ?></p>
                
                <div class="mt-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">About this event</h2>
                    <p class="mt-2 text-gray-700 text-sm sm:text-base"><?php echo $event['description']; ?></p>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Ticket Options</h2>
                    
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border rounded-lg p-4">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">General Admission</h3>
                            <p class="mt-2 text-xl sm:text-2xl font-bold text-indigo-600">$<?php echo number_format($event['price_general'], 2); ?></p>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500">Standard access to the event</p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">Student</h3>
                            <p class="mt-2 text-xl sm:text-2xl font-bold text-indigo-600">$<?php echo number_format($event['price_student'], 2); ?></p>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500">For students with valid ID</p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900">VIP</h3>
                            <p class="mt-2 text-xl sm:text-2xl font-bold text-indigo-600">$<?php echo number_format($event['price_vip'], 2); ?></p>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500">Premium seating and perks</p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <?php if (isLoggedIn()): ?>
                            <?php if ($registration): ?>
                                <form method="post">
                                    <button type="submit" name="cancel_booking"
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php else: ?>
                                <button onclick="openRegistrationModal()" 
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    Register Now
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" 
                                class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Login to Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="registrationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md mx-auto p-5 border shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Register for <?php echo $event['title']; ?></h3>
                
                <form class="mt-4" action="process-registration.php" method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="ticket_type">
                            Ticket Type
                        </label>
                        <select name="ticket_type" id="ticket_type" class="shadow border rounded w-full py-2 px-3" required>
                            <option value="general">General Admission ($<?php echo number_format($event['price_general'], 2); ?>)</option>
                            <option value="student">Student ($<?php echo number_format($event['price_student'], 2); ?>)</option>
                            <option value="vip">VIP ($<?php echo number_format($event['price_vip'], 2); ?>)</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="promo_code">
                            Promo Code (Optional)
                        </label>
                        <input type="text" name="promo_code" id="promo_code" class="shadow border rounded w-full py-2 px-3">
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                        <button type="button" onclick="closeRegistrationModal()" class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
        function openRegistrationModal() {
            document.getElementById('registrationModal').classList.remove('hidden');
        }
        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
        }
    </script>
</body>
</html>