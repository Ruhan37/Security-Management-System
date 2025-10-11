CREATE DATABASE IF NOT EXISTS security_management_db;
USE security_management_db;

-- Drop tables if they exist (in correct order to handle foreign key constraints)
DROP TABLE IF EXISTS Logs;
DROP TABLE IF EXISTS Vulnerabilities;
DROP TABLE IF EXISTS Incident_Assets;
DROP TABLE IF EXISTS Assets;
DROP TABLE IF EXISTS Incidents;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Roles;

-- Create Roles table
CREATE TABLE Roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE
);

-- Create Users table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    role_id INT NOT NULL,
    join_date DATE NOT NULL,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id) ON DELETE CASCADE
);

-- Create Incidents table
CREATE TABLE Incidents (
    incident_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    detected_date DATE NOT NULL,
    resolved_date DATE NULL,
    reported_by INT NOT NULL,
    FOREIGN KEY (reported_by) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Create Assets table
CREATE TABLE Assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(200) NOT NULL,
    asset_type VARCHAR(100) NOT NULL,
    criticality VARCHAR(50) NOT NULL
);

-- Create Incident_Assets junction table
CREATE TABLE Incident_Assets (
    incident_id INT NOT NULL,
    asset_id INT NOT NULL,
    PRIMARY KEY (incident_id, asset_id),
    FOREIGN KEY (incident_id) REFERENCES Incidents(incident_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES Assets(asset_id) ON DELETE CASCADE
);

-- Create Vulnerabilities table
CREATE TABLE Vulnerabilities (
    vuln_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    severity VARCHAR(50) NOT NULL,
    report_date DATE NOT NULL,
    reported_by INT NOT NULL,
    FOREIGN KEY (reported_by) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Create Logs table
CREATE TABLE Logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity TEXT NOT NULL,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO Roles (role_name) VALUES
('Administrator'),
('Security Analyst'),
('IT Manager'),
('User');

INSERT INTO Users (name, email, role_id, join_date) VALUES
('John Doe', 'john@company.com', 1, '2024-01-15'),
('Jane Smith', 'jane@company.com', 2, '2024-02-20'),
('Bob Johnson', 'bob@company.com', 3, '2024-03-10'),
('Alice Brown', 'alice@company.com', 2, '2024-04-05'),
('Charlie Wilson', 'charlie@company.com', 4, '2024-05-12');

INSERT INTO Assets (asset_name, asset_type, criticality) VALUES
('Web Server 1', 'Server', 'High'),
('Database Server', 'Database', 'Critical'),
('Firewall', 'Network', 'High'),
('Workstation 1', 'Computer', 'Medium'),
('Router', 'Network', 'High');

INSERT INTO Incidents (title, type, status, detected_date, resolved_date, reported_by) VALUES
('Unauthorized Access Attempt', 'Security', 'Open', '2024-10-01', NULL, 2),
('Server Downtime', 'System', 'Resolved', '2024-09-15', '2024-09-16', 3),
('Malware Detection', 'Security', 'In Progress', '2024-10-05', NULL, 2),
('Network Anomaly', 'Network', 'Open', '2024-10-08', NULL, 4);

INSERT INTO Incident_Assets (incident_id, asset_id) VALUES
(1, 1),
(1, 3),
(2, 1),
(3, 4),
(4, 5);

INSERT INTO Vulnerabilities (title, severity, report_date, reported_by) VALUES
('SQL Injection in Login Form', 'High', '2024-09-20', 2),
('Outdated SSL Certificate', 'Medium', '2024-09-25', 3),
('Weak Password Policy', 'Low', '2024-10-01', 2),
('Unpatched OS Vulnerability', 'Critical', '2024-10-03', 4);

INSERT INTO Logs (user_id, activity) VALUES
(1, 'User login successful'),
(2, 'Security scan initiated'),
(3, 'System backup completed'),
(1, 'User permissions modified'),
(4, 'Incident report created'),
(2, 'Vulnerability assessment completed');