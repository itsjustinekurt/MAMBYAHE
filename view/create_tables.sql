USE user_auth;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS driver_locations;

-- Modify the is_online column in the driver table to be an ENUM
ALTER TABLE driver
MODIFY COLUMN is_online ENUM('online', 'offline') DEFAULT 'offline';

-- Create driver_locations table if it doesn't exist
CREATE TABLE IF NOT EXISTS driver_locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    driver_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE
);

-- Create notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS notifications (
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
);

-- Modify the existing driver table
ALTER TABLE driver
MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) DEFAULT NULL;

-- Add foreign key constraints after tables are created
ALTER TABLE driver_locations
ADD CONSTRAINT fk_driver_locations_drivers
FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE; 