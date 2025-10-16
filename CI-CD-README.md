# 🚀 Pipeline CI/CD - BoutiqueProd

Ce document décrit l'ensemble des scripts et configurations pour automatiser le processus de développement, test, analyse de qualité et déploiement du projet BoutiqueProd.

## 📋 Table des matières

- [Scripts disponibles](#-scripts-disponibles)
- [Configuration GitHub Actions](#-configuration-github-actions)
- [Outils de qualité](#-outils-de-qualité)
- [Déploiement](#-déploiement)
- [Utilisation](#-utilisation)
- [Dépannage](#-dépannage)

## 🛠️ Scripts disponibles

### 1️⃣ Script Bash (Linux/macOS)
```bash
# Rendre le script exécutable
chmod +x ci-pipeline.sh

# Exécuter le pipeline complet
./ci-pipeline.sh

# Exécuter avec des options
./ci-pipeline.sh --help
```

### 2️⃣ Script PowerShell (Windows)
```powershell
# Exécuter le pipeline complet
.\ci-pipeline.ps1

# Exécuter avec des paramètres
.\ci-pipeline.ps1 -Version "20240101-120000" -SkipTests

# Aide
.\ci-pipeline.ps1 -Help
```

### 3️⃣ Script de déploiement
```bash
# Rendre le script exécutable
chmod +x deploy.sh

# Déploiement en production
./deploy.sh production

# Déploiement en staging
./deploy.sh staging

# Aide
./deploy.sh --help
```

## 🔄 Configuration GitHub Actions

Le workflow GitHub Actions est configuré dans `.github/workflows/ci-cd.yml` et s'exécute automatiquement sur :

- **Push** sur les branches `main` et `develop`
- **Pull Request** vers `main`
- **Release** publiée

### Étapes du workflow :

1. **🧪 Tests Automatisés**
   - Configuration PHP 8.1
   - Base de données PostgreSQL
   - Exécution des tests PHPUnit
   - Génération de la couverture de code

2. **🔍 Analyse de Qualité**
   - PHP CS Fixer (style de code)
   - PHPStan (analyse statique)
   - Security Checker (vulnérabilités)

3. **📦 Création du Livrable**
   - Archive de production
   - Manifest de déploiement
   - Rapports de qualité

4. **🚀 Déploiement** (optionnel)
   - Déploiement automatique sur `main`
   - Création de release sur tag

## 🔍 Outils de qualité

### PHPStan (Analyse statique)
```bash
# Configuration : phpstan.neon
vendor/bin/phpstan analyse --memory-limit=1G
```

**Niveau 6** - Analyse stricte avec :
- Vérification des types
- Détection d'erreurs potentielles
- Analyse des dépendances

### PHP CS Fixer (Style de code)
```bash
# Configuration : .php-cs-fixer.php
vendor/bin/php-cs-fixer fix --dry-run --diff --verbose
```

**Standards** :
- PSR-12
- Symfony Coding Standards
- Règles personnalisées

### Security Checker
```bash
# Vérification des vulnérabilités
vendor/bin/security-checker security:check
```

## 🚀 Déploiement

### Environnements supportés

- **Production** : Serveur de production
- **Staging** : Environnement de test

### Processus de déploiement

1. **Sauvegarde** automatique
2. **Arrêt** des services
3. **Extraction** de l'archive
4. **Installation** des dépendances
5. **Configuration** de l'environnement
6. **Migrations** de base de données
7. **Redémarrage** des services
8. **Vérification** du déploiement

## 📖 Utilisation

### Exécution locale

```bash
# 1. Tests et analyse de qualité
./ci-pipeline.sh

# 2. Création du livrable
# (automatique dans le script)

# 3. Déploiement
./deploy.sh production
```

### Exécution avec Docker

```bash
# Tests avec Docker Compose
docker-compose -f docker-compose.test.yml up --build

# Nettoyage
docker-compose -f docker-compose.test.yml down -v
```

### Exécution sur GitHub Actions

Le pipeline s'exécute automatiquement. Pour déclencher manuellement :

1. Aller dans l'onglet **Actions** du repository
2. Sélectionner **CI/CD Pipeline**
3. Cliquer sur **Run workflow**

## 📊 Rapports générés

### Fichiers de sortie

- **`build/BoutiqueProd-YYYYMMDD-HHMMSS.tar.gz`** - Archive de production
- **`build/MANIFEST.txt`** - Manifest de déploiement
- **`reports/ci-report.md`** - Rapport CI/CD complet
- **`reports/quality-report.md`** - Rapport de qualité
- **`coverage/`** - Couverture de code HTML
- **`reports/junit.xml`** - Rapport de tests JUnit
- **`reports/coverage.xml`** - Rapport de couverture Clover

### Artifacts GitHub Actions

- **`test-reports`** - Rapports de tests
- **`quality-report`** - Rapport de qualité
- **`build-artifact`** - Archive de production

## 🔧 Configuration

### Variables d'environnement

```bash
# Déploiement
export NOTIFICATION_EMAIL="admin@example.com"
export SLACK_WEBHOOK_URL="https://hooks.slack.com/..."

# Base de données
export DATABASE_URL="postgresql://user:pass@localhost:5432/db"
```

### Permissions

```bash
# Scripts
chmod +x ci-pipeline.sh
chmod +x deploy.sh

# Répertoires
chmod -R 755 var/
chmod -R 755 public/uploads/
```

## 🚨 Dépannage

### Problèmes courants

#### 1. Erreur PHP non trouvé
```bash
# Vérifier l'installation PHP
php --version

# Ajouter au PATH si nécessaire
export PATH="/usr/local/bin/php:$PATH"
```

#### 2. Erreur Composer
```bash
# Réinstaller Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 3. Erreur de permissions
```bash
# Corriger les permissions
sudo chown -R $USER:$USER .
chmod -R 755 var/
```

#### 4. Erreur de base de données
```bash
# Vérifier la connexion
php bin/console doctrine:database:create --env=test

# Réinitialiser la base
php bin/console doctrine:database:drop --force --env=test
php bin/console doctrine:database:create --env=test
```

### Logs et débogage

```bash
# Logs Symfony
tail -f var/log/dev.log
tail -f var/log/prod.log

# Logs PHP
tail -f /var/log/php_errors.log

# Logs Nginx/Apache
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

## 📈 Métriques et surveillance

### Couverture de code
- **Objectif** : > 80%
- **Rapport** : `coverage/index.html`

### Performance
- **Tests** : < 30 secondes
- **Build** : < 5 minutes
- **Déploiement** : < 10 minutes

### Qualité
- **PHPStan** : Niveau 6 (strict)
- **PHP CS Fixer** : Aucune erreur
- **Security Checker** : Aucune vulnérabilité

## 🔄 Maintenance

### Mise à jour des dépendances

```bash
# Mise à jour Composer
composer update

# Mise à jour des outils de qualité
composer require --dev phpstan/phpstan:^1.0
composer require --dev friendsofphp/php-cs-fixer:^3.0
```

### Nettoyage

```bash
# Nettoyer les caches
php bin/console cache:clear --env=prod

# Nettoyer les anciennes sauvegardes
find /var/backups/boutiqueprod -type d -mtime +30 -exec rm -rf {} \;

# Nettoyer les logs
find var/log -name "*.log" -mtime +7 -delete
```

## 📞 Support

Pour toute question ou problème :

1. Consulter les logs d'erreur
2. Vérifier la configuration
3. Tester en local
4. Créer une issue GitHub

---

*Documentation générée automatiquement - BoutiqueProd CI/CD Pipeline*

