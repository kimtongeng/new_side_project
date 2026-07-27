---
name: custom-zip-exporter
description: Creates a custom ZIP archive containing updated project files mapped to simplified category directory paths (app/, migrations/, js/, account/, sell/) and saves the ZIP using a descriptive task-based filename in C:\side project\new side\update_ai\. Use when the user asks to "make a zip of updated files", "export updated files to zip", "zip modified files", or similar.
---

# Custom ZIP Exporter

This skill packages modified or newly created project files into a ZIP archive formatted with simplified category-based folder paths, gives the ZIP file a descriptive task-based name (e.g., `Account_Location_Filtering_and_Mobile_Fixes.zip`), and saves it directly to `C:\side project\new side\update_ai\`.

## ZIP File Naming Convention
- **Do NOT use generic names** like `update.zip` or `update_files.zip`.
- **Always use descriptive, task-based filenames** using TitleCase with underscores or hyphens describing the feature or bug fix completed (e.g., `Account_Location_Filtering_and_Mobile_Fixes.zip`).

## Destination Directory
- `C:\side project\new side\update_ai\`

## Target ZIP Directory Structure

When exporting updated files into a ZIP archive, map project file paths to the following simplified directory layout:

- `app/*.php` (Models) -> `app/<Filename>`
- `app/Http/Controllers/**` -> `app/Http/Controllers/<Filename>`
- `app/Utils/**` -> `app/Utils/<Filename>`
- `database/migrations/**` -> `migrations/<Filename>`
- `public/js/**` -> `js/<Filename>`
- `resources/views/account/**` -> `account/<Filename>`
- `resources/views/sell/**` -> `sell/<Filename>`

Example Structure inside ZIP:
```text
app/
├── Account.php
├── Http/
│   └── Controllers/
│       ├── AccountController.php
│       └── ...
└── Utils/
    └── ModuleUtil.php

migrations/
└── 2026_07_26_163500_change_location_id_type_in_accounts_table.php

js/
├── pos.js
└── purchase.js

account/
├── create.blade.php
├── edit.blade.php
├── index.blade.php
└── transfer.blade.php

sell/
├── create.blade.php
└── edit.blade.php
```

## Workflow & Implementation

1. **Identify Modified Files**:
   - **If a git commit hash is provided**: Gather updated files from that specific commit using:
     `git diff-tree --no-commit-id --name-only -r <commit-hash>` or `git show --name-only --format="" <commit-hash>`
   - **If no commit hash is provided**: Gather current modified/added files using `git status --short` or `git diff --name-only`.

2. **Generate Descriptive ZIP Filename**:
   Create a descriptive filename based on the task (e.g. `Feature_Or_Fix_Description.zip`).

3. **Build Archive using PHP ZipArchive**:
   Use a PHP `ZipArchive` script to populate `C:\side project\new side\update_ai\<Descriptive_Task_Name>.zip` using the mapped local paths.

4. **Verify & Deliver Link**:
   Verify contents with `unzip -l`, then provide the user with a clickable markdown link `[Descriptive_Task_Name.zip](file:///C:/side%20project/new%20side/update_ai/Descriptive_Task_Name.zip)`.
