<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$events = getEvents(3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Discover Exciting Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
.hero-gradient {
    background: linear-gradient(
      135deg,
      #1DBAC1 0%,
      #0E7C86 100%
    );
}
.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
.event-card {
    transition: all 0.3s ease;
}
.btn-primary {
    background-color: #1DBAC1;
    color: white;
}
.btn-primary:hover {
    background-color: #FF7A1A;
}
    </style>
</head>
<?php include "header.php"; ?>
<body class="bg-gray-50">

    <div class="bg-teal-600 text-orange-200">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl text-white">
                    Discover Amazing Events
                </h1>
                <p class="mt-6 max-w-xl mx-auto text-lg text-white sm:text-xl">
                    Join thousands of people at our exciting events. Learn, network, and have fun!
                </p>
                <div class="mt-8 sm:mt-10">
                    <a href="events.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-teal-700 bg-white hover:bg-orange-50">
                        Browse Events
                        <i data-feather="arrow-right" class="ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-2xl font-extrabold text-gray-900 sm:text-3xl lg:text-4xl">
                Featured Events
            </h2>
            <p class="mt-4 max-w-2xl text-base sm:text-lg text-gray-500 mx-auto">
                Check out our most popular upcoming events
            </p>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($events as $event): ?>
            <div class="event-card bg-white rounded-lg overflow-hidden shadow-md flex flex-col" data-aos="fade-up">
                <img class="w-full h-48 object-cover" src="<?php echo $event['image_url']; ?>" alt="<?php echo $event['title']; ?>">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
                            <?php echo $event['category']; ?>
                        </span>
                        <span class="ml-auto text-sm text-gray-500"><?php echo date('F j, Y', strtotime($event['event_date'])); ?></span>
                    </div>
                    <h3 class="mt-2 text-lg sm:text-xl font-semibold text-gray-900"><?php echo $event['title']; ?></h3>
                    <p class="mt-3 text-sm sm:text-base text-gray-500">
                        <?php echo substr($event['description'], 0, 100) . '...'; ?>
                    </p>
                    <div class="mt-6">
                        <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-teal-600">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div>
                    <h2 class="text-2xl font-extrabold text-white sm:text-3xl lg:text-4xl">
                        <span class="block">Ready to dive in?</span>
                        <span class="block text-orange-200">Live your Event now!!</span>
                    </h2>
                    <p class="mt-3 max-w-2xl text-base sm:text-lg leading-6 text-orange-100">
                        Are you an adventurer? Join our platform and reach thousands of exciting adventures.
                    </p>
                </div>
                <div class="mt-8 lg:mt-0">
                    <div class="inline-flex rounded-md shadow">
                        <a href="admin/login.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-teal-700 bg-white hover:bg-orange-50">
                            Get started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        feather.replace();
        AOS.init({ duration: 1000, once: true });
    </script>
</body>
</html>
<?php include "footer.php"; ?>