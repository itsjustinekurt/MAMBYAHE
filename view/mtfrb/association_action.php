<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mtfrb') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once '../db_connect.php';
try {
    // Delete
    if (isset($_POST['delete']) && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM associations WHERE id = ?');
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
        exit;
    }
    // Edit
    if (isset($_POST['id']) && !isset($_POST['delete'])) {
        $stmt = $pdo->prepare('UPDATE associations SET name=?, chairman=?, chairman_contact=?, address=?, num_members=? WHERE id=?');
        $stmt->execute([
            $_POST['name'],
            $_POST['chairman'],
            $_POST['chairman_contact'],
            $_POST['address'],
            $_POST['num_members'],
            $_POST['id']
        ]);
        echo json_encode(['success' => true]);
        exit;
    }
    // Add
    if (isset($_POST['name'], $_POST['chairman'], $_POST['chairman_contact'], $_POST['address'], $_POST['num_members'])) {
        $stmt = $pdo->prepare('INSERT INTO associations (name, chairman, chairman_contact, address, num_members) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['name'],
            $_POST['chairman'],
            $_POST['chairman_contact'],
            $_POST['address'],
            $_POST['num_members']
        ]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 