<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    // Get unread notifications count
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_type = 'admin' AND status = 'unread'";
    $result = $conn->query($sql);
    $unread_count = $result->fetch_assoc()['count'];

    // Get notifications with related data
    $sql = "
        SELECT n.*, 
               p.fullname as passenger_name, 
               p.profile_pic, 
               p.phone as passenger_phone,
               d.fullname as driver_name,
               b.status as booking_status, 
               b.pickup_location, 
               b.destination, 
               b.seats, 
               b.fare,
               b.id as booking_id
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        LEFT JOIN driver d ON b.driver_id = d.driver_id
        WHERE n.user_type = 'admin'
        ORDER BY n.created_at DESC 
        LIMIT 10
    ";
    
    $result = $conn->query($sql);
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Format the notifications for display
    $formatted_notifications = array_map(function($notif) {
        return [
            'notification_id' => $notif['notification_id'],
            'type' => $notif['type'],
            'message' => $notif['message'],
            'status' => $notif['status'],
            'created_at' => $notif['created_at'],
            'booking_id' => $notif['booking_id'],
            'passenger_name' => $notif['passenger_name'] ?? 'Unknown',
            'driver_name' => $notif['driver_name'] ?? 'Unknown',
            'booking_details' => [
                'pickup' => $notif['pickup_location'] ?? 'N/A',
                'destination' => $notif['destination'] ?? 'N/A',
                'status' => $notif['booking_status'] ?? 'N/A'
            ]
        ];
    }, $notifications);

    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => $formatted_notifications
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_admin_notifications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 