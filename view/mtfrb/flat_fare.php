<?php
session_start();
require_once '../db_connect.php';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fare'])) {
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $fare = floatval($_POST['fare']);
    $students_senior = floatval($_POST['students_senior']);
    if ($origin && $destination && $fare > 0 && $students_senior > 0) {
        $stmt = $conn->prepare('INSERT INTO fare_matrix (origin, destination, fare, students_senior) VALUES (?, ?, ?, ?)');
        $stmt->execute([$origin, $destination, $fare, $students_senior]);
        echo json_encode(['status'=>'success']);
        exit;
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid input']);
        exit;
    }
}
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_fare'])) {
    $id = intval($_POST['id']);
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $fare = floatval($_POST['fare']);
    $students_senior = floatval($_POST['students_senior']);
    if ($id && $origin && $destination && $fare > 0 && $students_senior > 0) {
        $stmt = $conn->prepare('UPDATE fare_matrix SET origin=?, destination=?, fare=?, students_senior=? WHERE id=?');
        $stmt->execute([$origin, $destination, $fare, $students_senior, $id]);
        echo json_encode(['status'=>'success']);
        exit;
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid input']);
        exit;
    }
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fare'])) {
    $id = intval($_POST['id']);
    if ($id) {
        $stmt = $conn->prepare('DELETE FROM fare_matrix WHERE id=?');
        $stmt->execute([$id]);
        echo json_encode(['status'=>'success']);
        exit;
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid ID']);
        exit;
    }
}
// Fetch fares
$fares = [];
$result = $conn->query('SELECT * FROM fare_matrix ORDER BY origin, destination');
while ($row = $result->fetch_assoc()) {
    $fares[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Flat Fare - MTFRB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container-fluid" style="margin-left:260px;">
        <main class="flex-grow-1">
            <div class="container-fluid bg-white-opacity rounded-3 shadow-sm mt-4">
                <div class="p-4">
                    <h1 class="h5 fw-semibold mb-4">Flat Fare Matrix</h1>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <form id="addFareForm" class="d-flex align-items-center gap-2">
                            <input type="text" class="form-control w-auto" name="origin" placeholder="Origin" required />
                            <input type="text" class="form-control w-auto" name="destination" placeholder="Destination" required />
                            <input type="number" class="form-control w-auto" name="students_senior" placeholder="Student/Senior Fare" min="1" step="0.01" required />
                            <input type="number" class="form-control w-auto" name="fare" placeholder="Regular Fare" min="1" step="0.01" required />
                            <button type="submit" class="btn btn-add">Add</button>
                            <button type="reset" class="btn btn-clear">Clear</button>
                        </form>
                        <div class="flex-grow-1">
                            <input type="text" id="fareSearch" class="form-control" placeholder="Search by origin or destination...">
                        </div>
                    </div>
                    <div class="table-responsive rounded-3 border border-secondary-subtle shadow-sm" style="max-height: 70vh;">
                        <table class="table table-sm mb-0 text-nowrap align-middle" id="fareTable">
                            <thead class="bg-light-custom">
                                <tr>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th class="text-center">Student/Senior Fare</th>
                                    <th class="text-center">Regular Fare</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fares as $f): ?>
                                <tr data-id="<?= $f['id'] ?>">
                                    <td class="origin"><?= htmlspecialchars($f['origin']) ?></td>
                                    <td class="destination"><?= htmlspecialchars($f['destination']) ?></td>
                                    <td class="students_senior text-center">₱<?= number_format($f['students_senior'],2) ?></td>
                                    <td class="fare text-center">₱<?= number_format($f['fare'],2) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit-btn" type="button"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger delete-btn" type="button"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Pagination" class="mt-3">
                        <ul class="pagination pagination-sm mb-0" id="farePagination"></ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fare matrix logic
        $(document).ready(function() {
            const rowsPerPage = 10;
            let currentPage = 1;
            let searchQuery = '';

            function paginateTable() {
                const rows = $('#fareTable tbody tr');
                const totalRows = rows.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage);
                
                // Update pagination
                $('#farePagination').empty();
                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        $('#farePagination').append(
                            $('<li>').addClass('page-item' + (i === currentPage ? ' active' : '')).append(
                                $('<a>').addClass('page-link').attr('href', '#').text(i)
                            )
                        );
                    }
                }

                // Show/hide rows based on current page
                rows.each(function(index) {
                    const row = $(this);
                    const page = Math.floor(index / rowsPerPage) + 1;
                    row.toggle(page === currentPage);
                });
            }

            // Search functionality
            $('#fareSearch').on('input', function() {
                searchQuery = $(this).val().toLowerCase();
                $('#fareTable tbody tr').each(function() {
                    const row = $(this);
                    const matches = row.find('.origin, .destination').text().toLowerCase().includes(searchQuery);
                    row.toggle(matches);
                });
                paginateTable();
            });

            // Pagination click handler
            $('#farePagination').on('click', '.page-link', function(e) {
                e.preventDefault();
                currentPage = parseInt($(this).text());
                paginateTable();
            });

            // Add fare form submission
            $('#addFareForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: 'process_fare.php',
                    type: 'POST',
                    data: formData + '&action=add',
                    success: function(response) {
                        if (response.status === 'success') {
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                });
            });

            // Edit fare
            $('#fareTable').on('click', '.edit-btn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const origin = row.find('.origin').text();
                const destination = row.find('.destination').text();
                const students_senior = parseFloat(row.find('.students_senior').text().replace('₱', ''));
                const fare = parseFloat(row.find('.fare').text().replace('₱', ''));

                // Fill form with current values
                $('#addFareForm').find('[name="origin"]').val(origin);
                $('#addFareForm').find('[name="destination"]').val(destination);
                $('#addFareForm').find('[name="students_senior"]').val(students_senior);
                $('#addFareForm').find('[name="fare"]').val(fare);
                $('#addFareForm').data('edit-id', id);
            });

            // Update form action on edit
            $('#addFareForm').on('submit', function(e) {
                const editId = $(this).data('edit-id');
                if (editId) {
                    e.preventDefault();
                    const formData = $(this).serialize();
                    $.ajax({
                        url: 'process_fare.php',
                        type: 'POST',
                        data: formData + '&action=edit&id=' + editId,
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        }
                    });
                }
            });

            // Delete fare
            $('#fareTable').on('click', '.delete-btn', function() {
                if (confirm('Are you sure you want to delete this fare?')) {
                    const row = $(this).closest('tr');
                    const id = row.data('id');
                    $.ajax({
                        url: 'process_fare.php',
                        type: 'POST',
                        data: 'action=delete&id=' + id,
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 