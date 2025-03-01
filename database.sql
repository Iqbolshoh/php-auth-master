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

-- ==============================  
-- 📥 DATA INSERTION (COMPLETE)  
-- ==============================  
-- DEFAULT PASSWORD: "IQBOLSHOH" (HASHED FOR SECURITY)  
-- ==============================  

-- INSERTING USERS
INSERT INTO users (first_name, last_name, email, username, password, role, profile_picture) VALUES 
('Iqbolshoh', 'Ilhomjonov', 'iilhomjonov777@gmail.com', 'iqbolshoh', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'admin', '790d5772254c72bf5c01d43920d8e6a6.jpeg'),
('User', 'User', 'user@iqbolshoh.uz', 'user',  '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027',  'user', 'default.png');

INSERT INTO users (first_name, last_name, email, username, password, role, profile_picture) VALUES 
('Muhammad', 'Saidov', 'muhammad.saidov@example.com', 'muhammad_s', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'admin', 'profile10.jpg'),
('Olim', 'Norboyev', 'olim.norboyev@example.com', 'olim_n', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'user', 'profile11.jpg'),
('Laylo', 'Yusupova', 'laylo.yusupova@example.com', 'laylo_y', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'admin', 'profile12.jpg'),
('Sanjar', 'Bekmurodov', 'sanjar.bekmurodov@example.com', 'sanjar_b', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'user', 'profile13.jpg'),
('Nilufar', 'Shodmonova', 'nilufar.shodmonova@example.com', 'nilufar_s', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'user', 'profile14.jpg'),
('Temur', 'Rahimov', 'temur.rahimov@example.com', 'temur_r', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'admin', 'profile15.jpg'),
('Malika', 'Iskandarova', 'malika.iskandarova@example.com', 'malika_i', '52be5ff91284c65bac56f280df55f797a5c505f7ef66317ff358e34791507027', 'admin', 'profile16.jpg');
