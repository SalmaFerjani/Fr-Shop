# =============================================================================
# Script de test pour vérifier les scripts CI/CD
# =============================================================================

Write-Host "🧪 Test des scripts CI/CD - BoutiqueProd" -ForegroundColor Blue
Write-Host "=================================================="

# Vérifier l'existence des fichiers
$files = @(
    "ci-pipeline.sh",
    "ci-pipeline.ps1", 
    "deploy.sh",
    ".github/workflows/ci-cd.yml",
    "phpstan.neon",
    ".php-cs-fixer.php",
    "docker-compose.test.yml",
    "Dockerfile.test",
    "CI-CD-README.md"
)

Write-Host "📁 Vérification des fichiers créés..." -ForegroundColor Yellow

foreach ($file in $files) {
    if (Test-Path $file) {
        $size = (Get-Item $file).Length
        Write-Host "  [OK] $file ($size bytes)" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] $file (manquant)" -ForegroundColor Red
    }
}

# Vérifier la syntaxe PowerShell
Write-Host "`n🔍 Vérification de la syntaxe PowerShell..." -ForegroundColor Yellow

try {
    $ast = [System.Management.Automation.Parser]::ParseFile("ci-pipeline.ps1", [ref]$null, [ref]$null)
    Write-Host "  [OK] ci-pipeline.ps1 - Syntaxe valide" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] ci-pipeline.ps1 - Erreur de syntaxe: $($_.Exception.Message)" -ForegroundColor Red
}

# Vérifier la syntaxe YAML
Write-Host "`n🔍 Vérification de la syntaxe YAML..." -ForegroundColor Yellow

try {
    $yamlContent = Get-Content ".github/workflows/ci-cd.yml" -Raw
    if ($yamlContent -match "name:" -and $yamlContent -match "jobs:") {
        Write-Host "  [OK] ci-cd.yml - Structure valide" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] ci-cd.yml - Structure invalide" -ForegroundColor Red
    }
} catch {
    Write-Host "  [ERROR] ci-cd.yml - Erreur de lecture" -ForegroundColor Red
}

# Vérifier les configurations
Write-Host "`n🔍 Vérification des configurations..." -ForegroundColor Yellow

# PHPStan
if (Test-Path "phpstan.neon") {
    $phpstanContent = Get-Content "phpstan.neon" -Raw
    if ($phpstanContent -match "level: 6") {
        Write-Host "  [OK] phpstan.neon - Configuration valide (niveau 6)" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] phpstan.neon - Configuration invalide" -ForegroundColor Red
    }
}

# PHP CS Fixer
if (Test-Path ".php-cs-fixer.php") {
    $csFixerContent = Get-Content ".php-cs-fixer.php" -Raw
    if ($csFixerContent -match "@PSR12" -and $csFixerContent -match "@Symfony") {
        Write-Host "  [OK] .php-cs-fixer.php - Configuration valide (PSR-12 + Symfony)" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] .php-cs-fixer.php - Configuration invalide" -ForegroundColor Red
    }
}

# Docker
if (Test-Path "docker-compose.test.yml") {
    $dockerContent = Get-Content "docker-compose.test.yml" -Raw
    if ($dockerContent -match "version:" -and $dockerContent -match "services:") {
        Write-Host "  [OK] docker-compose.test.yml - Configuration valide" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] docker-compose.test.yml - Configuration invalide" -ForegroundColor Red
    }
}

# Statistiques
Write-Host "`n📊 Statistiques des scripts créés:" -ForegroundColor Yellow

$totalSize = 0
foreach ($file in $files) {
    if (Test-Path $file) {
        $totalSize += (Get-Item $file).Length
    }
}

Write-Host "  📁 Nombre de fichiers: $($files.Count)"
Write-Host "  💾 Taille totale: $([math]::Round($totalSize/1KB, 2)) KB"

# Résumé
Write-Host "`n🎉 Résumé du test:" -ForegroundColor Blue
Write-Host "=================================================="

$successCount = 0
foreach ($file in $files) {
    if (Test-Path $file) {
        $successCount++
    }
}

if ($successCount -eq $files.Count) {
    Write-Host "[SUCCESS] Tous les scripts CI/CD ont ete crees avec succes!" -ForegroundColor Green
    Write-Host "Le pipeline est pret a etre utilise." -ForegroundColor Green
} else {
    Write-Host "⚠️  $($files.Count - $successCount) fichier(s) manquant(s)." -ForegroundColor Yellow
}

Write-Host "`nPour plus d'informations, consultez CI-CD-README.md" -ForegroundColor Cyan
