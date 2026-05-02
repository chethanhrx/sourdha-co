# 🏢 Divyachethana Souhardha Sahakari Sangha Website

[![Project Status: Active](https://img.shields.io/badge/Project%20Status-Active-brightgreen.svg)](https://github.com/chethanhrx/souharda-co)
[![Language: Kannada](https://img.shields.io/badge/Language-Kannada%20%2F%20English-blue.svg)](#)
[![Tech: HTML/CSS/JS/PHP](https://img.shields.io/badge/Tech-HTML%20%7C%20CSS%20%7C%20JS%20%7C%20PHP-orange.svg)](#)

The official web platform for **Divyachethana Souhardha Sahakari Sangha Niyamitha**, a cooperative society based in Thirthahalli. This project was developed as part of a professional internship, focusing on delivering a feature-rich, multi-language experience for the local community.

---

## ✨ Key Features

- **🌐 Multi-Language Support:** Full accessibility in both Kannada and English with a seamless toggle system.
- **🧮 Smart Interest Calculator:** Real-time calculation for Fixed Deposit (FD) and Recurring Deposit (RD) returns, including special rates for senior citizens.
- **🔐 Admin Control Suite:** A secure CMS to manage notifications, document uploads, photo gallery albums, and business service listings.
- **📢 Dynamic Notifications:** An interactive carousel for the latest society news, alerts, and meeting announcements.
- **📂 Document Vault:** A centralized repository for annual reports, audit statements, and official documents organized by fiscal year.
- **📸 Event Gallery:** A beautiful, responsive gallery with lightbox support to showcase society events and gatherings.
- **💼 Business Service Showcase:** Dynamic listings for additional ventures like Xerox, Printing, and Stationery services.
- **📱 Mobile-First Design:** Fully responsive interface optimized for Android, iOS, and desktop browsers.

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, Vanilla CSS3 (Custom Properties), Vanilla JavaScript (ES6+)
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Integration:** Google Translate API, FontAwesome 6, Google Fonts (Noto Sans Kannada)

---

## 📂 Project Structure

```text
/
├── admin/              # Admin Panel interface
├── api/                # PHP Backend API endpoints
├── assets/             # Images, Logos, and Videos
├── css/                # Centralized stylesheet (style.css)
├── js/                 # Core logic (main.js)
├── uploads/            # Dynamically uploaded files (Docs/Images)
├── index.html          # Main landing page
├── documents.html      # Public document repository
├── gallery.html        # Interactive photo gallery
└── db.sql              # Database schema & initialization
```

---

## 🚀 Getting Started

### Prerequisites
- A local server environment (XAMPP, WAMP, or MAMP)
- MySQL Database

### Installation
1.  **Clone the repository:**
    ```bash
    git clone https://github.com/chethanhrx/souharda-co.git
    ```
2.  **Database Setup:**
    - Open phpMyAdmin.
    - Create a database named `divyachethana`.
    - Import the `db.sql` file.
3.  **Configure Environment:**
    - Edit `api/config.php` to match your database credentials.
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', 'your_password');
    define('DB_NAME', 'divyachethana');
    ```
4.  **Run the Project:**
    - Move the project folder to your server's root (e.g., `htdocs`).
    - Visit `http://localhost/divyachethana` in your browser.

---

## 🔐 Admin Access

The administrative dashboard is located at `/admin`.
- **Default Username:** `admin`
- **Default Password:** `divya2012`
*(Please change the password immediately after your first login via the Settings tab)*

---

## 🎓 Internship Project

This project was proudly designed and developed by the **Department of BCA, Tunga Mahavidyalaya (Batch 2023-26)**.

### Team Members
- [Chethan Kumar H.R](https://github.com/chethanhrx)
- [Raajath B.N](https://github.com/Rajathacharya24)
- [Harsha](https://github.com/Harxshz7)
- ShreeNidhi
- Abhishek C.P
- Nandan H.N
- Sumanth G.N

---

## 📄 License

This project is developed for **Divyachethana Souhardha Sahakari Sangha Niyamitha**. All rights reserved.

---
<p align="center">Designed with ❤️ in Thirthahalli</p>
