@echo off
echo Installing missing Symfony dev dependencies...
echo.

REM Try to find PHP in common Laragon locations
set PHP_PATH=
if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
) else if exist "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe
) else if exist "C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe
)

if "%PHP_PATH%"=="" (
    echo ERROR: Could not find PHP in Laragon. Please install the missing dependencies manually.
    echo.
    echo You can either:
    echo 1. Run: composer install --dev
    echo 2. Or add PHP to your system PATH and run: composer install --dev
    echo.
    pause
    exit /b 1
)

echo Found PHP at: %PHP_PATH%
echo.

REM Try to find Composer
set COMPOSER_PATH=
if exist "C:\laragon\bin\composer\composer.phar" (
    set COMPOSER_PATH=C:\laragon\bin\composer\composer.phar
) else if exist "composer.phar" (
    set COMPOSER_PATH=composer.phar
) else (
    echo Trying to use global composer...
    set COMPOSER_PATH=composer
)

echo Using Composer: %COMPOSER_PATH%
echo.

REM Install dev dependencies
echo Installing dev dependencies...
"%PHP_PATH%" "%COMPOSER_PATH%" install --dev

if %ERRORLEVEL% EQU 0 (
    echo.
    echo SUCCESS: Dev dependencies installed successfully!
    echo.
    echo You can now re-enable the commented bundles in config/bundles.php:
    echo - Symfony\Bundle\DebugBundle\DebugBundle::class
    echo - Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class  
    echo - Symfony\Bundle\MakerBundle\MakerBundle::class
    echo.
) else (
    echo.
    echo ERROR: Failed to install dev dependencies.
    echo Please check the error messages above and try again.
    echo.
)

pause
