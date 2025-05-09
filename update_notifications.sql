USE user_auth;

-- Drop existing foreign key constraints if they exist
ALTER TABLE notifications
DROP FOREIGN KEY IF EXISTS notifications_ibfk_1,
DROP FOREIGN KEY IF EXISTS notifications_ibfk_2;

-- Drop existing indexes if they exist
ALTER TABLE notifications
DROP INDEX IF EXISTS driver_id,
DROP INDEX IF EXISTS passenger_id;

-- Drop the existing notifications table
DROP TABLE IF EXISTS notifications;

-- Add status and suspension_end columns to driver table if they do not exist
ALTER TABLE driver ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'approved';
ALTER TABLE driver ADD COLUMN suspension_end DATETIME NULL;

-- Add status and suspension_end columns to passenger table if they do not exist
ALTER TABLE passenger ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active';
ALTER TABLE passenger ADD COLUMN suspension_end DATETIME NULL;

-- Create notifications table if it does not exist
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'unread',
    created_at DATETIME NOT NULL,
    INDEX (user_id)
);

-- Create the notifications table with the correct structure
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('driver', 'passenger') NOT NULL,
    driver_id INT NULL,
    passenger_id INT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES passenger(passenger_id) ON DELETE CASCADE,
    CHECK (
        (user_type = 'driver' AND driver_id IS NOT NULL AND passenger_id IS NULL) OR
        (user_type = 'passenger' AND passenger_id IS NOT NULL AND driver_id IS NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci; 