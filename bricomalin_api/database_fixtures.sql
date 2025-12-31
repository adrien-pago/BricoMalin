-- Données de test pour BricoMalin
-- À exécuter après la création des tables

-- Catégories
INSERT INTO category (name, `key`) VALUES
('Plomberie', 'plomberie'),
('Électricité', 'electricite'),
('Peinture', 'peinture'),
('Montage de meubles', 'montage-meubles'),
('Jardinage', 'jardinage'),
('Petits travaux', 'petits-travaux');

-- Utilisateurs (mots de passe: password123 - hash bcrypt)
-- Note: Vous devrez générer les hash avec password_hash() en PHP
-- Pour l'instant, voici des hash de test (password123)
INSERT INTO `user` (email, roles, password_hash, display_name, phone, created_at) VALUES
('user@example.com', '[]', '$2y$13$KIXxXxXxXxXxXxXxXxXxOeXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx', 'Jean Dupont', '0612345678', NOW()),
('pro@example.com', '[]', '$2y$13$KIXxXxXxXxXxXxXxXxXxOeXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx', 'Marie Martin', '0698765432', NOW());

-- Note: Les hash ci-dessus sont des exemples. 
-- Pour générer les vrais hash, utilisez cette commande PHP:
-- php -r "echo password_hash('password123', PASSWORD_DEFAULT);"

-- Profil PRO pour pro@example.com
INSERT INTO professional_profile (user_id, siret, status, created_at) VALUES
(2, '12345678901234', 'VERIFIED', NOW());

-- Demandes (nécessite que les catégories et utilisateurs existent)
-- Note: Ajustez les IDs selon vos données réelles
INSERT INTO job_request (requester_id, category_id, title, description, department, city, is_free, suggested_price, status, created_at) VALUES
(1, 1, 'Réparation robinet qui fuit', 'Mon robinet de cuisine fuit depuis plusieurs jours. J''ai besoin d''un plombier pour le réparer rapidement.', '75', 'Paris', 0, 80.00, 'OPEN', NOW()),
(1, 3, 'Peinture salon 25m²', 'Je souhaite repeindre mon salon. Les murs sont déjà préparés, il faut juste appliquer la peinture.', '92', 'Nanterre', 0, 200.00, 'OPEN', NOW());

-- Offre (nécessite que les demandes existent)
INSERT INTO offer (job_request_id, proposer_id, amount, message, status, created_at) VALUES
(1, 2, 75.00, 'Bonjour, je peux intervenir demain matin. Je propose 75€ pour cette réparation.', 'PENDING', NOW());

