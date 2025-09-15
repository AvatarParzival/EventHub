<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$category = isset($_GET['category']) ? $_GET['category'] : null;
$events = getEvents(null, $category);
$categories = ['Technology', 'Business', 'Music', 'Art', 'Sports', 'Education'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<?php include "header.php"; ?>
<body class="bg-gray-50">

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-1/4 order-2 lg:order-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter by Category</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="events.php" class="block px-3 py-2 rounded <?php echo !$category ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="events.php?category=<?php echo urlencode($cat); ?>" class="block px-3 py-2 rounded <?php echo $category == $cat ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                                <?php echo $cat; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="w-full lg:w-3/4 order-1 lg:order-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Upcoming Events</h1>
                
                <?php if (count($events) > 0): ?>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($events as $event): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                        <img class="w-full h-48 object-cover" src="<?php echo $event['image_url']; ?>" alt="<?php echo $event['title']; ?>">
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    <?php echo $event['category']; ?>
                                </span>
                                <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            <h3 class="mt-2 text-lg sm:text-xl font-semibold text-gray-900"><?php echo $event['title']; ?></h3>
                            <p class="mt-3 text-sm sm:text-base text-gray-500">
                                <?php echo substr($event['description'], 0, 100) . '...'; ?>
                            </p>
                            <div class="mt-6">
                                <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i data-feather="calendar" class="mx-auto h-12 w-12 text-gray-400"></i>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No events found</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no events in this category at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        feather.replace();
        AOS.init({ duration: 1000, once: true });
    </script>
</body>
</html>