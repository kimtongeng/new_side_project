param (
    [string]$StartCommit = "",
    [string]$EndCommit = ""
)

$destDir = "C:\side project\new side\update_ai"
if (!(Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
}

$files = @()

if ($StartCommit -ne "" -and $EndCommit -ne "") {
    Write-Host "Inspecting files in commit range: $StartCommit to $EndCommit (inclusive)..." -ForegroundColor Cyan
    $files = git diff --name-only "$($StartCommit)~1..$EndCommit"
} elseif ($StartCommit -ne "") {
    Write-Host "Inspecting files from commit: $StartCommit..." -ForegroundColor Cyan
    $files = git show --name-only --format="" $StartCommit
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

$baseName = "Updated_Project_Files_Export"
if ($StartCommit -ne "" -and $EndCommit -ne "") {
    $s1 = $StartCommit.Substring(0, [Math]::Min(7, $StartCommit.Length))
    $s2 = $EndCommit.Substring(0, [Math]::Min(7, $EndCommit.Length))
    $baseName = "Updates_${s1}_to_${s2}"
} elseif ($StartCommit -ne "") {
    $shortHash = $StartCommit.Substring(0, [Math]::Min(8, $StartCommit.Length))
    $baseName = "Commit_${shortHash}_Updates"
}

$targetFolder = Join-Path $destDir $baseName
$zipPath = Join-Path $destDir "$baseName.zip"

if (!(Test-Path $targetFolder)) {
    New-Item -ItemType Directory -Path $targetFolder -Force | Out-Null
}

$baseDir = Get-Location

Write-Host "`nExporting files maintaining exact project directory structure:" -ForegroundColor Cyan
Write-Host "=============================================================="

foreach ($relPath in $files) {
    $normPath = $relPath.Replace('/', '\')
    $srcFile = Join-Path $baseDir $relPath
    $destFile = Join-Path $targetFolder $normPath
    $parentDir = Split-Path $destFile

    if (!(Test-Path $parentDir)) {
        New-Item -ItemType Directory -Path $parentDir -Force | Out-Null
    }

    Copy-Item $srcFile $destFile -Force
    Write-Host "  [+] $relPath"
}

if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path "$targetFolder\*" -DestinationPath $zipPath -Force

Write-Host "=============================================================="
Write-Host "`nSuccessfully exported $($files.Count) files!" -ForegroundColor Green
Write-Host "Folder: $targetFolder" -ForegroundColor Yellow
Write-Host "ZIP:    $zipPath" -ForegroundColor Yellow

