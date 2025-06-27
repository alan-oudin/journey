-- V2__ajout_colonne_check_utilisateur.sql
ALTER TABLE agents_inscriptions ADD COLUMN `fast_food_check` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Colonne de vérification';

