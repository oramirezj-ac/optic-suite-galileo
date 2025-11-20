# Optic Suite Galileo (V2)

**Comprehensive Management System for Optometry Clinics**

Optic Suite Galileo is a custom-built web application designed to digitize and optimize the entire workflow of an optometry clinic. The system manages the complete patient lifecycle: from clinical records and prescription history to product sales and financial tracking of installments and balances.

Currently in **MVP (Minimum Viable Product)** status, specifically engineered to facilitate the mass migration of historical records (2023-Present) while supporting daily operations.

---

## 🚀 Key Features

### 1. Patient Management (Clinical Records)
* **Centralized Directory:** Full CRUD for patient management.
* **Smart Duplicate Detection:** Intelligent algorithm during creation that checks for name and phone number matches to prevent "dirty data" and duplicate records.
* **"At-a-Glance" Patient View:** A unified interface displaying personal information, clinical summary, and commercial history on a single screen, eliminating hidden tabs.

### 2. Clinical Module (Consultations & Prescriptions)
* **Consultation History:** Chronological record of patient visits.
* **Specialized Prescription Capture:**
    * Dynamic forms for Right Eye (OD) and Left Eye (OS/OI).
    * Support for Sphere, Cylinder, Axis, and Addition values.
    * Classification by exam type (Final, Autorefractor, Lensometer, etc.).
* **Clinical Visualization:** Comparative table that visually groups OD/OS prescriptions for quick interpretation by the optometrist.

### 3. Commercial Module (Sales/POS)
* **Agile Point of Sale:** Optimized capture flow designed for rapid data migration.
* **Flexible Description:** Free-text observation fields for detailing frames, materials, and lens treatments (ideal for capturing legacy data with varying formats).
* **Folio Validation:** Strict control of receipt numbers (0001-9999) with support for contingency suffixes to handle legacy duplicates.

### 4. Financial Module (Installments)
* **Balance Tracking:** Automatic calculation of `Total Cost` vs `Total Paid`.
* **Partial Payments:** System supports multiple installments per sale.
* **Status Automation:** The system automatically updates the sale status (`Pending` / `Paid`) based on the remaining balance whenever a payment is registered or deleted.

---

## 🛠️ Architecture & Tech Stack

The project was built using a **Native MVC (Model-View-Controller)** architecture, without relying on heavy frameworks. This approach ensures optimal performance and demonstrates complete control over the codebase.

* **Language:** PHP 8.x (Vanilla).
* **Database:** MariaDB / MySQL.
* **Frontend:** HTML5, CSS3 (Modular architecture with CSS Variables), JavaScript (Vanilla).
* **Design Patterns:**
    * **MVC:** Strict separation of business logic, data access, and user interface.
    * **Singleton:** Used for the Database Connection instance.
    * **View Helpers:** Static classes (`FormHelper`, `FormatHelper`) to encapsulate repetitive presentation logic (DRY Principle).
    * **SQL Transactions:** Implemented to ensure data integrity when saving Sales and Payments simultaneously.

### Project Structure

```text
/app
  ├── Controllers/      # Business logic (PatientController, VentaController, etc.)
  ├── Models/           # Data access objects (PDO, SQL queries)
  ├── Views/            # User Interface (HTML/PHP)
  │    ├── patients/
  │    ├── consultas/   # Consultations
  │    ├── graduaciones/# Prescriptions
  │    └── ventas/      # Sales
  └── Helpers/          # Reusable code generators (HTML, Formatting)
/config                 # DB and Session configuration
/public                 # Entry point (index.php, assets CSS/JS) 
```

---

## 📋 Prerequisites & Installation

1.  **Web Server:** Apache (XAMPP, Laragon, or similar).
2.  **Database:** Create a database named `optica_galileo_db`.
3.  **Setup:**
    * Clone the repository.
    * Import the SQL script `/database/schema.sql` (or provided structure).
    * Configure database credentials in the `.env` file or `/config/database.php`.
4.  **Run:** Point your web server to the `/public` folder.

---

## 🔄 Workflow

The system is designed with a **Patient-Centric** flow:

1.  **Search/Create Patient:** The starting point is always the patient.
2.  **Profile:** Access all modules from the patient's profile.
3.  **Clinical:** Register the Consultation (Appointment) and then the Prescriptions.
4.  **Sale:** Generate a Sales Note linked to the patient.
5.  **Payment:** Register installments (down payment and liquidation) in the "Sales Hub".

---

## 🔮 Roadmap

- [ ] Implementation of a Product Catalog (Frames/Lenses) to replace free-text capture.
- [ ] Visual Acuity (VA) and Visual Correction (VC) Module.
- [ ] PDF Report Generation (Sales Notes and Prescriptions).
- [ ] Dashboard with monthly sales statistics.

---

**Developed by:** [Omar Ramirez]  
**License:** Private / Intellectual Property of Óptica Galileo.