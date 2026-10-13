# Internship Portal

A web-based application that connects students with companies offering internship opportunities. Students can create profiles, explore internships, apply for opportunities, and track their application status. Companies can post internships and manage student applications.

## 1. Project Information

Project Name: Internship Portal

Project Type: Web Application

Purpose: To simplify the process of publishing internship opportunities and managing student applications through a centralized platform.

## 2. Technologies Used

Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL
Database Connectivity:PDO (PHP Data Objects)
Development Environment:ByteXL / VS Code

## 3. Main Features

### Student
Student registration and login
Create and manage student profiles
Add skills and profile information
Browse available internships
Apply for internships
Track application status

### Company
Company registration and login
Create and manage internship listings
View applicants for internships
Shortlist or reject applicants

### General
Session-based authentication
Role-based dashboards
Database-backed application data

## 4. Project Structure


internship-portal/
├── index.php
├── login.php
├── register.php
├── logout.php
├── start.sh
├── README.md
├── config/
│   └── database.php
├── student/
├── company/
├── css/
└── js/


Additional PHP MySQL extension files may be stored in the parent development workspace, depending on the environment configuration.

## 5. Requirements

- PHP 8.4 or a compatible PHP version
- MySQL database server or the configured remote MySQL service
- PHP PDO extension
- PHP PDO MySQL driver
- A modern web browser

## 6. Database Configuration

Open:

config/database.php

Configure the database connection using the appropriate values for:

- Database host
- Database port
- Database name
- Database username
- Database password

Ensure that the database and all required tables exist before running the application.

Important: Do not publish real database passwords or other private credentials in a public repository.

## 7. How to Run the Project

### Using the configured ByteXL environment

1. Open the project in the terminal.
2. Make sure the required database service is available.
3. Navigate to the project directory:

   cd /home/workspace/my-project/internship-portal
  

4. Start the application:

   ./start.sh
  

5. Open the Simple Browser or another browser and visit:

   http://localhost:8080

6. Register an account or log in to explore the available features.

The provided startup script loads the configured PHP MySQL extensions before starting the PHP development server.

### Running on another computer

1. Install PHP and MySQL.
2. Enable the PHP PDO and PDO MySQL extensions.
3. Create the project database and import the required SQL schema.
4. Update `config/database.php` with the new database credentials.
5. Start the PHP development server from the project directory:

php -S 0.0.0.0:8080
   

6. Open `http://localhost:8080` in a browser.

The `start.sh` script and extension paths are configured for the development environment. On another computer, the PHP MySQL driver may need to be installed and enabled separately.

## 8. Troubleshooting

### Database connection error
Verify the database host, port, database name, username, and password.
Confirm that the database server is running and reachable.
Check that the required database and tables have been created.

### `could not find driver`
Confirm that PHP PDO is enabled.
Confirm that the PDO MySQL driver is loaded.
Restart the PHP server after correcting the extension configuration.

### Port already in use
Stop the existing PHP development server or choose an available port and update the browser URL accordingly.

## 9. Conclusion

The Internship Portal provides a centralized platform for students to discover internship opportunities and for companies to publish openings and manage applications. It demonstrates the integration of PHP, MySQL, and web technologies to develop a database-driven application.
