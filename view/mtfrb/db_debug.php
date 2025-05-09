<?php
require_once '../db_connect.php';
echo "<h2>Connected to DB: <code>{$db_name}</code></h2>";
$stmt = $pdo->query('SHOW TABLES');
echo '<h3>Tables:</h3><ul>';
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo '<li>' . htmlspecialchars($row[0]) . '</li>';
}
echo '</ul>';
// Show columns in driver table
try {
    $cols = $pdo->query('DESCRIBE driver')->fetchAll(PDO::FETCH_ASSOC);
    echo '<h3>Columns in <code>driver</code> table:</h3>';
    echo '<table class="table table-bordered w-auto mt-3"><thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead><tbody>';
    foreach ($cols as $col) {
        echo '<tr>';
        foreach ($col as $v) echo '<td>' . htmlspecialchars($v) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">DESCRIBE driver failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?> 