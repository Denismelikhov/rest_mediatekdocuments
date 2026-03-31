param(
    [string]$MySqlDumpPath = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe",
    [string]$Host = "localhost",
    [int]$Port = 3306,
    [string]$Database = "mediatekdocuments",
    [string]$User = "root",
    [string]$Password = "",
    [string]$BackupDir = "C:\Sauvegardes\MediaTekDocuments",
    [int]$KeepDays = 7
)

$ErrorActionPreference = "Stop"

if (!(Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir | Out-Null
}

$stamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = Join-Path $BackupDir "mediatekdocuments_$stamp.sql"

& $MySqlDumpPath `
    --host=$Host `
    --port=$Port `
    --user=$User `
    --password=$Password `
    --default-character-set=utf8 `
    --routines `
    --events `
    --triggers `
    $Database | Out-File -FilePath $backupFile -Encoding utf8

Get-ChildItem $BackupDir -Filter "*.sql" |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$KeepDays) } |
    Remove-Item -Force