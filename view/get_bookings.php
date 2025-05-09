<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT b.*, d.fullname as driver_name, d.plate_no, d.phone as driver_phone
        FROM bookings b
        LEFT JOIN driver d ON b.driver_id = d.driver_id
        WHERE b.passenger_id = :passenger_id
        ORDER BY b.created_at DESC
    ");
    
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $html = '';
    foreach ($bookings as $booking) {
        $statusClass = '';
        switch ($booking['status']) {
            case 'pending':
                $statusClass = 'bg-warning';
                break;
            case 'confirmed':
                $statusClass = 'bg-success';
                break;
            case 'rejected':
                $statusClass = 'bg-danger';
                break;
            default:
                $statusClass = 'bg-secondary';
        }
        
        $html .= '<div class="booking-item mb-3 p-3 border rounded">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<div>';
        $html .= '<h6 class="mb-1">Booking #' . htmlspecialchars($booking['id']) . '</h6>';
        $html .= '<p class="mb-1">From: ' . htmlspecialchars($booking['pickup'] ?? 'Not specified') . '</p>';
        $html .= '<p class="mb-1">To: ' . htmlspecialchars($booking['dropoff'] ?? 'Not specified') . '</p>';
        $html .= '<p class="mb-1">Date: ' . (isset($booking['booking_date']) ? date('M d, Y', strtotime($booking['booking_date'])) : 'Not specified') . '</p>';
        $html .= '<p class="mb-1">Time: ' . (isset($booking['booking_time']) ? date('h:i A', strtotime($booking['booking_time'])) : 'Not specified') . '</p>';
        $html .= '</div>';
        $html .= '<div class="text-end">';
        $html .= '<span class="badge ' . $statusClass . '">' . ucfirst($booking['status'] ?? 'unknown') . '</span>';
        if (!empty($booking['driver_name'])) {
            $html .= '<p class="mb-1">Driver: ' . htmlspecialchars($booking['driver_name']) . '</p>';
            $html .= '<p class="mb-1">Plate: ' . htmlspecialchars($booking['plate_no'] ?? 'Not available') . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    echo $html;
} catch (PDOException $e) {
    echo '<p class="text-danger">Error loading bookings: ' . $e->getMessage() . '</p>';
}
?> 