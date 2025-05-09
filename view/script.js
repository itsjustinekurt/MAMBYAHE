// Function to handle booking status updates
async function updateBookingStatus(bookingId, status) {
    try {
        const response = await fetch('process_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                booking_id: bookingId,
                status: status
            })
        });

        const result = await response.json();

        if (result.success) {
            // Show bottom sheet if booking is accepted
            if (result.showBottomSheet) {
                showBottomSheet(result.booking);
            }

            // If driver arrives, hide bottom sheet and show review modal
            if (status === 'in_progress') {
                hideBottomSheet();
                showReviewModal(result.booking.id, result.booking.driver_id);
            }

            // Show review modal if trip is completed
            if (result.showReviewModal && status !== 'in_progress') {
                showReviewModal(result.booking.id, result.booking.driver_id);
            }

            // Only show success message for statuses other than 'in_progress'
            if (status !== 'in_progress') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }

            // Refresh notifications
            loadNotifications();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update booking status'
        });
    }
}

// Function to show bottom sheet
function showBottomSheet(booking) {
    const bottomSheet = document.getElementById('bottomSheet');
    const bottomSheetContent = document.getElementById('bottomSheetContent');
    
    // Update bottom sheet content with booking details
    bottomSheetContent.innerHTML = `
        <div class="booking-details">
            <h5>Booking Details</h5>
            <p><strong>Pickup:</strong> ${booking.pickup}</p>
            <p><strong>Destination:</strong> ${booking.destination}</p>
            <p><strong>Seats:</strong> ${booking.seats}</p>
            <p><strong>Total Fare:</strong> ₱${parseFloat(booking.fare).toFixed(2)}</p>
        </div>
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="updateBookingStatus(${booking.id}, 'in_progress')">
                Arrived
            </button>
            <button class="btn btn-success" onclick="updateBookingStatus(${booking.id}, 'completed')">
                Complete Trip
            </button>
        </div>
    `;
    
    // Show bottom sheet
    bottomSheet.classList.add('show');
}

// Function to hide bottom sheet
function hideBottomSheet() {
    const bottomSheet = document.getElementById('bottomSheet');
    bottomSheet.classList.remove('show');
}

// Function to handle notification clicks
function handleNotificationClick(notification) {
    if (notification.type === 'Driver Arrived' && notification.booking_id) {
        showReviewModal(notification.booking_id, notification.driver_id);
    }
}

// Function to load and display notifications
async function loadNotifications() {
    try {
        const response = await fetch('get_notifications.php');
        const result = await response.json();

        if (result.success) {
            const notificationsList = document.getElementById('notificationsList');
            if (notificationsList) {
                notificationsList.innerHTML = result.notifications.map(notification => `
                    <div class="notification-item" onclick="handleNotificationClick(${JSON.stringify(notification)})">
                        <div class="notification-content">
                            <h6>${notification.type}</h6>
                            <p>${notification.message}</p>
                            <small>${new Date(notification.created_at).toLocaleString()}</small>
                        </div>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    const closeBottomSheet = document.getElementById('closeBottomSheet');
    if (closeBottomSheet) {
        closeBottomSheet.addEventListener('click', hideBottomSheet);
    }

    // Load notifications initially
    loadNotifications();

    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
}); 