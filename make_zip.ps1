param (
    [string]$CommitHash = ""
)

$destDir = "C:\side project\new side\update_ai"
if (!(Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
}

$files = @()

if ($CommitHash -ne "") {
    Write-Host "Inspecting files from commit: $CommitHash..." -ForegroundColor Cyan
    $files = git show --name-only --format="" $CommitHash
} else {
    $statusLines = git status --short
    foreach ($line in $statusLines) {
        $trimmed = $line.Trim()
        if ($trimmed -ne "") {
            $parts = -split $trimmed
            if ($parts.Count -ge 2 -and (Test-Path $parts[1]) -and !(Test-Path -PathType Container $parts[1])) {
                $files += $parts[1]
            }
        }
    }

    if ($files.Count -eq 0) {
        Write-Host "No uncommitted modified files found. Gathering files from latest HEAD commit..." -ForegroundColor Yellow
        $files = git show --name-only --format="" HEAD
    }
}

$files = $files | Select-Object -Unique | Where-Object { $_ -ne "" -and (Test-Path $_) }

if ($files.Count -eq 0) {
    Write-Error "No modified files found to package."
    exit 1
}

$zipName = "Updated_Project_Files_Export.zip"
if ($CommitHash -ne "") {
    $shortHash = $CommitHash.Substring(0, [Math]::Min(8, $CommitHash.Length))
    $zipName = "Commit_${shortHash}_Updates.zip"
}

$zipPath = Join-Path $destDir $zipName
$tempDir = Join-Path $env:TEMP "zip_export_temp"

if (Test-Path $tempDir) { Remove-Item $tempDir -Recurse -Force }
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

New-Item -ItemType Directory -Path $tempDir | Out-Null

$baseDir = Get-Location

Write-Host "`nStaging files into category structure:" -ForegroundColor Cyan
Write-Host "========================================"

foreach ($relPath in $files) {
    $normPath = $relPath.Replace('\', '/')
    $fileName = Split-Path $normPath -Leaf
    $zipInsidePath = $normPath

    if ($normPath -match "^app/Http/Controllers/") {
        $zipInsidePath = "app/Http/Controllers/$fileName"
    } elseif ($normPath -match "^app/Utils/") {
        $zipInsidePath = "app/Utils/$fileName"
    } elseif ($normPath -match "^app/") {
        $zipInsidePath = "app/$fileName"
    } elseif ($normPath -match "^database/migrations/") {
        $zipInsidePath = "migrations/$fileName"
    } elseif ($normPath -match "^public/js/") {
        $zipInsidePath = "js/$fileName"
    } elseif ($normPath -match "^resources/views/account/") {
        $zipInsidePath = "account/$fileName"
    } elseif ($normPath -match "^resources/views/sell/") {
        $zipInsidePath = "sell/$fileName"
    }

    $srcFile = Join-Path $baseDir $relPath
    $destFile = Join-Path $tempDir ($zipInsidePath.Replace('/', '\'))
    $parentDir = Split-Path $destFile

    if (!(Test-Path $parentDir)) {
        New-Item -ItemType Directory -Path $parentDir -Force | Out-Null
    }

    Copy-Item $srcFile $destFile -Force
    Write-Host "  [+] $normPath  =>  $zipInsidePath"
}

Compress-Archive -Path "$tempDir\*" -DestinationPath $zipPath -Force
Remove-Item $tempDir -Recurse -Force

Write-Host "========================================"
Write-Host "`nSuccessfully created ZIP file at:" -ForegroundColor Green
Write-Host $zipPath -ForegroundColor Yellow
