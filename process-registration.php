<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];
$ticket_type = $_POST['ticket_type'];
$promo_code = isset($_POST['promo_code']) ? $_POST['promo_code'] : null;

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM registrations WHERE user_id = :user_id AND event_id = :event_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = "You are already registered for this event.";
    redirect("event-detail.php?id=$event_id");
}

$query = "INSERT INTO registrations (user_id, event_id, ticket_type, promo_code) VALUES (:user_id, :event_id, :ticket_type, :promo_code)";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':event_id', $event_id);
$stmt->bindParam(':ticket_type', $ticket_type);
$stmt->bindParam(':promo_code', $promo_code);

if ($stmt->execute()) {
    $_SESSION['success'] = "Successfully registered for the event!";
    redirect("user-dashboard.php");
} else {
    $_SESSION['error'] = "Registration failed. Please try again.";
    redirect("event-detail.php?id=$event_id");
}
?>