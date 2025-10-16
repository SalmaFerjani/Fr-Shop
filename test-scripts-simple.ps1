# Test simple des scripts CI/CD
Write-Host "Test des scripts CI/CD - BoutiqueProd" -ForegroundColor Blue
Write-Host "========================================"

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

Write-Host "Verification des fichiers crees..." -ForegroundColor Yellow

$successCount = 0
foreach ($file in $files) {
    if (Test-Path $file) {
        $size = (Get-Item $file).Length
        Write-Host "  OK: $file ($size bytes)" -ForegroundColor Green
        $successCount++
    } else {
        Write-Host "  ERROR: $file (manquant)" -ForegroundColor Red
    }
}

Write-Host "`nStatistiques:" -ForegroundColor Yellow
Write-Host "  Fichiers crees: $successCount sur $($files.Count)"
Write-Host "  Taille totale: $([math]::Round((Get-ChildItem $files | Measure-Object -Property Length -Sum).Sum/1KB, 2)) KB"

if ($successCount -eq $files.Count) {
    Write-Host "`nSUCCESS: Tous les scripts CI/CD ont ete crees!" -ForegroundColor Green
} else {
    Write-Host "`nWARNING: $($files.Count - $successCount) fichier(s) manquant(s)." -ForegroundColor Yellow
}

Write-Host "`nPour plus d'informations, consultez CI-CD-README.md" -ForegroundColor Cyan
