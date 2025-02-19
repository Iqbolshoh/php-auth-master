CREATE DATABASE IF NOT EXISTS auth_master;

USE auth_master;

CREATE TABLE IF NOT EXISTS users (
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

CREATE TABLE IF NOT EXISTS active_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- admin and user (password: 'Iqbolsoh$7')
INSERT INTO
    users (
        first_name,
        last_name,
        email,
        username,
        password,
        role
    )
VALUES
    (
        'Iqbolshoh',
        'Ilhomjonov',
        'iilhomjonov777@gmail.com',
        'iqbolshoh',
        '1f254bb82e64bde20137a2922989f6f57529c98e34d146b523a47898702b7231',
        'admin'
    ),
    (
        'User',
        'User',
        'user@gmail.com',
        'user',
        '1f254bb82e64bde20137a2922989f6f57529c98e34d146b523a47898702b7231',
        'user'
    );