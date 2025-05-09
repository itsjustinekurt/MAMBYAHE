USE user_auth;

-- Drop table if it exists
DROP TABLE IF EXISTS bookings;

-- Create bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    passenger_name VARCHAR(255) NOT NULL,
    pickup VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    seats INT NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    driver_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cash', 'card') NOT NULL DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 