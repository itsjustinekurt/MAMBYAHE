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