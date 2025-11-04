# Database Project Testing Guide

## Project: Security Management Database System
**Student:** Ruhan
**Database:** MySQL (Port 4306)
**Technologies:** PHP, MySQL, Bootstrap, JavaScript

---

## Pre-Testing Setup

### 1. Start Required Services
```bash
# Start XAMPP
# Make sure Apache and MySQL are running
# MySQL should be running on port 4306
```

### 2. Setup Database (First Time Only)
1. Open browser and go to: `http://localhost/DB_project/setup.php`
2. Click "Setup Database" button
3. Verify you see: "Database setup completed successfully!"

### 3. Access Main Application
- URL: `http://localhost/DB_project/`
- You should see the homepage with two main sections

---

## Testing Section 1: CRUD Operations

### Test 1.1 - Roles Table (CREATE)
**Steps:**
1. Click on "CRUD Operations" in navigation or scroll to CRUD section
2. Click on "Roles" in the left sidebar
3. Click "Add New Role" button
4. Fill in the form:
   - Role Name: `Security Analyst`
   - Description: `Analyzes security threats and vulnerabilities`
5. Click "Add Role"
6. **Expected Result:** New role appears in the table

### Test 1.2 - Roles Table (READ)
**Steps:**
1. On Roles page, view the table
2. **Expected Result:** All roles are displayed with columns: Role ID, Role Name, Description, Actions

### Test 1.3 - Roles Table (UPDATE)
**Steps:**
1. Click "Edit" button on any role (e.g., Security Analyst)
2. Modify the description to: `Expert in analyzing security threats`
3. Click "Update Role"
4. **Expected Result:** Role is updated and changes are visible in the table

### Test 1.4 - Roles Table (DELETE)
**Steps:**
1. Click "Delete" button on the Security Analyst role
2. Confirm deletion
3. **Expected Result:** Role is removed from the table

---

### Test 1.5 - Users Table (CREATE with Foreign Key)
**Steps:**
1. Click on "Users" in the left sidebar
2. Click "Add New User" button
3. Fill in the form:
   - Username: `john_doe`
   - Full Name: `John Doe`
   - Email: `john@example.com`
   - Role: Select "Admin" (dropdown)
   - Status: Select "Active"
4. Click "Add User"
5. **Expected Result:** New user appears in the table with role properly linked

### Test 1.6 - Users Table (READ with JOIN)
**Steps:**
1. View the Users table
2. **Expected Result:** See user data with role names displayed (not just role IDs)

### Test 1.7 - Users Table (UPDATE)
**Steps:**
1. Click "Edit" on John Doe
2. Change Status to "Inactive"
3. Change Role to "Analyst"
4. Click "Update User"
5. **Expected Result:** User is updated successfully

### Test 1.8 - Users Table (DELETE)
**Steps:**
1. Click "Delete" on John Doe
2. Confirm deletion
3. **Expected Result:** User is removed from the table

---

### Test 1.9 - Incidents Table (CREATE)
**Steps:**
1. Click on "Incidents" in the left sidebar
2. Click "Add New Incident" button
3. Fill in the form:
   - Title: `Suspicious Login Attempt`
   - Type: `Unauthorized Access`
   - Status: `Open`
   - Detected Date: Select today's date
   - Reported By: Select a user from dropdown
5. Click "Add Incident"
6. **Expected Result:** New incident appears in the table

### Test 1.10 - Incidents Table (UPDATE Status)
**Steps:**
1. Click "Edit" on the new incident
2. Change Status to "In Progress"
3. Click "Update Incident"
4. **Expected Result:** Status is updated

---

### Test 1.11 - Assets Table (CRUD)
**Steps:**
1. Click on "Assets" in the left sidebar
2. Click "Add New Asset"
3. Fill in:
   - Asset Name: `Web Server 01`
   - Asset Type: `Server`
   - Criticality: `High`
4. Click "Add Asset"
5. **Expected Result:** Asset is added
6. Edit the asset to change criticality to "Critical"
7. **Expected Result:** Asset is updated

---

### Test 1.12 - Vulnerabilities Table (CRUD)
**Steps:**
1. Click on "Vulnerabilities" in the left sidebar
2. Click "Add New Vulnerability"
3. Fill in:
   - Title: `SQL Injection in Login Form`
   - Severity: `Critical`
   - Report Date: Select today's date
   - Reported By: Select a user
4. Click "Add Vulnerability"
5. **Expected Result:** Vulnerability is added

---

### Test 1.13 - Logs Table (CRUD)
**Steps:**
1. Click on "Logs" in the left sidebar
2. Click "Add New Log"
3. Fill in:
   - User: Select a user
   - Activity: `User logged into system`
   - Log Time: Select current date and time
4. Click "Add Log"
5. **Expected Result:** Log entry is created

---

## Testing Section 2: Advanced Query Builder

### Test 2.1 - Simple SELECT Query
**Steps:**
1. Scroll to "Advanced Query Builder" section
2. FROM Table: Select "users"
3. Select Columns: Enter `*`
4. Leave other fields empty
5. Click "Execute Query"
6. **Expected Result:** All users are displayed with SQL query shown

### Test 2.2 - SELECT with Specific Columns
**Steps:**
1. FROM Table: Select "users"
2. Select Columns: Enter `username, full_name, email`
3. Click "Execute Query"
4. **Expected Result:** Only selected columns are displayed

### Test 2.3 - WHERE Condition (Equals)
**Steps:**
1. FROM Table: Select "users"
2. Select Columns: `*`
3. WHERE Conditions:
   - Column: Select "status"
   - Operator: Select "Equals (=)"
   - Value: Enter `Active`
4. Click "Execute Query"
5. **Expected Result:** Only active users are displayed

### Test 2.4 - WHERE Condition (LIKE - Pattern Matching)
**Steps:**
1. FROM Table: Select "incidents"
2. Select Columns: `*`
3. WHERE Conditions:
   - Column: Select "title"
   - Operator: Select "LIKE"
   - Value: Enter `Login`
4. Click "Execute Query"
5. **Expected Result:** All incidents with "Login" in the title are shown

### Test 2.5 - Multiple WHERE Conditions
**Steps:**
1. FROM Table: Select "vulnerabilities"
2. Select Columns: `*`
3. First WHERE Condition:
   - Column: "severity"
   - Operator: "Equals (=)"
   - Value: `Critical`
4. Click "+ Add Condition" button
5. Second WHERE Condition:
   - Column: "report_date"
   - Operator: ">="
   - Value: `2025-01-01`
6. Click "Execute Query"
7. **Expected Result:** Critical vulnerabilities reported in 2025

### Test 2.6 - JOIN Operation
**Steps:**
1. FROM Table: Select "users"
2. Select Columns: `users.username, users.full_name, roles.role_name`
3. JOIN Type: Select "INNER"
4. Join Table: Select "roles"
5. ON Condition: Enter `users.role_id = roles.role_id`
6. Click "Execute Query"
7. **Expected Result:** Users displayed with their role names

### Test 2.7 - GROUP BY with COUNT
**Steps:**
1. FROM Table: Select "users"
2. Select Columns: `role_id, COUNT(*) as user_count`
3. GROUP BY: Enter `role_id`
4. Click "Execute Query"
5. **Expected Result:** Count of users per role

### Test 2.8 - HAVING Clause
**Steps:**
1. FROM Table: Select "users"
2. Select Columns: `role_id, COUNT(*) as user_count`
3. GROUP BY: Enter `role_id`
4. HAVING: Enter `COUNT(*) > 1`
5. Click "Execute Query"
6. **Expected Result:** Only roles with more than one user

### Test 2.9 - ORDER BY
**Steps:**
1. FROM Table: Select "incidents"
2. Select Columns: `*`
3. ORDER BY Column: Select "detected_date"
4. Direction: Select "DESC"
5. Click "Execute Query"
6. **Expected Result:** Incidents sorted by newest first

### Test 2.10 - LIMIT Results
**Steps:**
1. FROM Table: Select "logs"
2. Select Columns: `*`
3. Limit Results: Enter `5`
4. Click "Execute Query"
5. **Expected Result:** Only 5 log entries displayed

### Test 2.11 - Complex Query (Combined Features)
**Steps:**
1. FROM Table: Select "incidents"
2. Select Columns: `*`
3. WHERE Condition:
   - Column: "status"
   - Operator: "!="
   - Value: `Closed`
4. ORDER BY Column: "detected_date"
5. Direction: "DESC"
6. Limit Results: `10`
7. Click "Execute Query"
8. **Expected Result:** Latest 10 open/in-progress incidents

### Test 2.12 - Complex JOIN Query
**Steps:**
1. FROM Table: Select "incidents"
2. Select Columns: `incidents.title, incidents.status, users.full_name as reported_by_name`
3. JOIN Type: "INNER"
4. Join Table: "users"
5. ON Condition: `incidents.reported_by = users.user_id`
6. Click "Execute Query"
7. **Expected Result:** Incidents with reporter's full name instead of ID

### Test 2.13 - Reset Button Test
**Steps:**
1. Fill in any query with multiple WHERE conditions
2. Click "Reset" button
3. **Expected Result:**
   - All form fields are cleared
   - Extra WHERE conditions are removed
   - Query results disappear

---

## Database Constraints Testing

### Test 3.1 - Foreign Key Constraint (ON DELETE)
**Steps:**
1. Try to delete a role that has users assigned to it
2. **Expected Result:** Should show an error or prevent deletion (foreign key constraint)

### Test 3.2 - NOT NULL Constraint
**Steps:**
1. Try to add a user without entering username
2. **Expected Result:** Should show validation error

### Test 3.3 - UNIQUE Constraint
**Steps:**
1. Try to add a user with an email that already exists
2. **Expected Result:** Should show error about duplicate email

---

## Presentation Tips for Your Teacher

### 1. Introduction (2 minutes)
- Explain the project purpose: Security Management Database
- Mention technologies: PHP, MySQL (port 4306), Bootstrap
- Show the home page design

### 2. Database Schema (3 minutes)
- Explain the 7 tables: roles, users, incidents, assets, incident_assets, vulnerabilities, logs
- Show relationships (foreign keys)
- Mention constraints (NOT NULL, UNIQUE, ON DELETE CASCADE)

### 3. CRUD Operations Demo (5 minutes)
- Pick 2-3 tables to demonstrate (e.g., Roles, Users, Incidents)
- Show all four operations: Create, Read, Update, Delete
- Highlight foreign key relationships (e.g., User → Role)

### 4. Query Builder Demo (5 minutes)
- Start simple: Basic SELECT with WHERE
- Show intermediate: JOIN operation
- Show advanced: Complex query with GROUP BY, HAVING, ORDER BY, LIMIT
- Demonstrate reset button

### 5. Special Features (2 minutes)
- Responsive UI (resize browser window)
- Form validation
- Dynamic WHERE conditions (add/remove)
- SQL query preview
- Error handling

### 6. Questions Preparation
**Be ready to answer:**
- Q: What database constraints did you implement?
  - A: Foreign keys, NOT NULL, UNIQUE, AUTO_INCREMENT, ON DELETE CASCADE

- Q: How did you prevent SQL injection?
  - A: Used PDO prepared statements with parameter binding

- Q: What's the difference between INNER JOIN and LEFT JOIN?
  - A: INNER shows only matching records, LEFT shows all from left table

- Q: Why use GROUP BY?
  - A: To aggregate data (like COUNT, SUM) for grouped categories

---

## Quick Test Checklist

Before presenting, verify:
- [ ] XAMPP Apache and MySQL are running
- [ ] Database is set up (run setup.php if needed)
- [ ] All 6 CRUD tables work (Create, Read, Update, Delete)
- [ ] Query Builder executes queries successfully
- [ ] Reset button clears form
- [ ] No console errors (press F12 to check)
- [ ] Sample data exists for demonstration
- [ ] All forms validate properly
- [ ] Foreign key relationships work
- [ ] JOIN queries return correct results

---

## Sample Queries to Prepare

### Query 1: All Active Users with Roles
```
FROM: users
SELECT: users.username, users.full_name, roles.role_name, users.status
JOIN: INNER JOIN roles
ON: users.role_id = roles.role_id
WHERE: status = 'Active'
```

### Query 2: Critical Open Incidents
```
FROM: incidents
SELECT: *
WHERE: status != 'Closed' AND type = 'Security Breach'
ORDER BY: detected_date DESC
```

### Query 3: User Activity Count
```
FROM: logs
SELECT: user_id, COUNT(*) as activity_count
GROUP BY: user_id
HAVING: COUNT(*) > 5
ORDER BY: activity_count DESC
```

---

## Troubleshooting

**If database connection fails:**
- Check XAMPP MySQL is running
- Verify port 4306 in config/database.php
- Run setup.php again

**If CRUD operations fail:**
- Check browser console for errors (F12)
- Verify PHP errors are displayed
- Check database table exists

**If queries don't work:**
- Verify table and column names match exactly
- Check SQL syntax in generated query
- Ensure proper quotes around string values

---

## Time Management for Demo

- **Total: 15-20 minutes**
- Setup & Introduction: 2 min
- CRUD Demo: 6 min
- Query Builder Demo: 7 min
- Special Features: 2 min
- Q&A: 3-5 min

Good luck with your presentation! 🎓
