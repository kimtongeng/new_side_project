<?php

$destDir = 'C:\\side project\\new side\\update_ai';
if (!file_exists($destDir)) {
    mkdir($destDir, 0777, true);
}

// 1. Get commit arguments if passed
$startCommit = $argv[1] ?? null;
$endCommit = $argv[2] ?? null;
$files = [];

if ($startCommit && $endCommit) {
    echo "Inspecting files in commit range: $startCommit to $endCommit (inclusive)...\n";
    exec("git diff --name-only " . escapeshellarg($startCommit . "~1") . ".." . escapeshellarg($endCommit), $files);
} elseif ($startCommit) {
    echo "Inspecting files from commit: $startCommit...\n";
    exec("git show --name-only --format=\"\" " . escapeshellarg($startCommit), $files);
} else {
    // Try getting current modified/added uncommitted files
    exec("git status --short", $statusLines);
    foreach ($statusLines as $line) {
        $line = trim($line);
        if (!empty($line)) {
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
$baseName = 'Updated_Project_Files_Export';
if ($startCommit && $endCommit) {
    $baseName = "Updates_" . substr($startCommit, 0, 7) . "_to_" . substr($endCommit, 0, 7);
} elseif ($startCommit) {
    $baseName = "Commit_" . substr($startCommit, 0, 8) . "_Updates";
}

$targetFolder = $destDir . DIRECTORY_SEPARATOR . $baseName;
$zipPath = $destDir . DIRECTORY_SEPARATOR . $baseName . '.zip';

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Failed to create ZIP archive at: $zipPath\n");
}

$baseDir = __DIR__;
$addedCount = 0;

echo "Packaging and exporting files maintaining project directory structure:\n";
echo "========================================================================\n";

foreach ($files as $relPath) {
    $relPathNormalized = str_replace('\\', '/', $relPath);
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPathNormalized);

    if (!file_exists($fullPath) || is_dir($fullPath)) {
        continue;
    }

    $destFilePath = $targetFolder . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPathNormalized);
    $destFileDir = dirname($destFilePath);

    if (!file_exists($destFileDir)) {
        mkdir($destFileDir, 0777, true);
    }

    copy($fullPath, $destFilePath);
    $zip->addFile($fullPath, $relPathNormalized);

    echo "  [+] $relPathNormalized\n";
    $addedCount++;
}

$zip->close();

// 3. Generate Upgrade Guide PDF
$pdfPath = $destDir . DIRECTORY_SEPARATOR . 'Upgrade_' . $baseName . '.pdf';
$edgeExecutable = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

if (file_exists($edgeExecutable)) {
    echo "Generating Upgrade Guide PDF...\n";

    // Detect unique top-level and second-level folders modified
    $folderMappings = [];
    foreach ($files as $f) {
        $parts = explode('/', str_replace('\\', '/', $f));
        if (count($parts) >= 2) {
            if ($parts[0] === 'resources' && $parts[1] === 'views' && isset($parts[2])) {
                $category = $parts[2];
                $dest = 'resources >> views >> ' . $parts[2];
                if (isset($parts[3]) && in_array($parts[3], ['partials', 'receipts'])) {
                    $category = $parts[3] . ' (' . $parts[2] . ')';
                    $dest .= ' >> ' . $parts[3];
                }
                $folderMappings[$category] = $dest;
            } elseif ($parts[0] === 'app' && $parts[1] === 'Http' && isset($parts[2])) {
                $folderMappings[strtolower($parts[2])] = 'app >> Http >> ' . $parts[2];
            } elseif ($parts[0] === 'app' && in_array($parts[1], ['Notifications', 'Providers', 'Utils', 'Middleware'])) {
                $folderMappings[strtolower($parts[1])] = 'app >> ' . $parts[1];
            } elseif ($parts[0] === 'app') {
                $folderMappings['app'] = 'app';
            } elseif ($parts[0] === 'database' && $parts[1] === 'migrations') {
                $folderMappings['migrations'] = 'database >> migrations';
            } elseif ($parts[0] === 'public' && $parts[1] === 'js') {
                $folderMappings['js'] = 'public >> js';
            } elseif ($parts[0] === 'lang') {
                $folderMappings['lang'] = 'lang >> ' . ($parts[1] ?? 'en');
            } else {
                $folderMappings[$parts[0]] = $parts[0];
            }
        } else {
            $folderMappings[$parts[0]] = $parts[0];
        }
    }

    $tableRowsHtml = '';
    foreach ($folderMappings as $folder => $dest) {
        $action = ($folder === 'Modules') ? 'Copy zip to folder and then extract file' : 'Replace all files';
        $tableRowsHtml .= "<tr><td class=\"folder\">" . htmlspecialchars($folder) . "</td><td class=\"path\">" . htmlspecialchars($dest) . "</td><td class=\"action\">$action</td></tr>\n";
    }

    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upgrade Guide</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 18mm 18mm 18mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #000; background: #fff; margin: 0; padding: 0; font-size: 13px; line-height: 1.5; }
        .header { margin-bottom: 22px; }
        h1 { font-size: 24px; font-weight: 800; margin: 0 0 4px 0; color: #000; letter-spacing: -0.5px; }
        h2 { font-size: 17px; font-weight: 700; margin: 18px 0 10px 0; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #fff; color: #000; font-weight: 700; font-size: 12.5px; text-align: left; padding: 8px 10px; border: 1.5px solid #000; }
        td { padding: 6px 10px; border: 1.5px solid #000; font-size: 12px; }
        td.folder { font-weight: 600; width: 26%; }
        td.path { font-family: "Consolas", monospace; color: #006644; width: 44%; }
        td.action { width: 30%; }
        .step-block { margin-top: 8px; margin-bottom: 16px; }
        .sub-heading { font-weight: 700; font-size: 13px; margin-bottom: 4px; }
        .step-text { font-size: 12.5px; margin-bottom: 8px; }
        .url-link { font-family: "Consolas", monospace; color: #0066cc; text-decoration: underline; }
        .code-box { font-family: "Consolas", monospace; color: #006644; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Upgrade product and payment account</h1>
    </div>
    <h2>Step 1: File Replacement</h2>
    <table>
        <thead>
            <tr><th>Folder Name</th><th>Path / Destination</th><th>Required Action</th></tr>
        </thead>
        <tbody>
            $tableRowsHtml
        </tbody>
    </table>
    <h2>Step 2: Add Table / Columns to Database</h2>
    <div class="step-block">
        <div class="sub-heading">Option 1 (Via Web Browser):</div>
        <div class="step-text">Go to this link: <span class="code-box">&lt;web url&gt;/run-migrations</span><br><em>Example:</em> <span class="url-link">http://your-pos-domain.com/run-migrations</span></div>
        <div class="sub-heading" style="margin-top: 10px;">Option 2 (Via Terminal / CLI):</div>
        <div class="step-text">Run command in terminal:<br><span class="code-box">php artisan migrate --force</span></div>
    </div>
    <h2>Step 3: Telegram Bot Setup (Optional):</h2>
    <div class="step-block">
        <div class="step-text">Go to <strong>Settings &rarr; Telegram Settings</strong> to enter Bot Token and Group/Topic IDs.</div>
    </div>
</body>
</html>
HTML;

    $tempHtml = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'upgrade_guide_temp.html';
    file_put_contents($tempHtml, $htmlContent);

    $cmd = '"' . $edgeExecutable . '" --headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf="' . $pdfPath . '" "' . $tempHtml . '"';
    exec($cmd);
    @unlink($tempHtml);
}

echo "========================================================================\n";
echo "Successfully exported $addedCount files!\n";
echo "Folder location: $targetFolder\n";
echo "ZIP location:    $zipPath\n";
if (file_exists($pdfPath)) {
    echo "PDF location:    $pdfPath\n";
}


