-- Création de la base de données
CREATE DATABASE IF NOT EXISTS intranet_entreprise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE intranet_entreprise;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_online BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Table des messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Table des équipes
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB;

-- Table de liaison utilisateurs-équipes
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_member (team_id, user_id),
    INDEX idx_team_id (team_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- Table des événements
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB;

-- Insertion de données de test
INSERT INTO users (username, email, password, full_name, avatar, is_online) VALUES
('sary.andria', 'sary@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sary Andria', 'https://i.pravatar.cc/48?img=32', TRUE),
('marie.dubois', 'marie@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie Dubois', 'https://i.pravatar.cc/48?img=45', TRUE),
('jean.martin', 'jean@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean Martin', 'https://i.pravatar.cc/48?img=12', TRUE),
('amin.rakoto', 'amin@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Amin Rakoto', 'https://i.pravatar.cc/48?img=15', FALSE),
('laura.rh', 'laura@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Laura RH', 'https://i.pravatar.cc/48?img=25', TRUE),
('admin', 'admin@entreprise.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', NULL, TRUE);

-- Insertion des messages de test
INSERT INTO messages (user_id, content, created_at) VALUES
(1, 'Bienvenue à tous dans notre nouvelle plateforme de communication interne ! 🎉', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(5, 'Rappel : réunion générale vendredi à 9h dans la salle de conférence.', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Insertion des équipes
INSERT INTO teams (name, description) VALUES
('Développement', 'Équipe de développement logiciel'),
('Ressources Humaines', 'Département RH'),
('Marketing', 'Équipe marketing et communication');

-- Liaison équipes-membres
INSERT INTO team_members (team_id, user_id) VALUES
(1, 2), (1, 3), (1, 4),  -- Développement
(2, 5), (2, 1);           -- RH

-- Insertion des événements
INSERT INTO events (title, description, event_date, created_by) VALUES
('Atelier innovation', 'Session de brainstorming pour les nouveaux projets', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1),
('Formation cybersécurité', 'Formation obligatoire sur la sécurité informatique', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 5),
('Réunion trimestrielle', 'Bilan du trimestre et objectifs', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1);