#!/bin/bash

# =============================================================================
# Script de déploiement pour BoutiqueProd
# =============================================================================
# Ce script automatise le déploiement de l'application en production
# =============================================================================

set -e

# Configuration
PROJECT_NAME="BoutiqueProd"
ENVIRONMENT=${1:-production}
BACKUP_DIR="/var/backups/boutiqueprod"
DEPLOY_DIR="/var/www/boutiqueprod"
SERVICE_NAME="boutiqueprod"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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
    log_info "Vérification des prérequis de déploiement..."
    
    # Vérifier que nous sommes sur le bon environnement
    if [ "$ENVIRONMENT" != "production" ] && [ "$ENVIRONMENT" != "staging" ]; then
        log_error "Environnement invalide. Utilisez 'production' ou 'staging'"
        exit 1
    fi
    
    # Vérifier les permissions
    if [ ! -w "$DEPLOY_DIR" ]; then
        log_error "Pas de permissions d'écriture sur $DEPLOY_DIR"
        exit 1
    fi
    
    # Vérifier l'espace disque
    AVAILABLE_SPACE=$(df "$DEPLOY_DIR" | awk 'NR==2 {print $4}')
    if [ "$AVAILABLE_SPACE" -lt 1000000 ]; then
        log_warning "Espace disque faible: ${AVAILABLE_SPACE}KB disponible"
    fi
    
    log_success "Prérequis vérifiés"
}

# Fonction pour créer une sauvegarde
create_backup() {
    log_info "Création d'une sauvegarde..."
    
    BACKUP_NAME="backup-$(date +%Y%m%d-%H%M%S)"
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
    
    mkdir -p "$BACKUP_PATH"
    
    # Sauvegarder les fichiers
    if [ -d "$DEPLOY_DIR" ]; then
        cp -r "$DEPLOY_DIR" "$BACKUP_PATH/app"
        log_success "Sauvegarde des fichiers créée: $BACKUP_PATH/app"
    fi
    
    # Sauvegarder la base de données
    if command -v pg_dump &> /dev/null; then
        pg_dump -h localhost -U postgres boutique_prod > "$BACKUP_PATH/database.sql"
        log_success "Sauvegarde de la base de données créée: $BACKUP_PATH/database.sql"
    fi
    
    # Nettoyer les anciennes sauvegardes (garder les 5 dernières)
    cd "$BACKUP_DIR"
    ls -t | tail -n +6 | xargs -r rm -rf
    
    log_success "Sauvegarde terminée: $BACKUP_NAME"
}

# Fonction pour déployer l'application
deploy_application() {
    log_info "Déploiement de l'application..."
    
    # Arrêter le service
    if systemctl is-active --quiet "$SERVICE_NAME"; then
        log_info "Arrêt du service $SERVICE_NAME..."
        systemctl stop "$SERVICE_NAME"
    fi
    
    # Créer le répertoire de déploiement s'il n'existe pas
    mkdir -p "$DEPLOY_DIR"
    
    # Extraire l'archive
    if [ -f "build/$PROJECT_NAME-*.tar.gz" ]; then
        LATEST_ARCHIVE=$(ls -t build/$PROJECT_NAME-*.tar.gz | head -1)
        log_info "Extraction de l'archive: $LATEST_ARCHIVE"
        tar -xzf "$LATEST_ARCHIVE" -C "$DEPLOY_DIR" --strip-components=0
    else
        log_error "Aucune archive trouvée dans le dossier build/"
        exit 1
    fi
    
    # Installer les dépendances
    cd "$DEPLOY_DIR"
    log_info "Installation des dépendances de production..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Configurer l'environnement
    if [ ! -f ".env.local" ]; then
        log_info "Création du fichier de configuration..."
        cp .env .env.local
        
        # Configuration spécifique à l'environnement
        if [ "$ENVIRONMENT" = "production" ]; then
            echo "APP_ENV=prod" >> .env.local
            echo "APP_DEBUG=0" >> .env.local
        else
            echo "APP_ENV=dev" >> .env.local
            echo "APP_DEBUG=1" >> .env.local
        fi
    fi
    
    # Nettoyer le cache
    log_info "Nettoyage du cache..."
    php bin/console cache:clear --env=prod --no-debug
    
    # Exécuter les migrations
    log_info "Exécution des migrations de base de données..."
    php bin/console doctrine:migrations:migrate --env=prod --no-interaction
    
    # Configurer les permissions
    log_info "Configuration des permissions..."
    chown -R www-data:www-data var/
    chmod -R 755 var/
    chown -R www-data:www-data public/uploads/
    chmod -R 755 public/uploads/
    
    log_success "Application déployée avec succès"
}

# Fonction pour redémarrer les services
restart_services() {
    log_info "Redémarrage des services..."
    
    # Redémarrer le service de l'application
    if systemctl is-enabled --quiet "$SERVICE_NAME"; then
        systemctl start "$SERVICE_NAME"
        systemctl status "$SERVICE_NAME" --no-pager
        log_success "Service $SERVICE_NAME redémarré"
    fi
    
    # Redémarrer le serveur web
    if systemctl is-active --quiet nginx; then
        systemctl reload nginx
        log_success "Nginx rechargé"
    elif systemctl is-active --quiet apache2; then
        systemctl reload apache2
        log_success "Apache rechargé"
    fi
    
    # Redémarrer PHP-FPM
    if systemctl is-active --quiet php8.1-fpm; then
        systemctl reload php8.1-fpm
        log_success "PHP-FPM rechargé"
    fi
}

# Fonction pour vérifier le déploiement
verify_deployment() {
    log_info "Vérification du déploiement..."
    
    # Vérifier que l'application répond
    if command -v curl &> /dev/null; then
        if curl -f -s http://localhost/health > /dev/null; then
            log_success "Application accessible via HTTP"
        else
            log_warning "Application non accessible via HTTP"
        fi
    fi
    
    # Vérifier les logs d'erreur
    if [ -f "$DEPLOY_DIR/var/log/prod.log" ]; then
        ERROR_COUNT=$(grep -c "ERROR" "$DEPLOY_DIR/var/log/prod.log" || true)
        if [ "$ERROR_COUNT" -gt 0 ]; then
            log_warning "$ERROR_COUNT erreurs trouvées dans les logs"
        else
            log_success "Aucune erreur dans les logs"
        fi
    fi
    
    # Vérifier l'espace disque
    AVAILABLE_SPACE=$(df "$DEPLOY_DIR" | awk 'NR==2 {print $4}')
    log_info "Espace disque disponible: ${AVAILABLE_SPACE}KB"
    
    log_success "Vérification du déploiement terminée"
}

# Fonction pour envoyer des notifications
send_notifications() {
    log_info "Envoi des notifications..."
    
    # Notification par email (si configuré)
    if command -v mail &> /dev/null && [ -n "$NOTIFICATION_EMAIL" ]; then
        echo "Déploiement de $PROJECT_NAME terminé avec succès sur $ENVIRONMENT" | \
        mail -s "Déploiement réussi - $PROJECT_NAME" "$NOTIFICATION_EMAIL"
        log_success "Notification email envoyée"
    fi
    
    # Notification Slack (si configuré)
    if [ -n "$SLACK_WEBHOOK_URL" ]; then
        curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"✅ Déploiement de $PROJECT_NAME réussi sur $ENVIRONMENT\"}" \
        "$SLACK_WEBHOOK_URL" > /dev/null 2>&1
        log_success "Notification Slack envoyée"
    fi
}

# Fonction pour afficher le résumé
show_summary() {
    log_info "Résumé du déploiement:"
    echo "  - Environnement: $ENVIRONMENT"
    echo "  - Répertoire: $DEPLOY_DIR"
    echo "  - Service: $SERVICE_NAME"
    echo "  - Date: $(date)"
    echo "  - Statut: ✅ Succès"
}

# Fonction principale
main() {
    log_info "🚀 Démarrage du déploiement de $PROJECT_NAME"
    log_info "Environnement: $ENVIRONMENT"
    echo "=================================================="
    
    check_prerequisites
    create_backup
    deploy_application
    restart_services
    verify_deployment
    send_notifications
    show_summary
    
    echo "=================================================="
    log_success "🎉 Déploiement terminé avec succès!"
}

# Gestion des erreurs
trap 'log_error "Déploiement interrompu par une erreur"; exit 1' ERR

# Aide
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    echo "Usage: $0 [ENVIRONMENT]"
    echo ""
    echo "Environnements supportés:"
    echo "  production  - Déploiement en production (défaut)"
    echo "  staging     - Déploiement en staging"
    echo ""
    echo "Exemples:"
    echo "  $0 production"
    echo "  $0 staging"
    exit 0
fi

# Exécution du script
main "$@"