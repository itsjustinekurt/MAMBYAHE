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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Flat Fare Matrix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .main-container, main.container, .container.py-4, .content-container { margin-left: 260px !important; }
        .table thead th, .table tbody td { padding-left: 1.25rem; padding-right: 1.25rem; }
        .table th, .table td { min-width: 120px; }
        .table th.text-center, .table td.text-center { text-align: center; }
        .form-control, .form-select { font-size: 0.85rem; }
        .form-label { font-size: 0.85rem; color: #6b7280; }
        .btn-add { background-color: #4ade80; color: #000; font-weight: 600; font-size: 0.85rem; }
        .btn-add:hover, .btn-add:focus { background-color: #22c55e; color: #000; }
        .btn-clear { background-color: #d1d5db; color: #000; font-weight: 600; font-size: 0.85rem; }
        .btn-clear:hover, .btn-clear:focus { background-color: #9ca3af; color: #000; }
        .search-input { font-size: 0.85rem; padding-left: 2rem; }
        .search-icon { position: absolute; left: 0.5rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 0.85rem; }
        .header-icons i { font-size: 1.25rem; color: #111827; cursor: pointer; }
        .header-icons i + i { margin-left: 1.5rem; }
        .bg-light-custom { background-color: #f9fafb; }
        .shadow-custom { box-shadow: 0 4px 8px rgb(0 0 0 / 0.05); }
        .header-fixed {
            position: sticky;
            top: 0;
            z-index: 1050;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .table-responsive {
            overflow-x: auto;
            max-height: 70vh;
        }
        .table thead th {
            position: sticky;
            top: 0;
            background: #f9fafb;
            z-index: 2;
        }
        /* Sidebar styles */
        .sidebar-overlay { display: none !important; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: #8fa195; z-index: 1050; transform: none !important; transition: none; border-top-right-radius: 2rem; }
        .sidebar.open { transform: none; }
        .sidebar-header { display: flex; align-items: center; gap: 0.75rem; padding: 1.5rem 1rem 1rem 1.5rem; }
        .sidebar-logo { width: 40px; height: 40px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; }
        .sidebar-title { font-weight: 800; font-size: 1.2rem; color: #fff; }
        .sidebar-subtitle { font-size: 0.85rem; color: #e0e7ef; font-weight: 600; }
        .sidebar-nav { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.5rem; color: #222; font-weight: 500; font-size: 1.05rem; border-radius: 0.5rem; text-decoration: none; transition: background 0.2s; }
        .sidebar-link:hover { background: #e5e7eb; color: #111; }
        .sidebar-link i { font-size: 1.3rem; }
        .sidebar-close { display: none !important; }
        @media (max-width: 600px) { .sidebar { width: 90vw; } .main-container, main.container, .container.py-4, .content-container { margin-left: 0 !important; } }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-container" style="margin-left:260px;">
    <header class="d-flex align-items-center justify-content-between px-3 py-3 bg-white shadow-sm header-fixed">
        <h1 class="h5 fw-semibold m-0 user-select-none">Flat Fare Matrix</h1>
        <div class="header-icons d-flex align-items-center">
            <i class="fas fa-user" title="User Account"></i>
            <i class="fas fa-sign-out-alt" title="Logout"></i>
        </div>
    </header>
    <main class="container px-0" style="margin-top: 40px;">
        <div class="bg-white rounded-4 shadow p-4 d-flex flex-column flex-md-row gap-4">
            <section class="flex-grow-1">
                <form id="addFareForm" class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <input type="text" class="form-control w-auto" name="origin" placeholder="Origin" required />
                    <input type="text" class="form-control w-auto" name="destination" placeholder="Destination" required />
                    <input type="number" class="form-control w-auto" name="students_senior" placeholder="Student/Senior Fare" min="1" step="0.01" required />
                    <input type="number" class="form-control w-auto" name="fare" placeholder="Regular Fare" min="1" step="0.01" required />
                    <button type="submit" class="btn btn-add">Add</button>
                    <button type="reset" class="btn btn-clear">Clear</button>
                </form>
                <div class="mb-3">
                    <input type="text" id="fareSearch" class="form-control" placeholder="Search by origin or destination...">
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
            </section>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Sidebar logic
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarOpen = document.getElementById('sidebarOpen');
const sidebarClose = document.getElementById('sidebarClose');
if (sidebarOpen) {
    sidebarOpen.onclick = function () {
        sidebar.classList.add('open');
        sidebarOverlay.style.display = 'block';
    };
}
if (sidebarClose) {
    sidebarClose.onclick = function () {
        sidebar.classList.remove('open');
        sidebarOverlay.style.display = 'none';
    };
}
if (sidebarOverlay) {
    sidebarOverlay.onclick = function () {
        sidebar.classList.remove('open');
        sidebarOverlay.style.display = 'none';
    };
}
// Add fare
$('#addFareForm').on('submit', function(e) {
    e.preventDefault();
    $.post('', $(this).serialize()+'&add_fare=1', function(resp) {
        if (resp.status === 'success') location.reload();
        else alert(resp.message||'Error');
    }, 'json');
});
// Edit fare
$('#fareTable').on('click', '.edit-btn', function() {
    var row = $(this).closest('tr');
    var id = row.data('id');
    var origin = row.find('.origin').text();
    var destination = row.find('.destination').text();
    var students_senior = row.find('.students_senior').text().replace('₱','');
    var fare = row.find('.fare').text().replace('₱','');
    row.html('<td><input type="text" class="form-control form-control-sm" value="'+origin+'"></td>'+
             '<td><input type="text" class="form-control form-control-sm" value="'+destination+'"></td>'+
             '<td><input type="number" class="form-control form-control-sm" value="'+students_senior+'" min="1" step="0.01"></td>'+
             '<td><input type="number" class="form-control form-control-sm" value="'+fare+'" min="1" step="0.01"></td>'+
             '<td class="text-center">'+
             '<button class="btn btn-sm btn-success save-btn"><i class="fas fa-check"></i></button> '+
             '<button class="btn btn-sm btn-secondary cancel-btn"><i class="fas fa-times"></i></button></td>');
    row.addClass('editing');
});
// Save edit
$('#fareTable').on('click', '.save-btn', function() {
    var row = $(this).closest('tr');
    var id = row.data('id');
    var origin = row.find('input').eq(0).val();
    var destination = row.find('input').eq(1).val();
    var students_senior = row.find('input').eq(2).val();
    var fare = row.find('input').eq(3).val();
    $.post('', {edit_fare:1, id:id, origin:origin, destination:destination, students_senior:students_senior, fare:fare}, function(resp) {
        if (resp.status === 'success') location.reload();
        else alert(resp.message||'Error');
    }, 'json');
});
// Cancel edit
$('#fareTable').on('click', '.cancel-btn', function() { location.reload(); });
// Delete fare
$('#fareTable').on('click', '.delete-btn', function() {
    if (!confirm('Delete this fare?')) return;
    var row = $(this).closest('tr');
    var id = row.data('id');
    $.post('', {delete_fare:1, id:id}, function(resp) {
        if (resp.status === 'success') location.reload();
        else alert(resp.message||'Error');
    }, 'json');
});
// Pagination logic
const rowsPerPage = 20;
function paginateTable() {
    const rows = $('#fareTable tbody tr');
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    let currentPage = 1;
    function showPage(page) {
        currentPage = page;
        rows.hide();
        rows.slice((page-1)*rowsPerPage, page*rowsPerPage).show();
        renderPagination();
    }
    function renderPagination() {
        let html = '';
        html += `<li class="page-item${currentPage===1?' disabled':''}"><a class="page-link" href="#" data-page="prev">&laquo; Previous</a></li>`;
        let start = Math.max(1, currentPage-1);
        let end = Math.min(totalPages, start+2);
        if (end-start<2) start = Math.max(1, end-2);
        for (let i=start; i<=end; i++) {
            html += `<li class="page-item${i===currentPage?' active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        if (end<totalPages) {
            html += '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        html += `<li class="page-item${currentPage===totalPages?' disabled':''}"><a class="page-link" href="#" data-page="next">Next &raquo;</a></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" data-page="all">Show all &raquo;</a></li>`;
        $('#farePagination').html(html);
    }
    $('#farePagination').off('click').on('click', 'a.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page==='prev' && currentPage>1) showPage(currentPage-1);
        else if (page==='next' && currentPage<totalPages) showPage(currentPage+1);
        else if (page==='all') { rows.show(); renderPagination(); }
        else if (typeof page==='number') showPage(page);
    });
    showPage(1);
}
$(document).ready(function() { paginateTable(); });
// Re-paginate after search
$('#fareSearch').on('input', function() { paginateTable(); });
</script>
</body>
</html> 