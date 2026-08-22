# 🚀 UltimatePOS Update & Installation Guide (v2026.8)

This guide provides step-by-step instructions to safely install and deploy this custom update package to an existing **UltimatePOS v6.x** installation (cPanel, VPS, or Local Server).

---

## ⚠️ STEP 1: Backup First (Crucial)

Before replacing any files, always create a full backup of your system.

1. **Backup Database**:
   - **cPanel / phpMyAdmin**: Go to phpMyAdmin $\rightarrow$ Select your database $\rightarrow$ Click **Export** $\rightarrow$ Save `.sql` file.
   - **SSH / Terminal**:
     ```bash
     mysqldump -u [db_user] -p [db_name] > backup_before_update_$(date +%F).sql
     ```

2. **Backup Project Files**:
   - Compress your current project folder into a ZIP file (e.g., `backup_pos_source.zip`).

---

## 📁 STEP 2: Copy & Replace Update Files

Extract the update package `UltimatePOS_Updates_6207585_to_HEAD.zip` directly into the **root folder** of your UltimatePOS project.

### Directory Mapping:
Ensure the folders merge with your existing project root structure:
- `app/` $\rightarrow$ merges with `[root]/app/`
- `config/` $\rightarrow$ merges with `[root]/config/`
- `database/` $\rightarrow$ merges with `[root]/database/`
- `lang/` $\rightarrow$ merges with `[root]/lang/`
- `Modules/` $\rightarrow$ merges with `[root]/Modules/`
- `public/` $\rightarrow$ merges with `[root]/public/`
- `resources/` $\rightarrow$ merges with `[root]/resources/`
- `routes/` $\rightarrow$ merges with `[root]/routes/`

> **Note**: When prompted by your file manager (cPanel File Manager, WinSCP, or Windows), choose **"Replace / Overwrite Existing Files"**.

---

## 🗄️ STEP 3: Run Database Migrations

The update introduces new database tables and columns (for Telegram notifications, fund transfer status, and audit logging).

### Method A: Via Terminal / SSH (Recommended)
Navigate to your project root folder and execute:
```bash
php artisan migrate --force
```

### Method B: Via Web Browser (If using Shared Hosting / cPanel without SSH)
If you don't have SSH access:
1. Log in to your UltimatePOS Admin account.
2. Visit your domain URL with the migration route:
   `https://yourdomain.com/install/update`
   *(or run the provided migration `.sql` script via phpMyAdmin).*

---

## 🧹 STEP 4: Clear Laravel Caches (Required)

To ensure Laravel registers all new routes, view templates, and configuration:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

> **If on Shared cPanel without terminal**:
> Log in as Superadmin, go to **Settings $\rightarrow$ Clear Cache** in your POS admin menu.

---

## ⚙️ STEP 5: Post-Installation Setup & Role Permissions

### 1. Enable New Permissions for User Roles
1. Go to **User Management $\rightarrow$ Roles**.
2. Click **Edit** on your Admin / Manager role.
3. Scroll through permissions or use the new **Permission Search Bar**:
   - Check **Account $\rightarrow$ Pending Transfer Approval / Reject**.
   - Check **Purchase $\rightarrow$ Receive Products**.
   - Check **Telegram $\rightarrow$ Access Telegram Settings**.
4. Click **Update**.

### 2. Configure Telegram Notifications (Optional)
1. Go to **Settings $\rightarrow$ Telegram Settings** (or `/telegram-settings`).
2. Add your **Telegram Bot Token**.
3. Create your **Telegram Groups** and assign topic IDs for Sales, Expenses, and Product alerts.

---

## 🌐 STEP 6: Refresh Cashier & Client Browsers

Because JavaScript files (`pos.js`, `product.js`, `purchase.js`) have been upgraded:
- Cashiers and staff should perform a **Hard Refresh** on their browser:
  - **Windows**: `Ctrl + F5`
  - **Mac**: `Cmd + Shift + R`
  - **Mobile Browser**: Clear browser cache / history.

---

## 🔄 Rollback Plan (In case of issues)

If any unforeseen error occurs:
1. Restore your database backup `.sql` file via phpMyAdmin.
2. Restore your previous source code ZIP.
3. Run `php artisan cache:clear && php artisan view:clear`.
