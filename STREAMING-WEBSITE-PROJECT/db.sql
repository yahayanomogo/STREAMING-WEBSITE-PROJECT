-- Création de la base
CREATE DATABASE IF NOT EXISTS streaming CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE streaming;

-- Table utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Exemple d'admin
INSERT INTO users (username, password, role) VALUES 
('admin', 'admin123', 'admin');

-- Table films
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    description_ru TEXT DEFAULT NULL,
    genre VARCHAR(100) NOT NULL,
    release_year YEAR NOT NULL,
    duration INT NOT NULL,
    director VARCHAR(255) NOT NULL,
    age_rating INT DEFAULT NULL,
    poster_path VARCHAR(500) DEFAULT NULL,
    banner_image VARCHAR(500) DEFAULT NULL,
    video_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table séries
CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    description_ru TEXT DEFAULT NULL,
    genre VARCHAR(100) NOT NULL,
    release_year YEAR NOT NULL,
    age_rating INT DEFAULT NULL,
    poster_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table épisodes (lié aux séries)
CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) DEFAULT NULL,
    episode_number INT NOT NULL,
    season_number INT NOT NULL,
    video_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Étendre table users
ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL;

-- Table favoris
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('movie','series') NOT NULL,
    content_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table historique de visionnage
CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('movie','series','episode') NOT NULL,
    content_id INT NOT NULL,
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table notes
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('movie','series') NOT NULL,
    content_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >=1 AND rating <=5),
    rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, content_type, content_id)
) ENGINE=InnoDB;

-- Table commentaires
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('movie','series') NOT NULL,
    content_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
