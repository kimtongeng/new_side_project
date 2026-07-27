<?php

$destDir = 'C:\\side project\\new side\\update_ai';
if (!file_exists($destDir)) {
    mkdir($destDir, 0777, true);
}

// 1. Get commit hash if passed as argument, else detect uncommitted or HEAD changes
$commitHash = $argv[1] ?? null;
$files = [];

if ($commitHash) {
    echo "Inspecting files from commit: $commitHash...\n";
    exec("git show --name-only --format=\"\" " . escapeshellarg($commitHash), $files);
} else {
    // Try getting current modified/added uncommitted files
    exec("git status --short", $statusLines);
    foreach ($statusLines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            // Status line format: XY path/to/file
            $parts = preg_split('/\s+/', $line, 2);
            if (isset($parts[1]) && file_exists($parts[1]) && !is_dir($parts[1])) {
                $files[] = $parts[1];
            }
        }
    }

    // If no uncommitted files found, fall back to HEAD commit files
    if (empty($files)) {
        echo "No uncommitted modified files found. Gathering files from latest HEAD commit...\n";
        exec("git show --name-only --format=\"\" HEAD", $files);
    }
}

$files = array_unique(array_filter(array_map('trim', $files)));

if (empty($files)) {
    die("No modified files found to package.\n");
}

// 2. Generate descriptive filename
$zipName = 'Updated_Project_Files_Export.zip';
if ($commitHash) {
    $zipName = "Commit_" . substr($commitHash, 0, 8) . "_Updates.zip";
}

$zipPath = $destDir . DIRECTORY_SEPARATOR . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Failed to create ZIP archive at: $zipPath\n");
}

$baseDir = __DIR__;
$addedCount = 0;

echo "Packaging files into ZIP:\n";
echo "========================================\n";

foreach ($files as $relPath) {
    $relPathNormalized = str_replace('\\', '/', $relPath);
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPathNormalized);

    if (!file_exists($fullPath) || is_dir($fullPath)) {
        continue;
    }

    // Determine simplified inside-ZIP path according to workspace mapping rules
    $fileName = basename($relPathNormalized);
    $zipInsidePath = $relPathNormalized;

    if (preg_match('#^app/Http/Controllers/#i', $relPathNormalized)) {
        $zipInsidePath = 'app/Http/Controllers/' . $fileName;
    } elseif (preg_match('#^app/Utils/#i', $relPathNormalized)) {
        $zipInsidePath = 'app/Utils/' . $fileName;
    } elseif (preg_match('#^app/#i', $relPathNormalized)) {
        $zipInsidePath = 'app/' . $fileName;
    } elseif (preg_match('#^database/migrations/#i', $relPathNormalized)) {
        $zipInsidePath = 'migrations/' . $fileName;
    } elseif (preg_match('#^public/js/#i', $relPathNormalized)) {
        $zipInsidePath = 'js/' . $fileName;
    } elseif (preg_match('#^resources/views/account/#i', $relPathNormalized)) {
        $zipInsidePath = 'account/' . $fileName;
    } elseif (preg_match('#^resources/views/sell/#i', $relPathNormalized)) {
        $zipInsidePath = 'sell/' . $fileName;
    }

    $zip->addFile($fullPath, $zipInsidePath);
    echo "  [+] $relPathNormalized  =>  $zipInsidePath\n";
    $addedCount++;
}

$zip->close();

echo "========================================\n";
echo "Successfully created ZIP containing $addedCount files!\n";
echo "Destination: $zipPath\n";
