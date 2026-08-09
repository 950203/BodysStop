<#
    BodyStop - Respaldo de la base de datos
    Genera un dump del contenedor MySQL a backups/ y conserva solo los últimos N.

    Uso:  powershell -ExecutionPolicy Bypass -File scripts/backup-bd.ps1 [-Mantener 10]
#>
param(
    [int]$Mantener = 10
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$dir = Join-Path $root 'backups'
$contenedor = 'bodysstop-db-1'
$base = 'bodystop'
$fecha = Get-Date -Format 'yyyyMMdd_HHmmss'
$archivo = Join-Path $dir "bodystop_$fecha.sql"

if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir | Out-Null }

# Verifica que el contenedor esté arriba
$corriendo = docker ps --filter "name=$contenedor" --format "{{.Names}}" 2>$null
if (-not $corriendo) {
    Write-Host "El contenedor '$contenedor' no está corriendo. Inicia Docker y el proyecto." -ForegroundColor Red
    exit 1
}

docker compose exec -T db sh -c "mysqldump -uroot -proot --single-transaction $base 2>/dev/null" | Out-File -FilePath $archivo -Encoding utf8

$tam = [Math]::Round((Get-Item $archivo).Length / 1KB, 1)
Write-Host "Respaldo creado: $archivo ($tam KB)" -ForegroundColor Green

# Conserva solo los últimos $Mantener respaldos
$viejos = Get-ChildItem -Path $dir -Filter 'bodystop_*.sql' |
    Sort-Object LastWriteTime -Descending |
    Select-Object -Skip $Mantener

foreach ($v in $viejos) {
    Remove-Item $v.FullName -Force
    Write-Host "Respaldo antiguo eliminado: $($v.Name)"
}
