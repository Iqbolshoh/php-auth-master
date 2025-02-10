CREATE DATABASE IF NOT EXISTS auth_master;

USE auth_master;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE active_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Login: Iqbolshoh
-- Password: 1
INSERT INTO
    users (
        first_name,
        last_name,
        email,
        username,
        password
    )
VALUES
    (
        'Iqbolshoh',
        'Ilhomjonov',
        'iilhomjonov777@gmail.com',
        'iqbolshoh',
        '65c2a32982abe41b1e6ff888d351ee6b7ade33affd4a595667ea7db910aecaa8'
    ),
    (
        'user',
        'user',
        'user@gmail.com',
        'user',
        '65c2a32982abe41b1e6ff888d351ee6b7ade33affd4a595667ea7db910aecaa8'
    )