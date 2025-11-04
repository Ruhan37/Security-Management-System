# LEFT and RIGHT OUTER JOIN Testing Guide

## Test 1: LEFT OUTER JOIN
**Purpose:** Get all incidents with their reporter names (even if reporter doesn't exist)

### Query Builder Settings:
1. **FROM Table:** `incidents`
2. **SELECT Columns:** `incidents.incident_id, incidents.title, incidents.status, users.name as reporter_name`
3. **JOIN Type:** `LEFT OUTER JOIN`
4. **Join Table:** `users`
5. **ON Condition:** `incidents.reported_by = users.user_id`
6. Click **Execute Query**

### Expected Result:
- All 15 incidents will be shown
- Incidents with valid reporters will show reporter name
- If any incident has invalid reporter ID, it will show NULL in reporter_name column

### Expected SQL:
```sql
SELECT incidents.incident_id, incidents.title, incidents.status, users.name as reporter_name
FROM incidents
LEFT OUTER JOIN users ON incidents.reported_by = users.user_id
```

---

## Test 2: RIGHT OUTER JOIN
**Purpose:** Get all users and their roles (including users without roles if any exist)

### Query Builder Settings:
1. **FROM Table:** `users`
2. **SELECT Columns:** `users.user_id, users.name, users.email, roles.role_name`
3. **JOIN Type:** `RIGHT OUTER JOIN`
4. **Join Table:** `roles`
5. **ON Condition:** `users.role_id = roles.role_id`
6. Click **Execute Query**

### Expected Result:
- All 10 roles will be shown
- Roles that have users assigned will show user details
- Roles without any users will show NULL in user columns
- You should see some roles (like "Auditor", "Help Desk Technician") with user data and some potentially without

### Expected SQL:
```sql
SELECT users.user_id, users.name, users.email, roles.role_name
FROM users
RIGHT OUTER JOIN roles ON users.role_id = roles.role_id
```

---

## Test 3: LEFT OUTER JOIN - Find Unassigned Vulnerabilities
**Purpose:** Show all vulnerabilities and who reported them (see if any have invalid reporter IDs)

### Query Builder Settings:
1. **FROM Table:** `vulnerabilities`
2. **SELECT Columns:** `vulnerabilities.vuln_id, vulnerabilities.title, vulnerabilities.severity, users.name as reporter`
3. **JOIN Type:** `LEFT OUTER JOIN`
4. **Join Table:** `users`
5. **ON Condition:** `vulnerabilities.reported_by = users.user_id`
6. Click **Execute Query**

### Expected Result:
- All 20 vulnerabilities shown
- Each vulnerability with its reporter name
- Any orphaned vulnerability (with invalid user_id) would show NULL

### Expected SQL:
```sql
SELECT vulnerabilities.vuln_id, vulnerabilities.title, vulnerabilities.severity, users.name as reporter
FROM vulnerabilities
LEFT OUTER JOIN users ON vulnerabilities.reported_by = users.user_id
```

---

## Test 4: RIGHT OUTER JOIN - All Assets with Incidents
**Purpose:** Show all incidents and their affected assets (some incidents may not have assets assigned)

### Query Builder Settings:
1. **FROM Table:** `incident_assets`
2. **SELECT Columns:** `incidents.incident_id, incidents.title, assets.asset_name`
3. **JOIN Type:** `RIGHT OUTER JOIN`
4. **Join Table:** `incidents`
5. **ON Condition:** `incident_assets.incident_id = incidents.incident_id`
6. **Add another JOIN** (if needed, or test with WHERE clause)
7. Click **Execute Query**

### Expected Result:
- All incidents shown
- Incidents with assets will show asset names
- Incidents without assets will show NULL in asset_name

---

## Key Differences to Observe:

### LEFT OUTER JOIN:
- Returns **ALL rows from LEFT table** (first table in FROM clause)
- Matching rows from RIGHT table
- NULL values in RIGHT table columns when no match

### RIGHT OUTER JOIN:
- Returns **ALL rows from RIGHT table** (second table in JOIN clause)
- Matching rows from LEFT table
- NULL values in LEFT table columns when no match

---

## Visual Comparison:

### Scenario: users (15 records) JOIN roles (10 records)

**INNER JOIN** (users + roles):
- Result: ~15 rows (only matching records)
- Shows: Users who have valid role assignments

**LEFT OUTER JOIN** (users + roles):
- Result: 15 rows (all users)
- Shows: All users, even if role_id is invalid (would show NULL in role_name)

**RIGHT OUTER JOIN** (users + roles):
- Result: 10 rows (all roles)
- Shows: All roles, even if no users have that role (would show NULL in user columns)

---

## How to Verify Results:

1. **Check Row Count:**
   - LEFT: Count should match left table row count (or more if one-to-many)
   - RIGHT: Count should match right table row count (or more if one-to-many)

2. **Look for NULL Values:**
   - LEFT: NULL values appear in RIGHT table columns
   - RIGHT: NULL values appear in LEFT table columns

3. **Compare with INNER JOIN:**
   - Run same query with INNER JOIN
   - OUTER JOINs should have more rows than INNER JOIN
   - Extra rows contain NULL values

---

## Quick Test Steps:

1. Open: http://localhost/DB_project/
2. Scroll to "Advanced Query Builder" section
3. Follow Test 1 settings above
4. Click "Execute Query"
5. Observe results and SQL generated
6. Click "Reset" button
7. Follow Test 2 settings
8. Compare results

---

## Expected Outcomes:

✅ **LEFT OUTER JOIN should show:**
- Total rows = left table count (minimum)
- All records from left table visible
- NULL in right table columns where no match

✅ **RIGHT OUTER JOIN should show:**
- Total rows = right table count (minimum)
- All records from right table visible
- NULL in left table columns where no match

✅ **Both should work without errors**

---

## Troubleshooting:

**If you get an error:**
- Check table names are correct (case-sensitive)
- Verify ON condition uses correct column names
- Ensure column names in SELECT include table prefixes

**If results look wrong:**
- Check which table is on LEFT (FROM table)
- Check which table is on RIGHT (JOIN table)
- Verify the ON condition matches correct columns

Good luck testing! 🧪
