# Job Portal & Recruitment Management System

A comprehensive recruitment management platform designed to bridge the gap between job seekers and employers. This system provides a seamless experience for posting jobs, managing applications, and tracking recruitment progress in real-time.

## 🚀 Features

### **For Job Seekers**
- **Unified Dashboard**: View application history, statuses, and notifications in one place.
- **Smart Job Search**: Browse and search for jobs across various categories and locations.
- **Resume Management**: Upload unique resumes for every job application to tailor your profile.
- **Real-time Notifications**: Get notified instantly when your application status changes (Shortlisted, Rejected, Hired).
- **Interview Tracking**: View and manage scheduled interview dates and types.

### **For Employers**
- **Powerful Dashboard**: High-level overview of active job postings and total applications received.
- **Job Management**: Create, view, and delete job postings with ease.
- **Applicant Tracking System (ATS)**: Review applicant profiles, download resumes, and update application statuses (Applied → Shortlisted → Hired).
- **Interview Scheduling**: Schedule online or offline interviews directly from the dashboard.
- **Company Branding**: Manage company profiles and information.

### **For Administrators**
- **Full Control**: Oversee all users and system activities.
- **Database Management**: Manage core tables and ensure system integrity.
- **Audit Support**: Track all recruitment activities across the platform.

---

## 🛠️ Tech Stack

- **Backend:** PHP (8+)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Fetch API / AJAX)
- **Styling:** Vanilla CSS, Google Fonts (Poppins, Segoe UI), Bootstrap (for specific components)
- **Server:** Apache (XAMPP)

---

## 📋 Installation Guide

### **Prerequisites**
- [XAMPP](https://www.apachefriends.org/) or any local server with PHP and MySQL support.

### **Local Setup**
1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/Nujat11/Job-Portal-Recruitment-Management-System.git
    ```
2.  **Move to Web Directory**:
    Copy the project folder to `C:\xampp\htdocs\` (or your server's root directory).

3.  **Database Configuration**:
    - Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
    - Create a new database named `job_portal`.
    - Import the provided `job_portal.sql` file located in the `Job_Portal/` directory.
    - (Optional) Run `create_admin_table.sql` to initialize the admin role.

4.  **Configure Connection**:
    Open `Job_Portal/connection.php` and update the database credentials if necessary:
    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "job_portal";
    ```

5.  **Run the Project**:
    Navigate to `http://localhost/Job-Portal-Recruitment-Management-System-main/Job_Portal/` in your browser.

---

## 📂 Project Structure

- `Job_Portal/index.php`: Landing page and job search.
- `Job_Portal/login.php`: Unified login system for all roles.
- `Job_Portal/seeker_dashboard.php`: Job seeker's personal area.
- `Job_Portal/employer_dashboard.php`: Main interface for employers.
- `Job_Portal/admin_dashboard.php`: Administrative control panel.
- `Job_Portal/apply.php`: Handles job applications and resume uploads.
- `Job_Portal/uploads/resumes/`: Directory where uploaded PDF resumes are stored.

---

## 🛡️ Security Features
- **Session Protection**: Restricted access to dashboards based on user roles.
- **Prepared Statements**: Used for MySQLi to prevent SQL Injection attacks.
- **File Validation**: Restricted file uploads to PDF format only for security.

---

## 📝 License
This project is for educational purposes. Feel free to use and modify it.

---
*Developed with ❤️ as a Comprehensive Recruitment Solution.*
