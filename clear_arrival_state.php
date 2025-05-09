<?php
session_start();

// Clear the arrival state from session
if (isset($_SESSION['arrival_state'])) {
    unset($_SESSION['arrival_state']);
    // Ensure session is written
    session_write_close();
}

header('Content-Type: application/json');
echo json_encode(['success' => true]); 