<?php
require_once __DIR__ . "/Database.php";

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getEvents($limit = null, $category = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM events";
    
    if ($category) {
        $query .= " WHERE category = :category";
    }
    
    $query .= " ORDER BY event_date ASC";
    
    if ($limit) {
        $query .= " LIMIT :limit";
    }
    
    $stmt = $db->prepare($query);
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventById($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM events WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserRegistrations($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT r.*, e.title, e.event_date, e.location 
              FROM registrations r 
              JOIN events e ON r.event_id = e.id 
              WHERE r.user_id = :user_id 
              ORDER BY r.registration_date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>