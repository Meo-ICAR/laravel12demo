# User Manual – [Your Project Name]

## 1. Introduction
- **About this Manual:**
  This manual provides step-by-step instructions for using the [Your Project Name] web application.
- **System Requirements:**
  - Modern web browser (Chrome, Firefox, Edge, Safari)
  - Internet connection
- **How to Access the Application:**
  - Go to: [your-app-url]
  - Enter your username and password

---

## 2. Getting Started
### Logging In
1. Navigate to the login page.
2. Enter your credentials and click **Login**.

### Navigating the Dashboard
- After login, you will see the main dashboard with quick stats and navigation.
- The sidebar provides access to all main modules.

### Understanding the Sidebar Menu
- The sidebar is organized by functional areas: Dashboards, Fatturazione, Call Center, Anagrafiche, User Settings, Pratiche, Invoiceins, and Filament Admin.

---

## 3. Dashboards
**Summary:**
The Dashboards section provides a visual overview of key business metrics, including invoices, commissions, calls, and leads. Use these dashboards to monitor performance and trends at a glance.

### Main Dashboard
**Purpose:** Overview of the entire system's key metrics.

**Step-by-step:**
1. Click **Dashboard > Main Dashboard** in the sidebar.
2. Review the summary cards and charts for a quick status update.
3. Use any available filters to adjust the displayed data.

### Invoices Dashboard
**Purpose:** Visualizes invoice statistics and trends.

**Step-by-step:**
1. Click **Dashboard > Invoices Dashboard**.
2. View charts and tables related to invoices.
3. Use filters to focus on specific periods or statuses.

### Provvigioni Dashboard
**Purpose:** Shows commission (provvigioni) stats and trends.

**Step-by-step:**
1. Click **Dashboard > Provvigioni Dashboard**.
2. Analyze commission data using the provided widgets and charts.

### Calls Dashboard
**Purpose:** Displays call center activity and KPIs.

**Step-by-step:**
1. Click **Dashboard > Calls Dashboard**.
2. Review call statistics and performance indicators.

### Leads Dashboard
**Purpose:** Shows lead generation and conversion data.

**Step-by-step:**
1. Click **Dashboard > Leads Dashboard**.
2. Examine lead metrics and conversion rates.

### Filament Admin
**Summary:**
The Filament Admin panel offers advanced resource management and dashboards for administrators.

**Step-by-step:**
1. Click **Filament Admin** in the sidebar.
2. Use the navigation to manage resources, view dashboards, and access advanced features.

---

## 4. Fatturazione
**Summary:**
The Fatturazione section manages all financial documents, including commissions (provvigioni), proforma invoices, and supplier invoices. It supports importing, viewing, editing, and reconciling records.

### Provvigioni Import
**Purpose:** Import commission records from a CSV file.

**Step-by-step:**
1. Click **Fatturazione > Provvigioni Import**.
2. Click the **Import Provvigioni** button.
3. Select your CSV file and upload it.
4. Review the import summary and confirm.

### Provvigioni
**Purpose:** Manage and review commission records.

**Step-by-step:**
1. Click **Fatturazione > Provvigioni**.
2. Browse the list of provvigioni.
3. Use filters to search by status, date, or supplier.
4. Click **View** to see details, **Edit** to modify, or **Delete** to remove a record.
5. Use the **Import** button to add new records from a file.

### Summary (Proforma Summary)
**Purpose:** View summarized data for proforma commissions.

**Step-by-step:**
1. Click **Fatturazione > Summary**.
2. Review the summary tables and charts.

### Proforma
**Purpose:** Manage proforma invoices.

**Step-by-step:**
1. Click **Fatturazione > Proforma**.
2. Browse, create, or edit proforma invoices.
3. Use the **Send Email** button to email a proforma.
4. Use the **Import** button to upload proformas in bulk.

#### Index View: Buttons & Actions
- **Filters**: Filter by supplier, status, email subject, sent/paid date, compensation, total, etc.
  - **Filter**: Applies the selected filters.
  - **Reset**: Clears all filters.
- **Send Bulk Emails**: Send emails to selected proformas (enabled when items are selected).
- **Table Actions**:
  - **View**: View proforma details.
  - **Edit**: Edit the proforma.
  - **Delete**: Delete the proforma (with confirmation).
  - **Email Body**: Opens a modal to view the email body.
  - **Email Simulation**: Opens a modal to simulate sending the email.

### Invoiceins Import
**Purpose:** Import supplier invoices from a file.

**Step-by-step:**
1. Click **Fatturazione > Invoiceins Import**.
2. Click the **Import CSV/Excel** button.
3. Select your file and upload.
4. Confirm the import.

### Invoices
**Purpose:** Manage and reconcile invoices.

**Step-by-step:**
1. Click **Fatturazione > Invoices**.
2. Browse, filter, and search invoices.
3. Use **View**, **Edit**, **Delete**, or **Check** (reconcile) actions.
4. Use the **Import** button to add invoices from a file.

#### Index View: Buttons & Actions
- **Reconciliation Dashboard**: Opens the reconciliation dashboard for invoice matching.
- **Import from CSV/Excel**: Opens the import page for uploading invoices.
- **Dashboard**: Opens the analytics dashboard for invoices.
- **Filters & Sorting**: Filter by status, supplier, date range, and sort by various fields.
  - **Apply Filters**: Applies the selected filters.
  - **Clear**: Resets all filters.
- **Table Actions**:
  - **View**: View invoice details.
  - **Edit**: Edit the invoice.
  - **Delete**: Delete the invoice (with confirmation).

### Riconciliazione (Reconciliation)
**Purpose:** Match invoices with provvigioni for reconciliation.

**Step-by-step:**
1. Click **Fatturazione > Riconciliazione**.
2. Select an invoice to reconcile.
3. Choose provvigioni to match.
4. Click **Reconcile** and confirm if the sum is less than the invoice total.

---

## 5. Call Center
**Summary:**
The Call Center section manages leads and call records, supporting tracking, follow-up, and performance analysis.

### Leads
**Purpose:** Manage and track sales leads.

**Step-by-step:**
1. Click **Call Center > Leads**.
2. Browse, add, or edit leads.
3. Use filters to search and segment leads.

#### Index View: Buttons & Actions
- **Filter Form**: Filter by ID, campaign, list, name, phone, operator, outcome, city, province, email, status, last call date, etc.
  - **Filter**: Applies the selected filters.
  - **Clear**: Resets all filters.
  - **Sort by**: Choose field and direction for sorting.
- **Import Leads**: Import leads from a file.
- **Dashboard**: Opens the leads analytics dashboard.
- **Create Lead**: Add a new lead.
- **Table Actions**:
  - **View**: View lead details.
  - **Edit**: Edit the lead.
  - **Delete**: Delete the lead.

### Calls
**Purpose:** Manage and review call records.

**Step-by-step:**
1. Click **Call Center > Calls**.
2. View call logs and details.
3. Add or edit call records as needed.

#### Index View: Buttons & Actions
- **Filter Calls**: Filter by called number, call status, outcome, user, date range, etc.
  - **Filter**: Applies the selected filters.
  - **Clear**: Resets all filters.
  - **Sort by**: Choose field and direction for sorting.
- **Import Calls**: Import calls from a file.
- **Calls Dashboard**: Opens the analytics dashboard for calls.
- **Table Actions**:
  - **View**: View call details.
  - **Edit**: Edit the call.
  - **Delete**: Delete the call (with confirmation).

---

## 6. Anagrafiche
**Summary:**
The Anagrafiche section manages master data for suppliers (Fornitori) and clients (Clienti).

### Fornitori
**Purpose:** Manage supplier records.

**Step-by-step:**
1. Click **Anagrafiche > Fornitori**.
2. Browse, add, edit, or delete suppliers.
3. Use the **Import** button to upload suppliers in bulk.

#### Index View: Buttons & Actions
- **Filter Fornitori**: Filter by name, coordinator, and sort by various fields.
  - **Search**: Applies the selected filters.
  - **Clear**: Resets all filters.
- **Import CSV**: Opens a modal to import suppliers from a CSV file.
- **Import Invoiceins to Invoices**: Transfers eligible invoiceins to invoices (with confirmation).
- **Table Actions**:
  - **COGE Link**: If present, links to the supplier's invoices.
  - **View**: View supplier details.
  - **Edit**: Edit the supplier.
  - **Delete**: Delete the supplier (with confirmation).

### Clienti
**Purpose:** Manage client records.

**Step-by-step:**
1. Click **Anagrafiche > Clienti**.
2. Browse, add, edit, or delete clients.

#### Index View: Buttons & Actions
- **Add Cliente**: Add a new client.
- **Table Actions**:
  - **COGE Link**: If present, links to the client's invoices.
  - **View**: View client details.
  - **Edit**: Edit the client.
  - **Delete**: Delete the client (with confirmation).

---

## 7. User Settings
**Summary:**
The User Settings section manages user accounts, roles, permissions, customer types, and company settings.

### Customer Types
**Purpose:** Manage types of customers.

**Step-by-step:**
1. Click **User Settings > Customer Types**.
2. Add, edit, or delete customer types.

### User Management
**Purpose:** Manage users, roles, and permissions.

**Step-by-step:**
1. Click **User Settings > User Management**.
2. Use the submenu to manage **Users**, **Roles**, and **Permissions**.
3. Add, edit, or delete records as needed.

#### Index View: Buttons & Actions (Users)
- **Import Users**: Import users from a file (if permitted).
- **Export Users**: Export the user list (if permitted).
- **Add New User**: Create a new user (if permitted).
- **Trashed Users**: View deleted (soft-deleted) users.
- **Table Actions**:
  - **View**: View user details.
  - **Edit**: Edit the user.
  - **Delete**: Delete the user (with confirmation).

### Setting (Companies)
**Purpose:** Manage company records.

**Step-by-step:**
1. Click **User Settings > Setting > Companies**.
2. Add, edit, or delete company records.

---

## 8. Pratiche
**Summary:**
The Pratiche section manages cases or applications (pratiche) in the system.

**Step-by-step:**
1. Click **Pratiche** in the sidebar.
2. Browse, add, or import pratiche.

#### Index View: Buttons & Actions
- **Importa CSV**: Opens a modal to import practices from a CSV file.
- **Table Actions**:
  - **View**: View practice details.

---

## 9. Invoiceins
**Summary:**
The Invoiceins section manages supplier invoice records.

**Step-by-step:**
1. Click **Invoiceins** in the sidebar.
2. Browse and manage invoiceins records.

#### Index View: Buttons & Actions
- **Filter Invoiceins**: Filter by supplier name and document type.
  - **Search**: Applies the selected filters.
  - **Clear**: Resets all filters.
- **Import CSV/Excel**: Opens a modal to import invoiceins from a file.
- **Table Actions**:
  - **View**: View invoicein details.

---

## 10. Common Actions
### Using Action Buttons
- **View:** <i class="fas fa-eye"></i> – See details
- **Edit:** <i class="fas fa-edit"></i> – Edit record
- **Delete:** <i class="fas fa-trash"></i> – Remove record (with confirmation)
- **Import:** <i class="fas fa-upload"></i> – Import data from file

### Exporting Data
- Use export buttons where available to download CSV/Excel files.

### Understanding Status Badges
- Colored badges indicate status (e.g., Paid, Unpaid, Proforma, Fatturato).

---

## 11. Troubleshooting & FAQ
- **I forgot my password:** Use the "Forgot Password" link on the login page.
- **I can't find a record:** Use filters and search bars.
- **Who to contact for help:** [Support contact/email]

---

## 12. Glossary
- **Fornitore:** Supplier
- **Provvigioni:** Commissions
- **Proforma:** Proforma invoice
- **Pratiche:** Cases/Applications
- **Filament:** Admin panel framework

---

> **[Add screenshots and more detailed instructions as needed for each section!]**
