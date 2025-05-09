USE user_auth;

-- Drop existing foreign key constraints if they exist
ALTER TABLE bookings
DROP FOREIGN KEY IF EXISTS bookings_ibfk_1;

-- Drop existing indexes if they exist
ALTER TABLE bookings
DROP INDEX IF EXISTS driver_id;

-- Modify the table structure
ALTER TABLE bookings
MODIFY COLUMN passenger_name varchar(100) NOT NULL,
MODIFY COLUMN pickup varchar(100) NOT NULL,
MODIFY COLUMN destination varchar(100) NOT NULL,
MODIFY COLUMN seats int(11) NOT NULL,
MODIFY COLUMN fare decimal(10,2) NOT NULL,
MODIFY COLUMN driver_id int(11) NOT NULL,
ADD COLUMN IF NOT EXISTS status enum('pending','accepted','completed','cancelled') DEFAULT 'pending',
ADD INDEX IF NOT EXISTS driver_id (driver_id),
ADD CONSTRAINT bookings_ibfk_1 FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE; 