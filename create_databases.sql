-- Create three databases for UPS-IMS
-- Run this script using MySQL command line or phpMyAdmin

CREATE DATABASE IF NOT EXISTS ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Show created databases
SHOW DATABASES LIKE 'ups';
SHOW DATABASES LIKE 'urs';
SHOW DATABASES LIKE 'ucs';









