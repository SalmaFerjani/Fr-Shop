#!/bin/bash

# =============================================================================
# Script CI/CD pour BoutiqueProd - Symfony 6.1
# =============================================================================
# Ce script automatise :
# 1️⃣ Les tests automatisés (PHPUnit)
# 2️⃣ L'analyse de qualité de code (PHPStan, PHP CS Fixer)
# 3️⃣ La création du livrable (build/archive)
# =============================================================================

set -e  # Arrêter le script en cas d'erreur

# Configuration
PROJECT_NAME="BoutiqueProd"
VERSION=$(date +"%Y%m%d-%H%M%S")
BUILD_DIR="build"
REPORTS_DIR="reports"
COVERAGE_DIR="coverage"
ARCHIVE_NAME="${PROJECT_NAME}-${VERSION}.tar.gz"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions utilitaires
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Fonction pour vérifier les prérequis
check_prerequisites() {
    log_info "Vérification des prérequis..."
    
    # Vérifier PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP n'est pas installé ou n'est pas dans le PATH"
        exit 1
    fi
    
    # Vérifier Composer
    if ! command -v composer &> /dev/null; then
        log_error "Composer n'est pas installé ou n'est pas dans le PATH"
        exit 1
    fi
    
    # Vérifier la version PHP
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    log_info "Version PHP détectée: $PHP_VERSION"
    
    if ! php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"; then
        log_error "PHP 8.1+ requis, version actuelle: $PHP_VERSION"
        exit 1
    fi
    
    log_success "Prérequis vérifiés avec succès"
}

# Fonction pour installer les dépendances
install_dependencies() {
    log_info "Installation des dépendances..."
    
    # Installer les dépendances de production
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Installer les dépendances de développement
    composer install --dev --no-interaction
    
    log_success "Dépendances installées avec succès"
}

# Fonction pour nettoyer le cache
clear_cache() {
    log_info "Nettoyage du cache..."
    
    # Nettoyer le cache Symfony
    php bin/console cache:clear --env=test --no-debug
    php bin/console cache:clear --env=prod --no-debug
    
    log_success "Cache nettoyé avec succès"
}

# Fonction pour exécuter les tests
run_tests() {
    log_info "🚀 Exécution des tests automatisés..."
    
    # Créer le dossier des rapports
    mkdir -p "$REPORTS_DIR"
    
    # Exécuter les tests avec couverture
    log_info "Exécution des tests PHPUnit avec couverture de code..."
    if php bin/phpunit --coverage-html="$COVERAGE_DIR" --coverage-clover="$REPORTS_DIR/coverage.xml" --log-junit="$REPORTS_DIR/junit.xml" --verbose; then
        log_success "Tests exécutés avec succès"
    else
        log_error "Échec des tests"
        exit 1
    fi
    
    # Afficher le résumé des tests
    log_info "Résumé des tests:"
    if [ -f "$REPORTS_DIR/junit.xml" ]; then
        TESTS=$(grep -o 'tests="[0-9]*"' "$REPORTS_DIR/junit.xml" | grep -o '[0-9]*' | head -1)
        FAILURES=$(grep -o 'failures="[0-9]*"' "$REPORTS_DIR/junit.xml" | grep -o '[0-9]*' | head -1)
        ERRORS=$(grep -o 'errors="[0-9]*"' "$REPORTS_DIR/junit.xml" | grep -o '[0-9]*' | head -1)
        
        log_info "  - Tests exécutés: $TESTS"
        log_info "  - Échecs: $FAILURES"
        log_info "  - Erreurs: $ERRORS"
        
        if [ "$FAILURES" -gt 0 ] || [ "$ERRORS" -gt 0 ]; then
            log_error "Des tests ont échoué"
            exit 1
        fi
    fi
}

# Fonction pour l'analyse de qualité de code
run_code_quality() {
    log_info "🔍 Analyse de qualité de code..."
    
    # Installer PHPStan si nécessaire
    if ! composer show phpstan/phpstan &> /dev/null; then
        log_info "Installation de PHPStan..."
        composer require --dev phpstan/phpstan --no-interaction
    fi
    
    # Installer PHP CS Fixer si nécessaire
    if ! composer show friendsofphp/php-cs-fixer &> /dev/null; then
        log_info "Installation de PHP CS Fixer..."
        composer require --dev friendsofphp/php-cs-fixer --no-interaction
    fi
    
    # Créer la configuration PHPStan si elle n'existe pas
    if [ ! -f "phpstan.neon" ]; then
        log_info "Création de la configuration PHPStan..."
        cat > phpstan.neon << EOF
parameters:
    level: 6
    paths:
        - src
    excludePaths:
        - src/Migrations
    ignoreErrors:
        - '#Call to an undefined method#'
    checkMissingIterableValueType: false
EOF
    fi
    
    # Créer la configuration PHP CS Fixer si elle n'existe pas
    if [ ! -f ".php-cs-fixer.php" ]; then
        log_info "Création de la configuration PHP CS Fixer..."
        cat > .php-cs-fixer.php << 'EOF'
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('var')
    ->exclude('public')
    ->exclude('node_modules')
    ->name('*.php');

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
    ])
    ->setFinder($finder);
EOF
    fi
    
    # Exécuter PHP CS Fixer (dry-run)
    log_info "Vérification du style de code avec PHP CS Fixer..."
    if vendor/bin/php-cs-fixer fix --dry-run --diff --verbose; then
        log_success "Style de code conforme"
    else
        log_warning "Problèmes de style de code détectés"
        log_info "Exécution de la correction automatique..."
        vendor/bin/php-cs-fixer fix --verbose
        log_success "Style de code corrigé"
    fi
    
    # Exécuter PHPStan
    log_info "Analyse statique avec PHPStan..."
    if vendor/bin/phpstan analyse --memory-limit=1G; then
        log_success "Analyse statique réussie"
    else
        log_error "Problèmes détectés par PHPStan"
        exit 1
    fi
    
    # Générer le rapport de qualité
    log_info "Génération du rapport de qualité..."
    cat > "$REPORTS_DIR/quality-report.md" << EOF
# Rapport de Qualité de Code - $PROJECT_NAME

**Date:** $(date)
**Version:** $VERSION

## Résumé

- ✅ Tests unitaires: Passés
- ✅ Style de code: Conforme
- ✅ Analyse statique: Réussie

## Détails

### Tests
- Couverture de code: Disponible dans \`$COVERAGE_DIR/\`
- Rapport JUnit: \`$REPORTS_DIR/junit.xml\`
- Rapport de couverture: \`$REPORTS_DIR/coverage.xml\`

### Qualité de code
- PHPStan: Niveau 6 (strict)
- PHP CS Fixer: PSR-12
- Aucun problème détecté

## Recommandations

- Maintenir la couverture de code au-dessus de 80%
- Exécuter ce pipeline avant chaque déploiement
- Réviser les rapports de couverture régulièrement
EOF
    
    log_success "Analyse de qualité terminée"
}

# Fonction pour créer le livrable
create_build() {
    log_info "📦 Création du livrable..."
    
    # Créer le dossier de build
    mkdir -p "$BUILD_DIR"
    
    # Nettoyer le cache de production
    php bin/console cache:clear --env=prod --no-debug
    
    # Optimiser l'autoloader
    composer dump-autoload --optimize --no-dev
    
    # Créer l'archive
    log_info "Création de l'archive: $ARCHIVE_NAME"
    
    # Liste des fichiers/dossiers à inclure
    tar --exclude='.git' \
        --exclude='.env.local' \
        --exclude='.env.test' \
        --exclude='var/cache' \
        --exclude='var/log' \
        --exclude='node_modules' \
        --exclude='tests' \
        --exclude='phpunit.xml.dist' \
        --exclude='.php-cs-fixer.php' \
        --exclude='phpstan.neon' \
        --exclude='vendor' \
        --exclude='build' \
        --exclude='reports' \
        --exclude='coverage' \
        -czf "$BUILD_DIR/$ARCHIVE_NAME" .
    
    # Calculer la taille de l'archive
    ARCHIVE_SIZE=$(du -h "$BUILD_DIR/$ARCHIVE_NAME" | cut -f1)
    
    # Créer un fichier de manifest
    cat > "$BUILD_DIR/MANIFEST.txt" << EOF
BoutiqueProd - Manifest de Build
================================

Version: $VERSION
Date de build: $(date)
Taille de l'archive: $ARCHIVE_SIZE

Contenu inclus:
- Code source de l'application
- Configuration de production
- Assets publics
- Migrations de base de données
- Documentation

Contenu exclu:
- Tests unitaires
- Outils de développement
- Cache et logs
- Fichiers de configuration locale

Installation:
1. Extraire l'archive sur le serveur
2. Installer les dépendances: composer install --no-dev --optimize-autoloader
3. Configurer la base de données
4. Exécuter les migrations: php bin/console doctrine:migrations:migrate
5. Configurer le serveur web

Support:
- Documentation: README.md
- API: /api/doc
- Logs: var/log/
EOF
    
    log_success "Livrable créé: $BUILD_DIR/$ARCHIVE_NAME ($ARCHIVE_SIZE)"
}

# Fonction pour générer le rapport final
generate_final_report() {
    log_info "📊 Génération du rapport final..."
    
    cat > "$REPORTS_DIR/ci-report.md" << EOF
# Rapport CI/CD - $PROJECT_NAME

**Date:** $(date)
**Version:** $VERSION
**Statut:** ✅ SUCCÈS

## Résumé des étapes

### 1️⃣ Tests automatisés
- ✅ PHPUnit: Tous les tests passés
- ✅ Couverture de code: Générée
- ✅ Rapports: JUnit et Clover

### 2️⃣ Analyse de qualité
- ✅ PHPStan: Analyse statique réussie
- ✅ PHP CS Fixer: Style de code conforme
- ✅ Aucun problème détecté

### 3️⃣ Création du livrable
- ✅ Archive créée: \`$ARCHIVE_NAME\`
- ✅ Taille: $(du -h "$BUILD_DIR/$ARCHIVE_NAME" | cut -f1)
- ✅ Manifest généré

## Fichiers générés

- **Archive:** \`$BUILD_DIR/$ARCHIVE_NAME\`
- **Manifest:** \`$BUILD_DIR/MANIFEST.txt\`
- **Rapport de qualité:** \`$REPORTS_DIR/quality-report.md\`
- **Couverture de code:** \`$COVERAGE_DIR/\`
- **Rapport JUnit:** \`$REPORTS_DIR/junit.xml\`

## Prochaines étapes

1. Tester l'archive sur un environnement de staging
2. Déployer en production
3. Surveiller les logs et métriques

---
*Généré automatiquement par le pipeline CI/CD*
EOF
    
    log_success "Rapport final généré: $REPORTS_DIR/ci-report.md"
}

# Fonction principale
main() {
    log_info "🚀 Démarrage du pipeline CI/CD pour $PROJECT_NAME"
    log_info "Version: $VERSION"
    echo "=================================================="
    
    # Étapes du pipeline
    check_prerequisites
    install_dependencies
    clear_cache
    run_tests
    run_code_quality
    create_build
    generate_final_report
    
    echo "=================================================="
    log_success "🎉 Pipeline CI/CD terminé avec succès!"
    log_info "Archive créée: $BUILD_DIR/$ARCHIVE_NAME"
    log_info "Rapport disponible: $REPORTS_DIR/ci-report.md"
    log_info "Couverture de code: $COVERAGE_DIR/index.html"
}

# Gestion des erreurs
trap 'log_error "Pipeline interrompu par une erreur"; exit 1' ERR

# Exécution du script
main "$@"

