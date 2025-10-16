# Guide de Déploiement - BoutiqueProd

## Problèmes Identifiés et Solutions

### 1. Configuration d'Environnement

**Problème :** L'application est configurée en mode développement (`APP_ENV=dev`) mais les bundles de développement sont désactivés.

**Solution :** 
- Créer un fichier `.env.prod` pour la production
- Configurer `APP_ENV=prod` en production

### 2. Configuration de Base de Données

**Problème :** La configuration actuelle pointe vers `127.0.0.1:3306` (localhost).

**Solution :** 
- Modifier `DATABASE_URL` dans `.env.prod` avec les vraies informations de votre hébergeur
- Format : `mysql://username:password@host:port/database_name`

### 3. Bundles de Développement

**Problème :** Les bundles de développement sont désactivés mais l'environnement est en `dev`.

**Solutions :**
- **Option A (Recommandée) :** Utiliser l'environnement de production
- **Option B :** Réactiver les bundles de développement

## Étapes de Déploiement

### Étape 1 : Configuration de l'Environnement

1. Créer le fichier `.env.prod` avec la bonne configuration
2. Configurer `APP_ENV=prod`
3. Mettre à jour `DATABASE_URL` avec les informations de votre hébergeur

### Étape 2 : Installation des Dépendances

```bash
# Installer les dépendances de production (sans les dev)
composer install --no-dev --optimize-autoloader

# Ou si vous voulez les outils de développement
composer install --optimize-autoloader
```

### Étape 3 : Configuration du Serveur Web

#### Pour Apache (.htaccess déjà créé)
Le fichier `.htaccess` est déjà configuré correctement.

#### Pour Nginx
Créer une configuration Nginx appropriée.

### Étape 4 : Permissions et Cache

```bash
# Vider le cache
php bin/console cache:clear --env=prod

# Définir les permissions
chmod -R 755 var/
chmod -R 755 public/
```

### Étape 5 : Base de Données

```bash
# Exécuter les migrations
php bin/console doctrine:migrations:migrate --env=prod

# Optionnel : Charger les données de test
php bin/console doctrine:fixtures:load --env=prod
```

## Configuration Recommandée pour la Production

### Fichier .env.prod
```env
APP_ENV=prod
APP_SECRET=your-secret-key-here
DATABASE_URL="mysql://your-db-user:your-db-password@your-db-host:3306/your-db-name"
MAILER_DSN=smtp://your-smtp-server:25
```

### Réactiver les Bundles (si nécessaire)
Si vous voulez les outils de développement en production, décommentez dans `config/bundles.php` :
```php
Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
```

## Vérifications Post-Déploiement

1. ✅ L'application se charge sans erreur 500
2. ✅ Les pages principales sont accessibles
3. ✅ La base de données se connecte correctement
4. ✅ Les images et assets se chargent
5. ✅ L'authentification fonctionne
6. ✅ Les fonctionnalités admin marchent

## Support

Si vous rencontrez des problèmes :
1. Vérifiez les logs dans `var/log/prod.log`
2. Vérifiez la configuration de votre hébergeur
3. Assurez-vous que PHP 8.1+ est installé
4. Vérifiez que les extensions PHP nécessaires sont activées

