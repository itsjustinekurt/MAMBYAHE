USE user_auth;

-- Modify the is_online column in the driver table to be an ENUM
ALTER TABLE driver
MODIFY COLUMN is_online ENUM('online', 'offline') DEFAULT 'offline';

-- Update existing records to use the new ENUM values
UPDATE driver SET is_online = 'offline' WHERE is_online = 0;
UPDATE driver SET is_online = 'online' WHERE is_online = 1; 