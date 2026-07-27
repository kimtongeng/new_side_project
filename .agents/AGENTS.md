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
