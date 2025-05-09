<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['passenger_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get passenger details
$stmt = $pdo->prepare("SELECT * FROM passenger WHERE passenger_id = ?");
$stmt->execute([$_SESSION['passenger_id']]);
$passenger = $stmt->fetch(PDO::FETCH_ASSOC);

// Get success/error messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear messages after retrieving them
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File MTFRB Complaint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            File MTFRB Complaint
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="complaintForm" action="submit_complaint.php" method="POST" enctype="multipart/form-data">
                            <!-- Complainant Information -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Complainant Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="complainant_name" value="<?= htmlspecialchars($passenger['fullname']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" name="complainant_phone" value="<?= htmlspecialchars($passenger['phone']) ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="complainant_email" value="<?= htmlspecialchars($passenger['email']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="complainant_address" rows="2" required><?= htmlspecialchars($passenger['address']) ?></textarea>
                                </div>
                            </div>

                            <!-- Driver Information -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Driver Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Driver Name</label>
                                        <input type="text" class="form-control" id="driverName" name="driver_name" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Plate Number</label>
                                        <input type="text" class="form-control" id="plateNumber" name="plate_number" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Incident Details -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Incident Details</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Incident</label>
                                        <input type="date" class="form-control" id="incidentDate" name="incident_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time of Incident</label>
                                        <input type="time" class="form-control" id="incidentTime" name="incident_time" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pickup Location</label>
                                        <input type="text" class="form-control" id="pickupLocation" name="pickup_location" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Destination</label>
                                        <input type="text" class="form-control" id="destination" name="destination" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection</label>
                                    <input type="text" class="form-control" id="rejectionReason" name="rejection_reason" readonly>
                                </div>
                            </div>

                            <!-- Complaint Details -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Complaint Details</h5>
                                <div class="mb-3">
                                    <label class="form-label">Nature of Complaint</label>
                                    <select class="form-select" name="complaint_type" required>
                                        <option value="">Select complaint type</option>
                                        <option value="discriminatory">Discriminatory Rejection</option>
                                        <option value="unprofessional">Unprofessional Behavior</option>
                                        <option value="inappropriate">Inappropriate Reason</option>
                                        <option value="no_show">Driver No-Show</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Detailed Description</label>
                                    <textarea class="form-control" name="complaint_details" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Supporting Documents (if any)</label>
                                    <input type="file" class="form-control" name="supporting_docs[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                                    <div class="form-text">You can upload screenshots, photos, or any relevant documents</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Complaint
                                </button>
                                <a href="../dashboardPassenger.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get stored report details from session storage
        document.addEventListener('DOMContentLoaded', function() {
            const reportDetails = JSON.parse(sessionStorage.getItem('reportDetails') || '{}');
            
            // Fill in the form fields
            document.getElementById('driverName').value = reportDetails.driverName || '';
            document.getElementById('plateNumber').value = reportDetails.plateNumber || '';
            document.getElementById('pickupLocation').value = reportDetails.pickup || '';
            document.getElementById('destination').value = reportDetails.dropoff || '';
            document.getElementById('rejectionReason').value = reportDetails.reason || '';
            
            // Set incident date and time
            if (reportDetails.datetime) {
                const incidentDate = new Date(reportDetails.datetime);
                document.getElementById('incidentDate').value = incidentDate.toISOString().split('T')[0];
                document.getElementById('incidentTime').value = incidentDate.toTimeString().slice(0, 5);
            }
            
            // Clear the stored details after using them
            sessionStorage.removeItem('reportDetails');
        });
    </script>
</body>
</html> 