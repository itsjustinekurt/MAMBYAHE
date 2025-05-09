USE user_auth;

-- Drop the existing bookings table
DROP TABLE IF EXISTS bookings;

-- Create the bookings table with the correct structure
CREATE TABLE bookings (
    id INT NOT NULL AUTO_INCREMENT,
    passenger_name VARCHAR(100) NOT NULL,
    pickup VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    seats INT NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    driver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending',
    booked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY driver_id (driver_id),
    CONSTRAINT bookings_ibfk_1 FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Verify the table structure
SHOW CREATE TABLE bookings;

-- Add driver_response_time column to bookings table
ALTER TABLE bookings ADD COLUMN driver_response_time TIMESTAMP NULL DEFAULT NULL;

-- Add other necessary columns if they don't exist
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS driver_id INT NULL,
ADD COLUMN IF NOT EXISTS passenger_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS pickup VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS destination VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS seats INT NULL,
ADD COLUMN IF NOT EXISTS fare DECIMAL(10,2) NULL;

-- Add foreign key constraint
ALTER TABLE bookings 
ADD CONSTRAINT IF NOT EXISTS bookings_driver_id_fk 
FOREIGN KEY (driver_id) REFERENCES driver(driver_id) 
ON DELETE SET NULL; 