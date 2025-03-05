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

-- 3. FAILED LOGINS TABLE
CREATE TABLE failed_logins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    attempts INT NOT NULL DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==============================  
-- 📥 DATA INSERTION (COMPLETE)  
-- ==============================  
-- DEFAULT PASSWORD: "IQBOLSHOH" (HASHED FOR SECURITY)  
-- ==============================  

-- INSERTING USERS
INSERT INTO users (first_name, last_name, email, username, password, role, profile_picture) VALUES 
('Iqbolshoh', 'Ilhomjonov', 'iilhomjonov777@gmail.com', 'iqbolshoh', '$2y$10$FK1CG7WYwBbjC/rNTscuGOuH05Jqs.fxLxYB0rZ..Y1keEoDiEQMu', 'admin', '790d5772254c72bf5c01d43920d8e6a6.jpeg'),
('User', 'User', 'user@iqbolshoh.uz', 'user',  '$2y$10$FK1CG7WYwBbjC/rNTscuGOuH05Jqs.fxLxYB0rZ..Y1keEoDiEQMu',  'user', 'default.png');