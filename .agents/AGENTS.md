# Project Workspace Rules & Customizations

## Custom ZIP Export Rule
When asked to create a ZIP file of updated/modified project code:
1. **Identify updated files**:
   - If a **git commit hash** is provided, inspect and package the files updated in that specific commit (`git show --name-only --format="" <commit-hash>`).
   - If **no commit hash** is provided, inspect and package current uncommitted/modified project files.

2. Package the updated files into simplified category directories:
   - `app/` (for app models: `app/<Filename>`, controllers: `app/Http/Controllers/<Filename>`, utils: `app/Utils/<Filename>`)
   - `migrations/` (for database migrations)
   - `js/` (for public JS files)
   - `account/` (for account blade views)
   - `sell/` (for sell blade views)

3. **Name the ZIP file using a descriptive task name** representing what was built/fixed (e.g. `Account_Location_Filtering_and_Mobile_Fixes.zip`). Never use generic names like `update.zip`.

4. Always save the output ZIP archive directly to:
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



