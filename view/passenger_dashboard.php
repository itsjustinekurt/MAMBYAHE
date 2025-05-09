<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['passenger_id'])) {
    header("Location: login.php");
    exit();
}

// Get passenger details
$stmt = $pdo->prepare("SELECT * FROM passenger WHERE passenger_id = ?");
$stmt->execute([$_SESSION['passenger_id']]);
$passenger = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$passenger) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .card {
            margin-bottom: 20px;
        }
        .navbar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Passenger Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($passenger['fullname']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Booking Form -->
            <div class="col-md-8">
                <div class="card booking-form">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Book a Ride</h5>
                    </div>
                    <div class="card-body">
                        <form id="bookingForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pickup" class="form-label">Pickup Location</label>
                                    <input type="text" class="form-control" id="pickup" name="pickup" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="destination" class="form-label">Destination</label>
                                    <input type="text" class="form-control" id="destination" name="destination" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="seats" class="form-label">Number of Seats</label>
                                    <input type="number" class="form-control" id="seats" name="seats" min="1" max="4" value="1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="booking_date" class="form-label">Pickup Date</label>
                                    <input type="date" class="form-control" id="booking_date" name="booking_date" required>
                                    <div class="form-text">Select pickup date</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="booking_time" class="form-label">Pickup Time</label>
                                    <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                                    <div class="form-text">Select pickup time</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fare" class="form-label">Fare (₱)</label>
                                    <input type="number" class="form-control" id="fare" name="fare" required>
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="submitBooking">Book Now</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Your Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div id="bookingsList">
                            <!-- Bookings will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profile</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($passenger['fullname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($passenger['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($passenger['passenger_phone']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Set default date to today
        const today = new Date();
        const dateStr = today.toISOString().split('T')[0];
        $('#booking_date').val(dateStr);
        
        // Set default time to current time rounded to nearest 30 minutes
        const hours = today.getHours();
        const minutes = Math.ceil(today.getMinutes() / 30) * 30;
        const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
        $('#booking_time').val(timeStr);

        // Set min date to today
        $('#booking_date').attr('min', dateStr);

        // Load initial bookings
        loadBookings();
    });

    function loadBookings() {
        $.ajax({
            url: 'get_bookings.php',
            method: 'GET',
            success: function(response) {
                $('#bookingsList').html(response);
            },
            error: function() {
                $('#bookingsList').html('<p class="text-danger">Error loading bookings</p>');
            }
        });
    }

    $('#submitBooking').click(function() {
        const selectedDate = new Date($('#booking_date').val());
        const selectedTime = $('#booking_time').val();
        const now = new Date();
        
        // Create a Date object for the selected date and time
        const [hours, minutes] = selectedTime.split(':');
        selectedDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        // Check if the selected date and time is in the future
        if (selectedDate <= now) {
            alert('Please select a future date and time for pickup');
            return;
        }

        const formData = {
            pickup: $('#pickup').val(),
            destination: $('#destination').val(),
            seats: $('#seats').val(),
            booking_date: $('#booking_date').val(),
            booking_time: $('#booking_time').val(),
            fare: $('#fare').val()
        };

        $.ajax({
            url: 'create_booking.php',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Booking created successfully!');
                    // Clear form
                    $('#bookingForm')[0].reset();
                    // Set default date and time again
                    const today = new Date();
                    const dateStr = today.toISOString().split('T')[0];
                    $('#booking_date').val(dateStr);
                    const hours = today.getHours();
                    const minutes = Math.ceil(today.getMinutes() / 30) * 30;
                    const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    $('#booking_time').val(timeStr);
                    // Refresh bookings list
                    loadBookings();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error creating booking');
            }
        });
    });

    $('#booking_date').change(function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        
        // If selected date is today, set minimum time to current time
        if (selectedDate.toDateString() === today.toDateString()) {
            const hours = today.getHours();
            const minutes = Math.ceil(today.getMinutes() / 30) * 30;
            const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            $('#booking_time').attr('min', timeStr);
        } else {
            // If future date, allow any time
            $('#booking_time').removeAttr('min');
        }
    });
    </script>
</body>
</html> 