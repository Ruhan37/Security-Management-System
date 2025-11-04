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
('Network Engineer'),
('System Administrator'),
('Security Manager'),
('Developer'),
('Database Administrator'),
('Help Desk Technician'),
('Auditor');

INSERT INTO Users (name, email, role_id, join_date) VALUES
('John Doe', 'john@company.com', 1, '2024-01-15'),
('Jane Smith', 'jane@company.com', 2, '2024-02-20'),
('Bob Johnson', 'bob@company.com', 3, '2024-03-10'),
('Alice Brown', 'alice@company.com', 2, '2024-04-05'),
('Charlie Wilson', 'charlie@company.com', 4, '2024-05-12'),
('David Martinez', 'david@company.com', 5, '2024-06-01'),
('Emma Davis', 'emma@company.com', 6, '2024-06-15'),
('Frank Miller', 'frank@company.com', 7, '2024-07-01'),
('Grace Lee', 'grace@company.com', 8, '2024-07-20'),
('Henry Taylor', 'henry@company.com', 9, '2024-08-05'),
('Isabella Garcia', 'isabella@company.com', 2, '2024-08-15'),
('Jack Anderson', 'jack@company.com', 10, '2024-09-01'),
('Kate Thomas', 'kate@company.com', 4, '2024-09-10'),
('Leo White', 'leo@company.com', 5, '2024-09-25'),
('Maria Rodriguez', 'maria@company.com', 2, '2024-10-01');

INSERT INTO Assets (asset_name, asset_type, criticality) VALUES
('Web Server 1', 'Server', 'High'),
('Web Server 2', 'Server', 'High'),
('Database Server', 'Database', 'Critical'),
('Database Server Backup', 'Database', 'High'),
('Firewall Primary', 'Network', 'Critical'),
('Firewall Secondary', 'Network', 'High'),
('Core Router', 'Network', 'Critical'),
('Edge Router', 'Network', 'High'),
('Workstation 1', 'Computer', 'Medium'),
('Workstation 2', 'Computer', 'Medium'),
('Workstation 3', 'Computer', 'Low'),
('Laptop Fleet 1-10', 'Computer', 'Medium'),
('Email Server', 'Server', 'High'),
('File Server', 'Server', 'Medium'),
('Backup Server', 'Server', 'High'),
('VPN Gateway', 'Network', 'High'),
('Load Balancer', 'Network', 'High'),
('Switch Main', 'Network', 'Medium'),
('Switch Floor 2', 'Network', 'Medium'),
('Printer Network', 'Computer', 'Low');

INSERT INTO Incidents (title, type, status, detected_date, resolved_date, reported_by) VALUES
('Unauthorized Access Attempt', 'Security Breach', 'Resolved', '2024-10-01', '2024-10-02', 2),
('Server Downtime', 'System Failure', 'Resolved', '2024-09-15', '2024-09-16', 3),
('Malware Detection on Workstation', 'Security Breach', 'In Progress', '2024-10-05', NULL, 2),
('Network Anomaly Detected', 'Network Issue', 'Open', '2024-10-08', NULL, 4),
('DDoS Attack Suspected', 'Security Breach', 'In Progress', '2024-10-10', NULL, 6),
('Database Performance Issue', 'System Failure', 'Resolved', '2024-10-12', '2024-10-13', 9),
('Phishing Email Campaign', 'Security Breach', 'Open', '2024-10-15', NULL, 2),
('Hardware Failure - Hard Drive', 'Hardware Failure', 'Resolved', '2024-10-16', '2024-10-17', 5),
('Unauthorized Software Installation', 'Policy Violation', 'In Progress', '2024-10-18', NULL, 11),
('Firewall Configuration Error', 'Configuration Error', 'Resolved', '2024-10-19', '2024-10-19', 4),
('Data Leak Suspected', 'Security Breach', 'Open', '2024-10-20', NULL, 6),
('VPN Connection Issues', 'Network Issue', 'In Progress', '2024-10-21', NULL, 13),
('Password Reset Flood', 'Suspicious Activity', 'Open', '2024-10-22', NULL, 2),
('Ransomware Alert', 'Security Breach', 'Open', '2024-10-23', NULL, 2),
('System Update Failed', 'System Failure', 'Resolved', '2024-10-24', '2024-10-25', 14);

INSERT INTO Incident_Assets (incident_id, asset_id) VALUES
(1, 1), (1, 5),
(2, 1),
(3, 9),
(4, 7), (4, 8),
(5, 1), (5, 2), (5, 5),
(6, 3),
(7, 13),
(8, 15),
(9, 10),
(10, 5), (10, 6),
(11, 3), (11, 14),
(12, 16),
(13, 13),
(14, 1), (14, 2), (14, 3),
(15, 15);

INSERT INTO Vulnerabilities (title, severity, report_date, reported_by) VALUES
('SQL Injection in Login Form', 'Critical', '2024-09-20', 2),
('Outdated SSL Certificate', 'High', '2024-09-25', 3),
('Weak Password Policy', 'Medium', '2024-10-01', 2),
('Unpatched OS Vulnerability', 'Critical', '2024-10-03', 4),
('XSS Vulnerability in Contact Form', 'High', '2024-10-05', 11),
('Missing Security Headers', 'Medium', '2024-10-07', 2),
('Open Port 23 (Telnet)', 'Critical', '2024-10-09', 6),
('Insecure File Upload', 'High', '2024-10-11', 2),
('CSRF Token Missing', 'Medium', '2024-10-13', 11),
('Default Admin Credentials', 'Critical', '2024-10-15', 6),
('Directory Traversal', 'High', '2024-10-17', 2),
('Outdated jQuery Library', 'Low', '2024-10-18', 11),
('Missing Rate Limiting', 'Medium', '2024-10-19', 2),
('Exposed Admin Panel', 'Critical', '2024-10-20', 6),
('Insecure Direct Object Reference', 'High', '2024-10-21', 2),
('Session Fixation', 'High', '2024-10-22', 11),
('Information Disclosure', 'Medium', '2024-10-23', 2),
('Weak Encryption Algorithm', 'High', '2024-10-24', 6),
('Missing Input Validation', 'Medium', '2024-10-25', 11),
('Cookie Without Secure Flag', 'Low', '2024-10-26', 2);

INSERT INTO Logs (user_id, activity, log_time) VALUES
(1, 'User login successful', '2024-10-27 08:00:00'),
(2, 'Security scan initiated', '2024-10-27 08:15:00'),
(3, 'System backup completed', '2024-10-27 08:30:00'),
(1, 'User permissions modified', '2024-10-27 09:00:00'),
(4, 'Incident report created', '2024-10-27 09:15:00'),
(2, 'Vulnerability assessment completed', '2024-10-27 09:30:00'),
(5, 'Server restart performed', '2024-10-27 09:45:00'),
(6, 'Firewall rules updated', '2024-10-27 10:00:00'),
(7, 'Code deployment successful', '2024-10-27 10:15:00'),
(8, 'Database optimization completed', '2024-10-27 10:30:00'),
(9, 'Ticket #1234 resolved', '2024-10-27 10:45:00'),
(10, 'Compliance audit started', '2024-10-27 11:00:00'),
(11, 'Malware scan completed', '2024-10-27 11:15:00'),
(12, 'Security report generated', '2024-10-27 11:30:00'),
(13, 'Network configuration changed', '2024-10-27 11:45:00'),
(14, 'System patch applied', '2024-10-27 12:00:00'),
(15, 'Security alert investigated', '2024-10-27 12:15:00'),
(1, 'User logged out', '2024-10-27 12:30:00'),
(2, 'Incident status updated', '2024-10-27 12:45:00'),
(3, 'Backup verification completed', '2024-10-27 13:00:00'),
(4, 'VPN access granted to new user', '2024-10-27 13:15:00'),
(5, 'System health check passed', '2024-10-27 13:30:00'),
(6, 'Security training completed', '2024-10-27 13:45:00'),
(7, 'API endpoint updated', '2024-10-27 14:00:00'),
(8, 'Database backup restored', '2024-10-27 14:15:00'),
(9, 'Password reset request processed', '2024-10-27 14:30:00'),
(10, 'Audit log reviewed', '2024-10-27 14:45:00'),
(11, 'Threat detected and blocked', '2024-10-27 15:00:00'),
(12, 'Monthly security report submitted', '2024-10-27 15:15:00'),
(13, 'Network bandwidth optimized', '2024-10-27 15:30:00'),
(14, 'System updates scheduled', '2024-10-27 15:45:00');
