#!/bin/bash

echo "========================================"
echo "Correction de l'erreur PhpStan Parser"
echo "========================================"

echo ""
echo "[1/5] Sauvegarde du composer.json actuel..."
cp composer.json composer.json.backup

echo ""
echo "[2/5] Nettoyage du cache Composer..."
composer clear-cache

echo ""
echo "[3/5] Suppression du vendor et composer.lock..."
rm -rf vendor/
rm -f composer.lock

echo ""
echo "[4/5] Installation des dépendances avec version corrigée..."
composer install --no-dev --optimize-autoloader

echo ""
echo "[5/5] Nettoyage du cache Symfony..."
php bin/console cache:clear

echo ""
echo "========================================"
echo "Correction terminée !"
echo "========================================"
echo ""
echo "Testez maintenant votre application."
echo "Si le problème persiste, essayez la solution alternative."
echo ""
