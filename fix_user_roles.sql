-- Script SQL pour corriger les rôles des utilisateurs
-- Ce script convertit les rôles stockés comme des entiers en format JSON array

-- Vérifier la structure actuelle
SELECT id, email, roles FROM `user` LIMIT 5;

-- Corriger les rôles
UPDATE `user` 
SET roles = JSON_ARRAY('ROLE_USER') 
WHERE roles = '0' OR roles = 0 OR roles IS NULL OR roles = '';

UPDATE `user` 
SET roles = JSON_ARRAY('ROLE_ADMIN') 
WHERE roles = '1' OR roles = 1;

-- Vérifier le résultat
SELECT id, email, roles FROM `user` LIMIT 5;
