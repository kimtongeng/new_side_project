# System Upgrade & Feature Release (v2026.8)

### Step 1: File Replacement

| Folder Name | Path | Action |
| :--- | :--- | :--- |
| **app** | `app` | Replace all files |
| **controllers** | `app >> Http >> Controllers` | Replace all files |
| **middleware** | `app >> Http >> Middleware` | Replace all files |
| **notifications** | `app >> Notifications` | Replace all files |
| **providers** | `app >> Providers` | Replace all files |
| **utils** | `app >> Utils` | Replace all files |
| **config** | `config` | Replace all files |
| **migrations** | `database >> migrations` | Replace all files |
| **lang** | `lang >> en` | Replace all files |
| **js** | `public >> js` | Replace all files |
| **account** | `resources >> views >> account` | Replace all files |
| **business** | `resources >> views >> business` | Replace all files |
| **contact** | `resources >> views >> contact` | Replace all files |
| **expense** | `resources >> views >> expense` | Replace all files |
| **layouts** | `resources >> views >> layouts` | Replace all files |
| **opening_stock** | `resources >> views >> opening_stock` | Replace all files |
| **product** | `resources >> views >> product` | Replace all files |
| **partials (product)** | `resources >> views >> product >> partials` | Replace all files |
| **purchase** | `resources >> views >> purchase` | Replace all files |
| **partials (purchase)** | `resources >> views >> purchase >> partials` | Replace all files |
| **purchase_order** | `resources >> views >> purchase_order` | Replace all files |
| **purchase_requisition** | `resources >> views >> purchase_requisition` | Replace all files |
| **purchase_return** | `resources >> views >> purchase_return` | Replace all files |
| **restaurant** | `resources >> views >> restaurant` | Replace all files |
| **role** | `resources >> views >> role` | Replace all files |
| **partials (role)** | `resources >> views >> role >> partials` | Replace all files |
| **sale_pos** | `resources >> views >> sale_pos` | Replace all files |
| **partials (sale_pos)** | `resources >> views >> sale_pos >> partials` | Replace all files |
| **receipts (sale_pos)** | `resources >> views >> sale_pos >> receipts` | Replace all files |
| **sell** | `resources >> views >> sell` | Replace all files |
| **sell_return** | `resources >> views >> sell_return` | Replace all files |
| **stock_adjustment** | `resources >> views >> stock_adjustment` | Replace all files |
| **stock_transfer** | `resources >> views >> stock_transfer` | Replace all files |
| **taxonomy** | `resources >> views >> taxonomy` | Replace all files |
| **telegram** | `resources >> views >> telegram` | Replace all files |
| **Modules (ExchangeCurrency)** | `Modules >> ExchangeCurrency` | Replace all files |
| **Modules (Loan)** | `Modules >> Loan` | Replace all files |
| **Modules (Manufacturing)** | `Modules >> Manufacturing` | Replace all files |
| **Modules (ProductCatalogue)** | `Modules >> ProductCatalogue` | Replace all files |
| **Modules (Repair)** | `Modules >> Repair` | Replace all files |
| **Modules (StockCount)** | `Modules >> StockCount` | Replace all files |
| **routes** | `routes` | Replace all files |

---

### Step 2: Add Table / Columns to Database

* **Option 1 (Via Web Browser):**
  * Go to this link: `<web url>/run-migrations` or `<web url>/install/update`  
    *Example:* `http://ltpos168.ptcservice.net/run-migrations`

* **Option 2 (Via SSH / Terminal):**
  * Run command:
    ```bash
    php artisan migrate --force
    ```

---

### Step 3: Clear System Cache

* **Via Terminal:**
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  php artisan route:clear
  ```
* **Via Admin Panel:**
  * Go to **Settings $\rightarrow$ Clear Cache**

---

### Step 4: Configure Permissions & Settings

1. **User Role Permissions**:
   * Go to **User Management $\rightarrow$ Roles** $\rightarrow$ Click **Edit** on Admin / Manager roles.
   * Check permissions for **Pending Transfer Approval / Reject**, **Telegram Settings**, and **Receive Products**.
2. **Telegram Bot Setup (Optional)**:
   * Go to **Settings $\rightarrow$ Telegram Settings** to enter Bot Token and Group/Topic IDs.
3. **Browser Refresh**:
   * Press **`Ctrl + F5`** (or `Cmd + Shift + R`) on cashier browsers to load new POS and Scanner scripts.
