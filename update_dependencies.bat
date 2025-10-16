@echo off
echo Mise à jour des dépendances Composer...

REM Chemins possibles pour PHP dans Laragon
set PHP_PATHS=C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe;C:\laragon\bin\php\php-8.1.12-Win32-vs16-x64\php.exe;C:\laragon\bin\php\php-8.0.12-Win32-vs16-x64\php.exe;C:\xampp\php\php.exe;C:\wamp64\bin\php\php8.2.12\php.exe;C:\wamp64\bin\php\php8.1.12\php.exe

REM Chercher l'exécutable PHP
for %%i in (%PHP_PATHS%) do (
    if exist "%%i" (
        echo PHP trouvé : %%i
        set PHP_EXE=%%i
        goto :found_php
    )
)

echo ERREUR : Aucun exécutable PHP trouvé.
echo Veuillez exécuter manuellement : composer update
pause
exit /b 1

:found_php
echo Exécution de composer update...
"%PHP_EXE%" composer.phar update --no-dev --optimize-autoloader

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Mise à jour réussie !
    echo Le cache Symfony a été vidé automatiquement.
) else (
    echo.
    echo ❌ Erreur lors de la mise à jour.
    echo Veuillez exécuter manuellement : composer update
)

echo.
pause
