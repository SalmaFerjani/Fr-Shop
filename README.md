# La Boutique Française 🇫🇷

Une plateforme e-commerce moderne développée avec Symfony 6.1 pour la vente de sacs et bijoux fabriqués en France.

## 🚀 Déploiement avec Docker

### Prérequis
- Docker
- Docker Compose

### Installation locale

```bash
# Cloner le projet
git clone https://github.com/SalmaFerjani/Fr-Shop.git
cd Fr-Shop

# Lancer les services
docker-compose up -d

# Créer la base de données
docker exec -it fr-shop-app-1 php bin/console doctrine:database:create

# Exécuter les migrations
docker exec -it fr-shop-app-1 php bin/console doctrine:migrations:migrate

# Ajouter des données de test
docker exec -it fr-shop-app-1 php bin/console app:seed-products

# Créer un compte admin
docker exec -it fr-shop-app-1 php bin/console app:create-admin
```

### Accès
- **Application** : http://localhost:8000
- **Admin** : http://localhost:8000/admin (admin@boutiquefrancaise.fr / admin123)
- **Base de données** : localhost:3307
- **Mail (Mailpit)** : http://localhost:8025

## 🛠️ Technologies

- **Backend** : Symfony 6.1, PHP 8.1
- **Base de données** : MySQL 8.0
- **Frontend** : Twig, Bootstrap 5
- **Containerisation** : Docker, Docker Compose
- **Tests** : PHPUnit

## 📋 Fonctionnalités

- ✅ Catalogue produits avec images
- ✅ Gestion des catégories
- ✅ Panier d'achat
- ✅ Système d'authentification
- ✅ Interface d'administration
- ✅ Upload d'images sécurisé
- ✅ API REST

## 🔧 Commandes utiles

```bash
# Voir les logs
docker-compose logs app

# Accéder au conteneur
docker exec -it fr-shop-app-1 bash

# Redémarrer les services
docker-compose restart

# Arrêter les services
docker-compose down
```

## 📝 Licence

MIT License - Voir le fichier LICENSE pour plus de détails.

## 👥 Auteurs

- **Salma Ferjani** - Développement initial