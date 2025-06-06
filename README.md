
# Guide pour initialiser la BDD

-- Créer la base de données (si elle n'existe pas)
CREATE DATABASE IF NOT EXISTS journee_proches 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE journee_proches;

-- Supprimer la table si elle existe (pour repartir proprement)
DROP TABLE IF EXISTS agents_inscriptions;

-- Créer la table des agents
CREATE TABLE agents_inscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code_personnel VARCHAR(10) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    service VARCHAR(150) NOT NULL,
    nombre_proches INT NOT NULL DEFAULT 0,
    heure_arrivee TIME NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Créer les index
CREATE INDEX idx_code_personnel ON agents_inscriptions(code_personnel);
CREATE INDEX idx_heure_arrivee ON agents_inscriptions(heure_arrivee);

-- Insérer les données d'exemple
INSERT INTO agents_inscriptions (code_personnel, nom, prenom, service, nombre_proches, heure_arrivee) VALUES
('1234', 'MARTIN', 'Pierre', 'Informatique', 2, '09:00:00'),
('5678', 'DUBOIS', 'Marie', 'Ressources Humaines', 3, '09:20:00'),
('9012', 'BERNARD', 'Jean', 'Comptabilité', 1, '09:40:00'),
('3456', 'LEROY', 'Sophie', 'Marketing', 0, '10:00:00'),
('7890', 'MOREAU', 'Paul', 'Direction', 4, '13:00:00'),
('2468', 'ROUSSEAU', 'Emma', 'Communication', 2, '13:20:00'),
('1357', 'GARCIA', 'Lucas', 'Technique', 1, '14:00:00'),
('9753', 'THOMAS', 'Lea', 'Commercial', 3, '14:40:00');
