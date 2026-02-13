# Xivex - Luxury Streetwear E-Commerce

**Xivex** is a modern, responsive e-commerce web application built with PHP and MySQL. It features a hybrid "Gen Z Luxury" aesthetic, real-time live chat for customer support, and a comprehensive admin dashboard.

![Xivex Banner](images/hero_cartoon.png)
*(Note: Replace with actual screenshot path if available)*

## 🚀 Features

*   **Front-End**:
    *   Dynamic Product Catalog with Filtering & Sorting.
    *   Shopping Cart & Checkout System.
    *   Real-time Live Chat Widget (AJAX Polling).
    *   Responsive Design (Mobile-First).
    *   Contact Page with Google Maps.
*   **Back-End (Admin)**:
    *   Dashboard with Sales Analytics & Charts.
    *   Product Management (Add, Edit, Delete, Variants).
    *   Order Management.
    *   Live Chat Dashboard for responding to customers.
    *   Secure Authentication.

## 🛠️ Technology Stack

*   **Backend**: PHP 8.0+
*   **Database**: MySQL / MariaDB
*   **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
*   **Server**: Apache (via XAMPP/MAMP)

## 📦 Installation Guide

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/Xivex.git
    cd Xivex
    ```

2.  **Setup Database**
    *   Open **phpMyAdmin** (http://localhost/phpmyadmin).
    *   Create a new database named `xivex_store`.
    *   Import the `database.sql` file located in the root directory.

3.  **Configure Environment**
    *   Create a `.env` file in the root directory (copy from `.env.example` if available).
    *   Add your database credentials:
        ```env
        DB_HOST=localhost
        DB_NAME=xivex_store
        DB_USER=root
        DB_PASS=
        ```

4.  **Run the Project**
    *   Move the project folder to `htdocs` (if using XAMPP).
    *   Access the site at: `http://localhost/Xivex/`

## 🔑 Default Admin Credentials

*   **Login URL**: `http://localhost/Xivex/admin/login.php`
*   **Username**: `admin`
*   **Password**: `Xivex@2024` *(Please change after first login)*

## 🔒 Security Note

*   Configuration files (`.env`) containing passwords are excluded from Git for security.
*   Ensure to set up your own `.env` file when deploying.

---
Developed by [Your Name/Team]
