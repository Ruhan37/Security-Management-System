# JOIN Operations Guide

## Complete Guide to All JOIN Types in Your Query Builder

---

## 1. INNER JOIN (Equi Join)

**Description:** Returns only matching records from both tables based on the join condition.

**Use Case:** Get users with their role names

**Query Builder Settings:**
- FROM Table: `users`
- SELECT Columns: `users.name, users.email, roles.role_name`
- JOIN Type: `INNER JOIN`
- Join Table: `roles`
- ON Condition: `users.role_id = roles.role_id` *(Equi Join - uses = operator)*

**Expected SQL:**
```sql
SELECT users.name, users.email, roles.role_name
FROM users
INNER JOIN roles ON users.role_id = roles.role_id
```

**Result:** Only users who have a valid role assigned

---

## 2. LEFT OUTER JOIN

**Description:** Returns all records from left table and matching records from right table. Non-matching right records show NULL.

**Use Case:** Get all incidents with reporter names (including incidents without reporters)

**Query Builder Settings:**
- FROM Table: `incidents`
- SELECT Columns: `incidents.title, incidents.status, users.name as reporter`
- JOIN Type: `LEFT OUTER JOIN`
- Join Table: `users`
- ON Condition: `incidents.reported_by = users.user_id`

**Expected SQL:**
```sql
SELECT incidents.title, incidents.status, users.name as reporter
FROM incidents
LEFT OUTER JOIN users ON incidents.reported_by = users.user_id
```

**Result:** All incidents shown. If no reporter exists, reporter column shows NULL

---

## 3. RIGHT OUTER JOIN

**Description:** Returns all records from right table and matching records from left table. Non-matching left records show NULL.

**Use Case:** Get all roles and users in those roles (including roles with no users)

**Query Builder Settings:**
- FROM Table: `users`
- SELECT Columns: `roles.role_name, users.name`
- JOIN Type: `RIGHT OUTER JOIN`
- Join Table: `roles`
- ON Condition: `users.role_id = roles.role_id`

**Expected SQL:**
```sql
SELECT roles.role_name, users.name
FROM users
RIGHT OUTER JOIN roles ON users.role_id = roles.role_id
```

**Result:** All roles shown. Roles without users show NULL in user name

---

## 4. CROSS JOIN

**Description:** Returns Cartesian product - every row from first table combined with every row from second table.

**Use Case:** Generate all possible combinations of users and assets

**Query Builder Settings:**
- FROM Table: `users`
- SELECT Columns: `users.name, assets.asset_name`
- JOIN Type: `CROSS JOIN`
- Join Table: `assets`
- ON Condition: *(Leave empty - not needed)*

**Expected SQL:**
```sql
SELECT users.name, assets.asset_name
FROM users
CROSS JOIN assets
```

**Result:** If 15 users and 20 assets, you get 300 rows (15 × 20)

---

## 5. NATURAL JOIN

**Description:** Automatically joins tables on columns with the same name in both tables.

**Use Case:** Join users and logs (both have user_id column)

**Query Builder Settings:**
- FROM Table: `users`
- SELECT Columns: `users.name, logs.activity, logs.log_time`
- JOIN Type: `NATURAL JOIN`
- Join Table: `logs`
- ON Condition: *(Leave empty - automatic)*

**Expected SQL:**
```sql
SELECT users.name, logs.activity, logs.log_time
FROM users
NATURAL JOIN logs
```

**Result:** Joins automatically on `user_id` column (present in both tables)

**⚠️ Warning:** Only works if tables have columns with identical names

---

## 6. SELF JOIN

**Description:** Joins a table to itself using an alias to compare rows within the same table.

**Use Case:** Find users who joined on the same date

**Query Builder Settings:**
- FROM Table: `users`
- SELECT Columns: `u1.name as user1, u2.name as user2, u1.join_date`
- JOIN Type: `SELF JOIN`
- Join Table: `users` *(Will be aliased automatically)*
- Table Alias: `u2`
- ON Condition: `users.join_date = u2.join_date AND users.user_id != u2.user_id`

**Expected SQL:**
```sql
SELECT users.name as user1, u2.name as user2, users.join_date
FROM users
INNER JOIN users AS u2 ON users.join_date = u2.join_date AND users.user_id != u2.user_id
```

**Result:** Pairs of users who joined on the same date

**Note:** In SELECT clause, use table name for first table and alias for second

---

## 7. EQUI JOIN

**Description:** A join that uses equality operator (=) in the ON condition.

**Use Case:** Match incidents with their reporters

**Query Builder Settings:**
- FROM Table: `incidents`
- SELECT Columns: `incidents.title, users.name as reporter`
- JOIN Type: `INNER JOIN`
- Join Table: `users`
- ON Condition: `incidents.reported_by = users.user_id` *(Uses = operator)*

**Expected SQL:**
```sql
SELECT incidents.title, users.name as reporter
FROM incidents
INNER JOIN users ON incidents.reported_by = users.user_id
```

**Note:** Most common type of join. Uses equality (=) operator.

---

## 8. NON-EQUI JOIN

**Description:** A join that uses operators other than equality (!=, <, >, <=, >=).

**Use Case:** Find vulnerabilities reported after incidents were detected

**Query Builder Settings:**
- FROM Table: `vulnerabilities`
- SELECT Columns: `vulnerabilities.title as vuln, incidents.title as incident`
- JOIN Type: `INNER JOIN`
- Join Table: `incidents`
- ON Condition: `vulnerabilities.report_date > incidents.detected_date` *(Uses > operator)*

**Expected SQL:**
```sql
SELECT vulnerabilities.title as vuln, incidents.title as incident
FROM vulnerabilities
INNER JOIN incidents ON vulnerabilities.report_date > incidents.detected_date
```

**Other Examples:**
- `table1.price < table2.price` (find cheaper items)
- `table1.priority >= table2.priority` (priority matching)
- `table1.date != table2.date` (different dates)

---

## Testing Each JOIN Type

### Test 1: INNER JOIN (Equi Join)
```
FROM: users
SELECT: users.name, users.email, roles.role_name
JOIN: INNER JOIN
Table: roles
ON: users.role_id = roles.role_id
```

### Test 2: LEFT OUTER JOIN
```
FROM: incidents
SELECT: incidents.title, users.name as reporter
JOIN: LEFT OUTER JOIN
Table: users
ON: incidents.reported_by = users.user_id
```

### Test 3: RIGHT OUTER JOIN
```
FROM: users
SELECT: users.name, roles.role_name
JOIN: RIGHT OUTER JOIN
Table: roles
ON: users.role_id = roles.role_id
```

### Test 4: CROSS JOIN
```
FROM: roles
SELECT: roles.role_name, assets.asset_name
JOIN: CROSS JOIN
Table: assets
ON: (leave empty)
```

### Test 5: NATURAL JOIN
```
FROM: users
SELECT: *
JOIN: NATURAL JOIN
Table: logs
ON: (leave empty)
```

### Test 6: SELF JOIN
```
FROM: users
SELECT: users.name as user1, u2.name as user2, users.join_date
JOIN: SELF JOIN
Table: users
Alias: u2
ON: users.join_date = u2.join_date AND users.user_id != u2.user_id
```

### Test 7: Non-Equi Join
```
FROM: vulnerabilities
SELECT: vulnerabilities.title, incidents.title
JOIN: INNER JOIN
Table: incidents
ON: vulnerabilities.report_date > incidents.detected_date
```

---

## Quick Reference Table

| JOIN Type | Syntax | Condition Required? | Use Case |
|-----------|--------|-------------------|----------|
| INNER | `INNER JOIN` | Yes (=) | Matching records only |
| LEFT OUTER | `LEFT OUTER JOIN` | Yes | All from left + matching right |
| RIGHT OUTER | `RIGHT OUTER JOIN` | Yes | All from right + matching left |
| CROSS | `CROSS JOIN` | No | All combinations |
| NATURAL | `NATURAL JOIN` | No | Auto-join on same names |
| SELF | Table AS alias | Yes | Compare within same table |
| EQUI | Any JOIN with = | Yes (=) | Equality condition |
| NON-EQUI | Any JOIN with !=,<,> | Yes (!=,<,>,<=,>=) | Non-equality condition |

---

## Common Mistakes to Avoid

1. **CROSS JOIN with Large Tables:** Can produce millions of rows (Cartesian product)
2. **NATURAL JOIN:** Only works if column names match exactly
3. **SELF JOIN:** Must use different aliases and avoid matching same row
4. **Missing ON Condition:** Required for INNER, LEFT, RIGHT, SELF joins
5. **Wrong Table Prefix:** In SELECT, use correct table name or alias

---

## Tips for Your Presentation

1. **Start Simple:** Begin with INNER JOIN (most common)
2. **Show Differences:** Demonstrate LEFT vs RIGHT with same tables
3. **Practical Examples:** Use real scenarios (users + roles, incidents + reporters)
4. **Show Results:** Point out NULL values in OUTER JOINs
5. **Explain Use Cases:** When would you use each type?

---

## Expected Results Count

With your sample data:
- **INNER JOIN users-roles:** ~15 rows (all users have roles)
- **LEFT OUTER JOIN incidents-users:** ~15 rows (all incidents)
- **RIGHT OUTER JOIN users-roles:** ~10 rows (all roles)
- **CROSS JOIN roles-assets:** 200 rows (10 × 20)
- **NATURAL JOIN users-logs:** ~30 rows (matching user_ids)
- **SELF JOIN users:** Variable (depends on same join dates)

Good luck with your demonstration! 🎓
