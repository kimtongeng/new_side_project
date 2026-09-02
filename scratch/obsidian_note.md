# System Upgrade & Feature Release (v2026.8) - User Filters & Essentials Module

### Commit Range: `a6ae9cb5d3daaf88f488d189fa841e5520c52ece` &rarr; `7a87b36b69fcc91f404fe0112a7ac6e40840b1c9`

---

## 📦 Package Information
- **ZIP File**: `User_Management_Filters_And_Essentials_Module_Updates.zip` / `Updates_a6ae9cb_to_7a87b36.zip`
- **Location**: `C:\side project\new side\update_ai\`
- **PDF Guide**: `Upgrade_Updates_a6ae9cb_to_7a87b36.pdf`
- **Total Files Included**: 446 files

---

## 🌟 Key Features & Updates

1. **User Management Advanced Multi-Filters**:
   - Filter by Business Location
   - Filter by Username
   - Filter by User Role
   - Filter by Status (Active / Inactive)
   - Filter by Date of Birth Range (with DateRangePicker)
   - Filter by Gender (Male / Female / Others)
   - Filter by Department
   - Filter by Designation
   - One-Click Reset All Filters button (`#reset_user_filters`)

2. **Quick AJAX Status Toggle**:
   - Direct status switching (Active &harr; Inactive) via AJAX directly from user list actions without page refresh.
   - Dedicated backend route: `/users/update-status/{id}`.

3. **Complete Essentials & HRM Module (436 files)**:
   - **HRM & Essentials Dashboards**: Summary metrics, birthday reminders, upcoming holidays.
   - **Attendance & Shifts**: Shift assignments, attendance tracking by date/shift, clock-in/out, Excel template import.
   - **Payroll System**: Payroll groups, allowances & deductions, payments, and notifications.
   - **Leave Management**: Leave types, applications, approval workflow, activity log, and user leave summary.
   - **Tasks & To-Dos**: Task assignments, comments, file attachments, and shared documents.
   - **Knowledge Base**: Structured KB articles and categories.
   - **Sales Targets & Reminders**: Sales target settings and automated reminders.

4. **UI & Responsive Enhancements**:
   - Layout CSS styling improvements in `resources/views/layouts/partials/css.blade.php`.
   - Translation keys added in `lang/en/lang_v1.php` and `lang/en/report.php`.

---

## 🚀 Step-by-Step Installation Instructions

### Step 1: File Replacement
Extract `User_Management_Filters_And_Essentials_Module_Updates.zip` directly into your UltimatePOS root folder and overwrite existing files:

| Folder Name | Path | Action | Description |
| :--- | :--- | :--- | :--- |
| **controllers** | `app/Http/Controllers` | Replace | `ManageUserController.php`, `UserController.php` |
| **utils** | `app/Utils` | Replace | `Util.php` |
| **Modules (Essentials)** | `Modules/Essentials` | Add / Replace | Complete HRM & Essentials Module |
| **manage_user views** | `resources/views/manage_user` | Replace | `index.blade.php`, `create.blade.php`, `edit.blade.php` |
| **layouts partials** | `resources/views/layouts/partials` | Replace | `css.blade.php` |
| **lang** | `lang/en` | Update | `lang_v1.php`, `report.php` |
| **routes** | `routes` | Replace | `web.php` |

---

### Step 2: Run Database Migrations
* **Via SSH / Terminal**:
  ```bash
  php artisan migrate --force
  ```
* **Via Browser**:
  Visit `https://<your-domain>/run-migrations` or `https://<your-domain>/install/update`

---

### Step 3: Clear Laravel Caches
* **Via Terminal**:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  php artisan route:clear
  ```
* **Via Admin Panel**:
  Go to **Settings $\rightarrow$ Clear Cache**

---

### Step 4: Role Permissions Setup
1. Go to **User Management $\rightarrow$ Roles** $\rightarrow$ Click **Edit** on Admin / Manager roles.
2. Grant access to Essentials module features (Attendance, Leaves, Payroll, To-Do, Knowledge Base) as desired.
3. Save changes.

---

### Step 5: Browser Refresh
Press **`Ctrl + F5`** (or `Cmd + Shift + R` on Mac) on client browsers to load updated CSS styles and DataTables filter scripts.
