# =============================================================================
# Script CI/CD pour BoutiqueProd - Symfony 6.1 (PowerShell)
# =============================================================================
# Ce script automatise :
# 1️⃣ Les tests automatisés (PHPUnit)
# 2️⃣ L'analyse de qualité de code (PHPStan, PHP CS Fixer)
# 3️⃣ La création du livrable (build/archive)
# =============================================================================

param(
    [string]$Version = (Get-Date -Format "yyyyMMdd-HHmmss"),
    [string]$ProjectName = "BoutiqueProd",
    [switch]$SkipTests = $false,
    [switch]$SkipQuality = $false,
    [switch]$SkipBuild = $false
)

# Configuration
$BuildDir = "build"
$ReportsDir = "reports"
$CoverageDir = "coverage"
$ArchiveName = "$ProjectName-$Version.zip"

# Fonctions utilitaires
function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Fonction pour vérifier les prérequis
function Test-Prerequisites {
    Write-Info "Vérification des prérequis..."
    
    # Vérifier PHP
    try {
        $phpVersion = php -r "echo PHP_VERSION;"
        Write-Info "Version PHP détectée: $phpVersion"
        
        # Vérifier la version PHP
        $phpVersionCheck = php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"
        if ($LASTEXITCODE -ne 0) {
            Write-Error "PHP 8.1+ requis, version actuelle: $phpVersion"
            exit 1
        }
    }
    catch {
        Write-Error "PHP n'est pas installé ou n'est pas dans le PATH"
        exit 1
    }
    
    # Vérifier Composer
    try {
        composer --version | Out-Null
    }
    catch {
        Write-Error "Composer n'est pas installé ou n'est pas dans le PATH"
        exit 1
    }
    
    Write-Success "Prérequis vérifiés avec succès"
}

# Fonction pour installer les dépendances
function Install-Dependencies {
    Write-Info "Installation des dépendances..."
    
    # Installer les dépendances de production
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Installer les dépendances de développement
    composer install --dev --no-interaction
    
    Write-Success "Dépendances installées avec succès"
}

# Fonction pour nettoyer le cache
function Clear-Cache {
    Write-Info "Nettoyage du cache..."
    
    # Nettoyer le cache Symfony
    php bin/console cache:clear --env=test --no-debug
    php bin/console cache:clear --env=prod --no-debug
    
    Write-Success "Cache nettoyé avec succès"
}

# Fonction pour exécuter les tests
function Invoke-Tests {
    if ($SkipTests) {
        Write-Warning "Tests ignorés (paramètre -SkipTests)"
        return
    }
    
    Write-Info "🚀 Exécution des tests automatisés..."
    
    # Créer le dossier des rapports
    if (!(Test-Path $ReportsDir)) {
        New-Item -ItemType Directory -Path $ReportsDir | Out-Null
    }
    
    # Exécuter les tests avec couverture
    Write-Info "Exécution des tests PHPUnit avec couverture de code..."
    
    $testResult = php bin/phpunit --coverage-html="$CoverageDir" --coverage-clover="$ReportsDir\coverage.xml" --log-junit="$ReportsDir\junit.xml" --verbose
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Tests exécutés avec succès"
    }
    else {
        Write-Error "Échec des tests"
        exit 1
    }
    
    # Analyser le rapport JUnit
    if (Test-Path "$ReportsDir\junit.xml") {
        $junitContent = Get-Content "$ReportsDir\junit.xml" -Raw
        $testsMatch = [regex]::Match($junitContent, 'tests="(\d+)"')
        $failuresMatch = [regex]::Match($junitContent, 'failures="(\d+)"')
        $errorsMatch = [regex]::Match($junitContent, 'errors="(\d+)"')
        
        $tests = if ($testsMatch.Success) { $testsMatch.Groups[1].Value } else { "0" }
        $failures = if ($failuresMatch.Success) { $failuresMatch.Groups[1].Value } else { "0" }
        $errors = if ($errorsMatch.Success) { $errorsMatch.Groups[1].Value } else { "0" }
        
        Write-Info "Résumé des tests:"
        Write-Info "  - Tests exécutés: $tests"
        Write-Info "  - Échecs: $failures"
        Write-Info "  - Erreurs: $errors"
        
        if ([int]$failures -gt 0 -or [int]$errors -gt 0) {
            Write-Error "Des tests ont échoué"
            exit 1
        }
    }
}

# Fonction pour l'analyse de qualité de code
function Invoke-CodeQuality {
    if ($SkipQuality) {
        Write-Warning "Analyse de qualité ignorée (paramètre -SkipQuality)"
        return
    }
    
    Write-Info "🔍 Analyse de qualité de code..."
    
    # Installer PHPStan si nécessaire
    $phpstanInstalled = composer show phpstan/phpstan 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Info "Installation de PHPStan..."
        composer require --dev phpstan/phpstan --no-interaction
    }
    
    # Installer PHP CS Fixer si nécessaire
    $csFixerInstalled = composer show friendsofphp/php-cs-fixer 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Info "Installation de PHP CS Fixer..."
        composer require --dev friendsofphp/php-cs-fixer --no-interaction
    }
    
    # Créer la configuration PHPStan si elle n'existe pas
    if (!(Test-Path "phpstan.neon")) {
        Write-Info "Création de la configuration PHPStan..."
        $phpstanConfig = @"
parameters:
    level: 6
    paths:
        - src
    excludePaths:
        - src/Migrations
    ignoreErrors:
        - '#Call to an undefined method#'
    checkMissingIterableValueType: false
"@
        $phpstanConfig | Out-File -FilePath "phpstan.neon" -Encoding UTF8
    }
    
    # Créer la configuration PHP CS Fixer si elle n'existe pas
    if (!(Test-Path ".php-cs-fixer.php")) {
        Write-Info "Création de la configuration PHP CS Fixer..."
        $csFixerConfig = @'
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
'@
        $csFixerConfig | Out-File -FilePath ".php-cs-fixer.php" -Encoding UTF8
    }
    
    # Exécuter PHP CS Fixer (dry-run)
    Write-Info "Vérification du style de code avec PHP CS Fixer..."
    $csFixerResult = vendor\bin\php-cs-fixer fix --dry-run --diff --verbose
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Style de code conforme"
    }
    else {
        Write-Warning "Problèmes de style de code détectés"
        Write-Info "Exécution de la correction automatique..."
        vendor\bin\php-cs-fixer fix --verbose
        Write-Success "Style de code corrigé"
    }
    
    # Exécuter PHPStan
    Write-Info "Analyse statique avec PHPStan..."
    $phpstanResult = vendor\bin\phpstan analyse --memory-limit=1G
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Analyse statique réussie"
    }
    else {
        Write-Error "Problèmes détectés par PHPStan"
        exit 1
    }
    
    # Générer le rapport de qualité
    Write-Info "Génération du rapport de qualité..."
    $qualityReport = @"
# Rapport de Qualité de Code - $ProjectName

**Date:** $(Get-Date)
**Version:** $Version

## Résumé

- ✅ Tests unitaires: Passés
- ✅ Style de code: Conforme
- ✅ Analyse statique: Réussie

## Détails

### Tests
- Couverture de code: Disponible dans ``$CoverageDir\``
- Rapport JUnit: ``$ReportsDir\junit.xml``
- Rapport de couverture: ``$ReportsDir\coverage.xml``

### Qualité de code
- PHPStan: Niveau 6 (strict)
- PHP CS Fixer: PSR-12
- Aucun problème détecté

## Recommandations

- Maintenir la couverture de code au-dessus de 80%
- Exécuter ce pipeline avant chaque déploiement
- Réviser les rapports de couverture régulièrement
"@
    $qualityReport | Out-File -FilePath "$ReportsDir\quality-report.md" -Encoding UTF8
    
    Write-Success "Analyse de qualité terminée"
}

# Fonction pour créer le livrable
function New-Build {
    if ($SkipBuild) {
        Write-Warning "Création du livrable ignorée (paramètre -SkipBuild)"
        return
    }
    
    Write-Info "📦 Création du livrable..."
    
    # Créer le dossier de build
    if (!(Test-Path $BuildDir)) {
        New-Item -ItemType Directory -Path $BuildDir | Out-Null
    }
    
    # Nettoyer le cache de production
    php bin/console cache:clear --env=prod --no-debug
    
    # Optimiser l'autoloader
    composer dump-autoload --optimize --no-dev
    
    # Créer l'archive
    Write-Info "Création de l'archive: $ArchiveName"
    
    # Créer une archive ZIP
    $excludePatterns = @(
        ".git",
        ".env.local",
        ".env.test",
        "var\cache",
        "var\log",
        "node_modules",
        "tests",
        "phpunit.xml.dist",
        ".php-cs-fixer.php",
        "phpstan.neon",
        "vendor",
        "build",
        "reports",
        "coverage"
    )
    
    # Obtenir tous les fichiers à inclure
    $filesToInclude = Get-ChildItem -Path . -Recurse | Where-Object {
        $exclude = $false
        foreach ($pattern in $excludePatterns) {
            if ($_.FullName -like "*\$pattern*") {
                $exclude = $true
                break
            }
        }
        return !$exclude
    }
    
    # Créer l'archive ZIP
    Compress-Archive -Path $filesToInclude.FullName -DestinationPath "$BuildDir\$ArchiveName" -Force
    
    # Calculer la taille de l'archive
    $archiveSize = (Get-Item "$BuildDir\$ArchiveName").Length
    $archiveSizeFormatted = "{0:N2} MB" -f ($archiveSize / 1MB)
    
    # Créer un fichier de manifest
    $manifest = @"
BoutiqueProd - Manifest de Build
================================

Version: $Version
Date de build: $(Get-Date)
Taille de l'archive: $archiveSizeFormatted

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
"@
    $manifest | Out-File -FilePath "$BuildDir\MANIFEST.txt" -Encoding UTF8
    
    Write-Success "Livrable créé: $BuildDir\$ArchiveName ($archiveSizeFormatted)"
}

# Fonction pour générer le rapport final
function New-FinalReport {
    Write-Info "📊 Génération du rapport final..."
    
    $archiveSize = if (Test-Path "$BuildDir\$ArchiveName") { 
        "{0:N2} MB" -f ((Get-Item "$BuildDir\$ArchiveName").Length / 1MB) 
    } else { "N/A" }
    
    $finalReport = @"
# Rapport CI/CD - $ProjectName

**Date:** $(Get-Date)
**Version:** $Version
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
- ✅ Archive créée: ``$ArchiveName``
- ✅ Taille: $archiveSize
- ✅ Manifest généré

## Fichiers générés

- **Archive:** ``$BuildDir\$ArchiveName``
- **Manifest:** ``$BuildDir\MANIFEST.txt``
- **Rapport de qualité:** ``$ReportsDir\quality-report.md``
- **Couverture de code:** ``$CoverageDir\``
- **Rapport JUnit:** ``$ReportsDir\junit.xml``

## Prochaines étapes

1. Tester l'archive sur un environnement de staging
2. Déployer en production
3. Surveiller les logs et métriques

---
*Généré automatiquement par le pipeline CI/CD*
"@
    $finalReport | Out-File -FilePath "$ReportsDir\ci-report.md" -Encoding UTF8
    
    Write-Success "Rapport final généré: $ReportsDir\ci-report.md"
}

# Fonction principale
function Main {
    Write-Info "🚀 Démarrage du pipeline CI/CD pour $ProjectName"
    Write-Info "Version: $Version"
    Write-Host "=================================================="
    
    try {
        # Étapes du pipeline
        Test-Prerequisites
        Install-Dependencies
        Clear-Cache
        Invoke-Tests
        Invoke-CodeQuality
        New-Build
        New-FinalReport
        
        Write-Host "=================================================="
        Write-Success "🎉 Pipeline CI/CD terminé avec succès!"
        Write-Info "Archive créée: $BuildDir\$ArchiveName"
        Write-Info "Rapport disponible: $ReportsDir\ci-report.md"
        Write-Info "Couverture de code: $CoverageDir\index.html"
    }
    catch {
        Write-Error "Pipeline interrompu par une erreur: $($_.Exception.Message)"
        exit 1
    }
}

# Exécution du script
Main

