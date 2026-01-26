-- Create databases for UPS-IMS (original and department 2)
-- Run this script using MySQL command line or phpMyAdmin

-- Original databases
CREATE DATABASE IF NOT EXISTS ups CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS urs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ucs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Department 2 databases
CREATE DATABASE IF NOT EXISTS ups2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS urs2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ucs2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Show created databases
SHOW DATABASES LIKE 'ups%';
SHOW DATABASES LIKE 'urs%';
SHOW DATABASES LIKE 'ucs%';









