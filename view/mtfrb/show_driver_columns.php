<?php
require_once '../db_connect.php';
$stmt = $pdo->query('DESCRIBE driver');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    $test = $pdo->query('SELECT phone FROM driver LIMIT 1')->fetch();
    echo "<div class='alert alert-success'>Test query succeeded. Value: " . htmlspecialchars($test['phone'] ?? 'NULL') . "</div>";
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Test query failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Table Columns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Columns in <code>driver</code> Table</h3>
    <table class="table table-bordered w-auto mt-3">
        <thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>
        <tbody>
        <?php foreach ($columns as $col): ?>
            <tr>
                <td><?= htmlspecialchars($col['Field']) ?></td>
                <td><?= htmlspecialchars($col['Type']) ?></td>
                <td><?= htmlspecialchars($col['Null']) ?></td>
                <td><?= htmlspecialchars($col['Key']) ?></td>
                <td><?= htmlspecialchars($col['Default']) ?></td>
                <td><?= htmlspecialchars($col['Extra']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 