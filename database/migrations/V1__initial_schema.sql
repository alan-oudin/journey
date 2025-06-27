-- V1__initial_schema.sql
-- Création de la base et des tables de base

CREATE DATABASE IF NOT EXISTS `journee_proches` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `journee_proches`;

-- Table admins
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table agents_inscriptions
CREATE TABLE `agents_inscriptions` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code_personnel` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Format: 7 chiffres + 1 lettre (ex: 1234567A)',
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de famille de l''agent',
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Prénom de l''agent',
  `nombre_proches` int NOT NULL DEFAULT '0' COMMENT 'Nombre de proches accompagnants (0 à 4)',
  `statut` enum('inscrit','present','absent','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inscrit',
  `heure_validation` timestamp NULL DEFAULT NULL COMMENT 'Heure de validation de présence (pointage automatique)',
  `heure_arrivee` time NOT NULL COMMENT 'Heure d''arrivée prévue - créneaux de 20 minutes',
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date et heure d''inscription',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date de dernière modification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des inscriptions pour la journée des proches';
