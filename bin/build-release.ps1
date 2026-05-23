# =============================================================================
# bin/build-release.ps1
# -----------------------------------------------------------------------------
# Construit un dossier (et un ZIP) prêt à uploader sur un hébergeur PHP mutualisé.
#
# Usage : depuis la racine du projet
#   .\bin\build-release.ps1
#
# Le ZIP final est placé dans `release/studio-photo-YYYYMMDD-HHmmss.zip`.
# =============================================================================

$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent (Split-Path -Parent $PSCommandPath)
Set-Location $projectRoot

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$releaseDir = Join-Path $projectRoot "release"
$buildDir = Join-Path $releaseDir "studio-photo-$timestamp"
$zipPath = Join-Path $releaseDir "studio-photo-$timestamp.zip"

Write-Host ""
Write-Host "Studio Photo - Build de release" -ForegroundColor Cyan
Write-Host "==============================" -ForegroundColor Cyan
Write-Host "Projet : $projectRoot"
Write-Host "Cible  : $buildDir"
Write-Host ""

# 1. Clean prior builds
if (Test-Path $releaseDir) {
    Write-Host "Nettoyage des releases precedentes..." -ForegroundColor Yellow
    Remove-Item $releaseDir -Recurse -Force
}
New-Item -ItemType Directory -Path $buildDir -Force | Out-Null

# 2. Copy source files (without vendor / var / node_modules / .git)
Write-Host "Copie des sources..." -ForegroundColor Yellow
$exclude = @('vendor', 'var', 'node_modules', '.git', '.idea', '.vscode', 'release', 'build', 'dist', '.phpunit.cache', 'tests')
Get-ChildItem $projectRoot -Force | Where-Object { $exclude -notcontains $_.Name } | ForEach-Object {
    if ($_.PSIsContainer) {
        Copy-Item $_.FullName -Destination $buildDir -Recurse -Force
    } else {
        Copy-Item $_.FullName -Destination $buildDir -Force
    }
}

# 3. Install Composer prod dependencies inside the build folder
Write-Host "Installation des dependances Composer (--no-dev, --optimize)..." -ForegroundColor Yellow
Set-Location $buildDir
& php "C:\xampp\php\composer" install --no-dev --optimize-autoloader --no-interaction --no-progress 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Erreur lors du composer install" -ForegroundColor Red
    exit 1
}

# 4. Empty var/ to start fresh on the server
Write-Host "Reset du dossier var/..." -ForegroundColor Yellow
if (Test-Path "$buildDir\var") { Remove-Item "$buildDir\var" -Recurse -Force }
New-Item -ItemType Directory -Path "$buildDir\var\cache" -Force | Out-Null
New-Item -ItemType Directory -Path "$buildDir\var\log" -Force | Out-Null
New-Item -ItemType Directory -Path "$buildDir\var\client-galleries" -Force | Out-Null

# 5. Remove dev artifacts that should not be on prod
Write-Host "Suppression des fichiers de developpement..." -ForegroundColor Yellow
@(
    ".env.local",
    ".env.test",
    "phpunit.dist.xml",
    "config\packages\test",
    "config\services_test.yaml"
) | ForEach-Object {
    $path = Join-Path $buildDir $_
    if (Test-Path $path) { Remove-Item $path -Recurse -Force }
}

# 6. Create production .env from the .dist template
Write-Host "Creation de .env.prod (a personnaliser sur le serveur)..." -ForegroundColor Yellow
if (Test-Path "$buildDir\.env.prod.dist") {
    Copy-Item "$buildDir\.env.prod.dist" "$buildDir\.env.prod.local.example"
}

# 7. Create the ZIP
Write-Host "Creation de l archive ZIP..." -ForegroundColor Yellow
Set-Location $releaseDir
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path "studio-photo-$timestamp\*" -DestinationPath $zipPath -CompressionLevel Optimal

$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)

Write-Host ""
Write-Host "Build termine !" -ForegroundColor Green
Write-Host "  Dossier : $buildDir"
Write-Host "  ZIP     : $zipPath ($sizeMB MB)"
Write-Host ""
Write-Host "Prochaine etape :" -ForegroundColor Cyan
Write-Host "  1. Uploader le ZIP sur l hebergeur (FTP/SFTP)"
Write-Host "  2. Decompresser dans le dossier web"
Write-Host "  3. Copier .env.prod.local.example en .env.prod.local et le remplir"
Write-Host "  4. Lancer bin/post-deploy.sh sur le serveur (ou les commandes equivalentes)"
Write-Host ""
