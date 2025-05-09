<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Rate Your Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" id="reviewBookingId" name="booking_id">
                    <input type="hidden" id="reviewDriverId" name="driver_id">
                    
                    <div class="mb-4 text-center">
                        <h6>How was your ride?</h6>
                        <div class="rating">
                            <input type="radio" name="rating" value="5" id="5"><label for="5">☆</label>
                            <input type="radio" name="rating" value="4" id="4"><label for="4">☆</label>
                            <input type="radio" name="rating" value="3" id="3"><label for="3">☆</label>
                            <input type="radio" name="rating" value="2" id="2"><label for="2">☆</label>
                            <input type="radio" name="rating" value="1" id="1"><label for="1">☆</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Share your experience</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" 
                                placeholder="Tell us about your ride experience..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitReview">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    margin: 20px 0;
}

.rating input {
    display: none;
}

.rating label {
    cursor: pointer;
    font-size: 40px;
    color: #ddd;
    padding: 5px;
    transition: color 0.2s ease-in-out;
}

.rating input:checked ~ label,
.rating label:hover,
.rating label:hover ~ label {
    color: #ffd700;
}

.notification-item {
    cursor: pointer;
    padding: 10px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-content {
    padding: 5px;
}

.notification-content h6 {
    margin-bottom: 5px;
    color: #333;
}

.notification-content p {
    margin-bottom: 5px;
    color: #666;
    white-space: pre-line;
}

.notification-content small {
    color: #999;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    const reviewForm = document.getElementById('reviewForm');
    const submitReviewBtn = document.getElementById('submitReview');

    // Function to show review modal
    window.showReviewModal = function(bookingId, driverId) {
        document.getElementById('reviewBookingId').value = bookingId;
        document.getElementById('reviewDriverId').value = driverId;
        reviewModal.show();
    };

    // Handle review submission
    submitReviewBtn.addEventListener('click', async function() {
        const formData = new FormData(reviewForm);
        const data = {
            booking_id: formData.get('booking_id'),
            driver_id: formData.get('driver_id'),
            rating: formData.get('rating'),
            feedback: formData.get('feedback')
        };

        if (!data.rating) {
            Swal.fire({
                icon: 'warning',
                title: 'Rating Required',
                text: 'Please select a rating before submitting'
            });
            return;
        }

        try {
            const response = await fetch('submit_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                reviewModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Thank You!',
                    text: 'Your feedback helps us improve our service',
                    showConfirmButton: false,
                    timer: 1500
                });
                // Clear form
                reviewForm.reset();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to submit review'
            });
        }
    });
});
</script> 