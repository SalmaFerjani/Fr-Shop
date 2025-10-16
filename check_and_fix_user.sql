-- Script pour vérifier et corriger l'utilisateur de test

-- 1. Vérifier les utilisateurs existants
SELECT id, email, first_name, last_name, roles, is_active, created_at 
FROM `user` 
ORDER BY created_at DESC;

-- 2. Supprimer l'utilisateur existant s'il y a des problèmes
DELETE FROM `user` WHERE email = 'ferjanisalma50@gmail.com';

-- 3. Insérer un nouvel utilisateur avec des données correctes
INSERT INTO `user` (
    email, 
    roles, 
    password, 
    first_name, 
    last_name, 
    phone, 
    address, 
    postal_code, 
    city, 
    country, 
    created_at, 
    updated_at, 
    is_active
) VALUES (
    'ferjanisalma50@gmail.com',
    JSON_ARRAY('ROLE_USER'),
    '$2y$13$YourHashedPasswordHere', -- Remplacez par le vrai hash
    'Salma',
    'Ferjani',
    '24242424',
    'Tunisie',
    '1145',
    'Tunis',
    'France',
    NOW(),
    NOW(),
    1
);

-- 4. Vérifier le résultat
SELECT id, email, first_name, last_name, roles, is_active 
FROM `user` 
WHERE email = 'ferjanisalma50@gmail.com';
