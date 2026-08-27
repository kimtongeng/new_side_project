---
name: custom-zip-exporter
description: Creates a custom package consisting of an exported folder, ZIP archive, and upgrade guide PDF for updated project files and saves all three directly to C:\side project\new side\update_ai\. Use when the user asks to "make a zip", "make folder", "export commit", or similar.
---

# Custom Package Exporter (Folder, ZIP & PDF)

This skill packages modified or newly created project files into a folder, a ZIP archive (both maintaining the exact project directory layout), and generates an Upgrade Guide PDF (`Upgrade <Feature/Task Name>.pdf`). All 3 assets are saved directly to `C:\side project\new side\update_ai\`.

## Exported Assets (Always Generate All 3)
1. 📁 **Exported Folder**: Exact relative directory hierarchy with all modified files copied.
2. 📦 **ZIP Archive**: Compressed archive containing all modified files in exact relative directory hierarchy.
3. 📄 **Upgrade Guide PDF**: Formatted PDF document containing:
   - **Header & Title**: `Upgrade <Feature/Task Name>`
   - **Step 1 (File Replacement)**: Table listing updated folders, destination paths, and required action ("Replace all files" / "Copy folder / extract files").
   - **Step 2 (Database Migrations)**: Options for web browser (`<web url>/run-migrations`) and terminal (`php artisan migrate --force`).
   - **Step 3 (Optional Setups)**: Specific module or setting instructions (e.g. Telegram bot setup, permission configurations).

## Destination Directory
- `C:\side project\new side\update_ai\`

## Workflow & Implementation

1. **Identify Modified Files (Inclusive Ranges)**:
   - **Single commit hash**: Inspect and package files from that commit (`git show --name-only --format="" <commit-hash>`).
   - **Commit range (`from <start-commit> to <end-commit>`)**: **Always include the start commit inclusively** by using:
     `git diff --name-only <start-commit>~1..<end-commit>`
   - **No commit hash**: Gather uncommitted modified files via `git status --short` or HEAD.

2. **Package Folder & ZIP**:
   Copy files to `C:\side project\new side\update_ai\<TaskName>\` and create `C:\side project\new side\update_ai\<TaskName>.zip` preserving exact project hierarchy.

3. **Generate PDF Guide**:
   Render HTML template using Microsoft Edge headless (`--headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf=...`) to produce `C:\side project\new side\update_ai\Upgrade <TaskName>.pdf`.

4. **Verify & Deliver Links**:
   Provide clickable markdown links for all 3 generated assets:
   - `[Folder](file:///C:/side%20project/new%20side/update_ai/<TaskName>/)`
   - `[ZIP](file:///C:/side%20project/new%20side/update_ai/<TaskName>.zip)`
   - `[PDF](file:///C:/side%20project/new%20side/update_ai/<PDFName>.pdf)`

