-- DROP DATABASE IF EXISTS
DROP DATABASE IF EXISTS auth_master;

-- CREATE DATABASE
CREATE DATABASE auth_master;
USE auth_master;

-- 1. USERS TABLE
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. ACTIVE SESSIONS TABLE
CREATE TABLE active_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================
-- 📥 DATA INSERTION (Complate)
-- =============================

-- USERS
INSERT INTO users (first_name, last_name, email, username, password, role, profile_picture) VALUES
('Iqbolshoh', 'Ilhomjonov', 'iilhomjonov777@gmail.com', 'iqbolshoh', '1f254bb82e64bde20137a2922989f6f57529c98e34d146b523a47898702b7231', 'admin', '11703422cd9e03a57c355b6f24bcef40.jpeg'),
('User', 'user', 'user@iqbolshoh.uz', 'user', '1f254bb82e64bde20137a2922989f6f57529c98e34d146b523a47898702b7231', 'user', 'default.png');

-- ACTIVE SESSION INSERT
INSERT INTO active_sessions (user_id, device_name, ip_address, session_token) VALUES
(1, 'iPhone 15 Pro Max', '192.168.1.10', 'session_token_123'),
(1, 'Windows 11 PC', '192.168.1.15', 'session_token_456');
