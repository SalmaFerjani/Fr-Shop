# Script PowerShell pour installer les bundles de développement manquants
Write-Host "=== Installation des Bundles de Développement Symfony ===" -ForegroundColor Green
Write-Host ""

# Vérifier si Composer est disponible
$composerPath = $null

# Essayer de trouver Composer dans différents emplacements
$possiblePaths = @(
    "composer",
    "C:\laragon\bin\composer\composer.phar",
    "C:\xampp\composer\composer.phar",
    "C:\wamp\bin\php\php8.1.0\composer.phar"
)

foreach ($path in $possiblePaths) {
    try {
        if ($path -eq "composer") {
            $result = Get-Command composer -ErrorAction SilentlyContinue
            if ($result) {
                $composerPath = "composer"
                break
            }
        } else {
            if (Test-Path $path) {
                $composerPath = $path
                break
            }
        }
    } catch {
        continue
    }
}

if (-not $composerPath) {
    Write-Host "❌ Composer non trouvé. Désactivation temporaire des bundles..." -ForegroundColor Red
    Write-Host ""
    
    # Désactiver temporairement les bundles
    Write-Host "🔄 Désactivation temporaire des bundles de développement..." -ForegroundColor Yellow
    
    $bundlesFile = "config\bundles.php"
    if (Test-Path $bundlesFile) {
        $content = Get-Content $bundlesFile -Raw
        $content = $content -replace 'Symfony\\Bundle\\DebugBundle\\DebugBundle::class => \[.*?\],', '// Symfony\Bundle\DebugBundle\DebugBundle::class => [''dev'' => true],'
        $content = $content -replace 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle::class => \[.*?\],', '// Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => [''dev'' => true, ''test'' => true],'
        $content = $content -replace 'Symfony\\Bundle\\MakerBundle\\MakerBundle::class => \[.*?\],', '// Symfony\Bundle\MakerBundle\MakerBundle::class => [''dev'' => true],'
        
        Set-Content -Path $bundlesFile -Value $content -Encoding UTF8
        Write-Host "✅ Bundles désactivés temporairement" -ForegroundColor Green
    }
    
    Write-Host ""
    Write-Host "⚠️  SOLUTION TEMPORAIRE:" -ForegroundColor Yellow
    Write-Host "Les bundles de développement ont été désactivés."
    Write-Host ""
    Write-Host "Options:" -ForegroundColor Cyan
    Write-Host "1. Utiliser l'interface graphique de votre hébergeur pour installer Composer"
    Write-Host "2. Télécharger composer.phar manuellement"
    Write-Host "3. Utiliser l'option '--no-dev' pour ignorer les bundles de développement"
    Write-Host ""
    
} else {
    Write-Host "✅ Composer trouvé: $composerPath" -ForegroundColor Green
    Write-Host ""
    
    # Installer les dépendances de développement
    Write-Host "📦 Installation des dépendances de développement..." -ForegroundColor Yellow
    
    if ($composerPath -eq "composer") {
        & composer install --dev
    } else {
        & php $composerPath install --dev
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Installation réussie!" -ForegroundColor Green
    } else {
        Write-Host "❌ Erreur lors de l'installation" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Instructions Finales ===" -ForegroundColor Cyan
Write-Host "1. Vérifiez que les bundles sont installés dans vendor/symfony/"
Write-Host "2. Si l'installation a échoué, les bundles sont désactivés dans config/bundles.php"
Write-Host "3. Videz le cache: php bin/console cache:clear"
Write-Host "4. Testez votre application"
Write-Host ""
Write-Host "=== Fin du Script ===" -ForegroundColor Green