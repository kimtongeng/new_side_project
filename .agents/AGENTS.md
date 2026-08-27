# Project Workspace Rules & Customizations

## Custom Export Package Rule (Folder, ZIP & PDF)
When asked to create an export, ZIP, or folder of updated/modified project code (for a commit range, single commit, or current changes):
1. **Always generate all 3 outputs together**:
   - 📁 **Exported Folder** (maintaining exact project hierarchy)
   - 📦 **ZIP Archive** (containing files in exact project hierarchy)
   - 📄 **Upgrade Guide PDF** (`Upgrade <Feature/Task Name>.pdf` with Step 1: File Replacement Table, Step 2: Database Migrations `<web url>/run-migrations` & `php artisan migrate --force`, Step 3: Optional Setup instructions).

2. **Identify updated files & Inclusive Commit Ranges**:
   - If a **single git commit hash** is provided, inspect and package the files updated in that specific commit (`git show --name-only --format="" <commit-hash>`).
   - If a **commit range** is provided (`from <start-commit> to <end-commit>`), **always include the start commit inclusively** by using `<start-commit>~1..<end-commit>` with `git diff --name-only` (never omit changes from the start commit itself).
   - If **no commit hash** is provided, inspect and package current uncommitted/modified project files.

3. **Maintain exact project directory structure**:
   Preserve relative paths matching the project hierarchy (e.g. `database/migrations/`, `resources/views/...`, `app/Http/Controllers/`, `public/js/`, etc.).

4. **Descriptive Task Naming**:
   Name the folder, ZIP, and PDF using a descriptive task name representing what was built/fixed. Never use generic names like `update.zip`.

5. **Destination Directory**:
   Always save all 3 outputs directly to:
   `C:\side project\new side\update_ai\`

Refer to the `custom-zip-exporter` skill instructions in `.agents/skills/custom-zip-exporter/SKILL.md` for full implementation details.

## Obsidian Vault Integration Rule
When asked to create, edit, or sync notes to Obsidian:
1. The default Obsidian Vault path is fixed at:
   `C:\Users\kimtong\Documents\Obsidian Vault\`
2. Default project notes subfolder:
   `C:\Users\kimtong\Documents\Obsidian Vault\side project\`
3. Do not run search commands to look for vault paths; write directly to this folder location immediately.
4. Always provide a clickable link using `file:///C:/Users/kimtong/Documents/Obsidian%20Vault/side%20project/<Filename>` format so the note opens directly.



