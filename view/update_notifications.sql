USE user_auth;

DELIMITER //

-- Drop existing foreign key constraints if they exist
ALTER TABLE notifications
DROP FOREIGN KEY IF EXISTS notifications_ibfk_1,
DROP FOREIGN KEY IF EXISTS notifications_ibfk_2,
DROP FOREIGN KEY IF EXISTS fk_notifications_booking,
DROP FOREIGN KEY IF EXISTS fk_notifications_driver,
DROP FOREIGN KEY IF EXISTS fk_notifications_passenger//

-- Drop existing indexes if they exist
ALTER TABLE notifications
DROP INDEX IF EXISTS driver_id,
DROP INDEX IF EXISTS passenger_id//

-- First, add the booking_id column if it doesn't exist
ALTER TABLE notifications
ADD COLUMN IF NOT EXISTS booking_id INT NULL,
ADD COLUMN IF NOT EXISTS passenger_phone VARCHAR(20) DEFAULT '',
ADD COLUMN IF NOT EXISTS passenger_pic VARCHAR(255) DEFAULT 'default_profile.jpg'//

-- Then modify the rest of the table structure
ALTER TABLE notifications
DROP COLUMN IF EXISTS user_id,
MODIFY COLUMN user_type ENUM('driver', 'passenger') NOT NULL,
ADD COLUMN IF NOT EXISTS driver_id INT NULL AFTER user_type,
ADD COLUMN IF NOT EXISTS passenger_id INT NULL AFTER driver_id,
ADD COLUMN IF NOT EXISTS type VARCHAR(50) NOT NULL DEFAULT 'Notification',
ADD COLUMN IF NOT EXISTS status ENUM('read', 'unread') NOT NULL DEFAULT 'unread',
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD INDEX IF NOT EXISTS driver_id (driver_id),
ADD INDEX IF NOT EXISTS passenger_id (passenger_id),
ADD INDEX IF NOT EXISTS booking_id (booking_id),
ADD CONSTRAINT notifications_ibfk_1 FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE,
ADD CONSTRAINT notifications_ibfk_2 FOREIGN KEY (passenger_id) REFERENCES passenger(passenger_id) ON DELETE CASCADE,
ADD CONSTRAINT fk_notifications_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
ADD CONSTRAINT notifications_chk_1 CHECK (
    (user_type = 'driver' AND driver_id IS NOT NULL AND passenger_id IS NULL) OR
    (user_type = 'passenger' AND passenger_id IS NOT NULL AND driver_id IS NULL)
)//

DELIMITER ; 