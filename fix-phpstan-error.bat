@echo off
echo ========================================
echo Correction de l'erreur PhpStan Parser
echo ========================================

echo.
echo [1/5] Sauvegarde du composer.json actuel...
copy composer.json composer.json.backup

echo.
echo [2/5] Nettoyage du cache Composer...
composer clear-cache

echo.
echo [3/5] Suppression du vendor et composer.lock...
if exist vendor rmdir /s /q vendor
if exist composer.lock del composer.lock

echo.
echo [4/5] Installation des dependances avec version corrigee...
composer install --no-dev --optimize-autoloader

echo.
echo [5/5] Nettoyage du cache Symfony...
php bin/console cache:clear

echo.
echo ========================================
echo Correction terminee !
echo ========================================
echo.
echo Testez maintenant votre application.
echo Si le probleme persiste, essayez la solution alternative.
echo.
pause
