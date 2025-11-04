<?php
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$result = null;
$error = null;
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'];

    try {
        if ($operation === 'combined_query') {
            $result = handleCombinedQuery($conn, $_POST);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Add the generated query to error for debugging
        if (!empty($query)) {
            $error .= " | Generated SQL: " . $query;
        }
    }
}

function handleCombinedQuery($conn, $data) {
    global $query;

    // Start building the query
    $selectClause = !empty($data['select_columns']) ? $data['select_columns'] : '*';
    $fromTable = $data['from_table'];

    $query = "SELECT {$selectClause} FROM {$fromTable}";
    $params = [];

    // Add JOIN if specified
    if (!empty($data['join_table']) && !empty($data['join_type'])) {
        $joinType = $data['join_type'];
        $joinTable = $data['join_table'];
        $joinCondition = isset($data['join_condition']) ? trim($data['join_condition']) : '';
        $joinAlias = isset($data['join_alias']) ? trim($data['join_alias']) : '';

        switch ($joinType) {
            case 'INNER':
                if (empty($joinCondition)) {
                    throw new Exception("INNER JOIN requires an ON condition");
                }
                $query .= " INNER JOIN {$joinTable}";
                $query .= " ON {$joinCondition}";
                break;

            case 'LEFT':
                if (empty($joinCondition)) {
                    throw new Exception("LEFT JOIN requires an ON condition");
                }
                $query .= " LEFT JOIN {$joinTable}";
                $query .= " ON {$joinCondition}";
                break;

            case 'RIGHT':
                if (empty($joinCondition)) {
                    throw new Exception("RIGHT JOIN requires an ON condition");
                }
                $query .= " RIGHT JOIN {$joinTable}";
                $query .= " ON {$joinCondition}";
                break;

            case 'CROSS':
                // CROSS JOIN doesn't use ON condition
                $query .= " CROSS JOIN {$joinTable}";
                break;

            case 'NATURAL':
                // NATURAL JOIN automatically joins on columns with same names
                $query .= " NATURAL JOIN {$joinTable}";
                break;

            case 'SELF':
                // SELF JOIN - join table to itself with alias
                if (empty($joinCondition)) {
                    throw new Exception("SELF JOIN requires an ON condition");
                }
                $aliasName = !empty($joinAlias) ? $joinAlias : 't2';
                $query .= " INNER JOIN {$fromTable} AS {$aliasName}";
                $query .= " ON {$joinCondition}";
                break;

            default:
                if (!empty($joinCondition)) {
                    $query .= " {$joinType} JOIN {$joinTable} ON {$joinCondition}";
                } else {
                    $query .= " {$joinType} JOIN {$joinTable}";
                }
        }
    }

    // Add WHERE conditions
    if (!empty($data['where_conditions'])) {
        $whereConditions = [];
        foreach ($data['where_conditions'] as $index => $condition) {
            if (!empty($condition['column']) && !empty($condition['operator']) && isset($condition['value'])) {
                $column = $condition['column'];
                $operator = $condition['operator'];
                $value = $condition['value'];
                $paramName = "where_{$index}";

                if ($operator === 'LIKE' || $operator === 'NOT LIKE') {
                    $whereConditions[] = "{$column} {$operator} :{$paramName}";
                    $params[$paramName] = '%' . $value . '%';
                } elseif ($operator === 'IN') {
                    $whereConditions[] = "{$column} IN ({$value})";
                } elseif ($operator === 'BETWEEN') {
                    $values = explode(',', $value);
                    if (count($values) == 2) {
                        $whereConditions[] = "{$column} BETWEEN :{$paramName}_1 AND :{$paramName}_2";
                        $params["{$paramName}_1"] = trim($values[0]);
                        $params["{$paramName}_2"] = trim($values[1]);
                    }
                } else {
                    $whereConditions[] = "{$column} {$operator} :{$paramName}";
                    $params[$paramName] = $value;
                }
            }
        }

        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
    }

    // Add GROUP BY
    if (!empty($data['group_by_columns'])) {
        $query .= " GROUP BY " . $data['group_by_columns'];
    }

    // Add HAVING
    if (!empty($data['having_condition'])) {
        $query .= " HAVING " . $data['having_condition'];
    }

    // Add ORDER BY
    if (!empty($data['order_by_column'])) {
        $orderDirection = !empty($data['order_direction']) ? $data['order_direction'] : 'ASC';
        $query .= " ORDER BY " . $data['order_by_column'] . " " . $orderDirection;
    }

    // Add LIMIT
    if (!empty($data['limit'])) {
        $query .= " LIMIT " . intval($data['limit']);
    }

    $stmt = $conn->prepare($query);

    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }

    $stmt->execute();

    // Create a display-friendly query by replacing placeholders with actual values
    $displayQuery = $query;
    foreach ($params as $key => $value) {
        $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        $displayQuery = str_replace(':' . $key, $escapedValue, $displayQuery);
    }

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $displayQuery
    ];
}

function  getTableFields($table) {
    $fields = [
        'roles' => ['role_id', 'role_name'],
        'users' => ['user_id', 'name', 'email', 'role_id', 'join_date'],
        'incidents' => ['incident_id', 'title', 'type', 'status', 'detected_date', 'resolved_date', 'reported_by'],
        'assets' => ['asset_id', 'asset_name', 'asset_type', 'criticality'],
        'vulnerabilities' => ['vuln_id', 'title', 'severity', 'report_date', 'reported_by'],
        'logs' => ['log_id', 'user_id', 'activity', 'log_time']
    ];

    return $fields[$table] ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Builder - Constraints Operations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            font-size: 14px;
            background-color: #f5f7fa;
            overflow-x: hidden;
            height: auto;
            min-height: 100%;
        }
        .query-builder-section {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .result-section {
            width: 100%;
        }
        .query-display {
            background-color: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .where-condition-row {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
        }
        .btn-add-condition {
            background: #667eea;
            color: white;
            border: none;
        }
        .btn-add-condition:hover {
            background: #764ba2;
            color: white;
        }
        .btn-run-query {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-run-query:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }
        .btn-reset {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h4 class="mb-4"><i class="fas fa-search-plus"></i> Advanced Query Builder</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" id="queryBuilderForm">
            <input type="hidden" name="operation" value="combined_query">

            <!-- FROM Table Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-database"></i> FROM Table
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Main Table</label>
                        <select name="from_table" id="fromTable" class="form-select" required>
                            <option value="">-- Select Table --</option>
                            <option value="roles">Roles</option>
                            <option value="users">Users</option>
                            <option value="incidents">Incidents</option>
                            <option value="assets">Assets</option>
                            <option value="vulnerabilities">Vulnerabilities</option>
                            <option value="logs">Logs</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Select Columns (use * for all or comma-separated)</label>
                        <input type="text" name="select_columns" class="form-control" placeholder="e.g., name, email or *" value="*">
                    </div>
                </div>
            </div>

            <!-- JOIN Tables Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-link"></i> JOIN Operations
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">JOIN Type</label>
                        <select name="join_type" id="joinType" class="form-select">
                            <option value="">-- No JOIN --</option>
                            <option value="INNER">INNER JOIN</option>
                            <option value="LEFT">LEFT OUTER JOIN</option>
                            <option value="RIGHT">RIGHT OUTER JOIN</option>
                            <option value="CROSS">CROSS JOIN</option>
                            <option value="NATURAL">NATURAL JOIN</option>
                            <option value="SELF">SELF JOIN</option>
                        </select>
                        <small class="text-muted">Select join type</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Join Table</label>
                        <select name="join_table" id="joinTable" class="form-select">
                            <option value="">-- Select Table --</option>
                            <option value="roles">Roles</option>
                            <option value="users">Users</option>
                            <option value="incidents">Incidents</option>
                            <option value="assets">Assets</option>
                            <option value="vulnerabilities">Vulnerabilities</option>
                            <option value="logs">Logs</option>
                        </select>
                        <small class="text-muted">Table to join with</small>
                    </div>
                    <div class="col-md-2" id="joinAliasField" style="display:none;">
                        <label class="form-label">Table Alias</label>
                        <input type="text" name="join_alias" class="form-control" placeholder="t2">
                        <small class="text-muted">For SELF JOIN</small>
                    </div>
                    <div class="col-md-6" id="joinConditionField">
                        <label class="form-label">ON Condition <span class="text-danger">*</span></label>
                        <input type="text" name="join_condition" id="joinConditionInput" class="form-control" placeholder="users.role_id = roles.role_id" required>
                        <small class="text-muted">
                            <strong>Equi Join:</strong> Use = (e.g., table1.id = table2.id)<br>
                            <strong>Non-Equi Join:</strong> Use !=, &lt;, &gt;, &lt;=, &gt;= (e.g., table1.value &gt; table2.value)
                        </small>
                    </div>
                </div>

                <!-- Join Type Explanations -->
                <div class="mt-3 p-2 bg-light rounded" id="joinExplanation">
                    <small>
                        <strong>Join Types:</strong><br>
                        • <strong>INNER JOIN:</strong> Returns matching records from both tables<br>
                        • <strong>LEFT OUTER JOIN:</strong> Returns all from left table + matching from right<br>
                        • <strong>RIGHT OUTER JOIN:</strong> Returns all from right table + matching from left<br>
                        • <strong>CROSS JOIN:</strong> Returns Cartesian product (all combinations)<br>
                        • <strong>NATURAL JOIN:</strong> Auto-joins on common column names<br>
                        • <strong>SELF JOIN:</strong> Joins table to itself using alias
                    </small>
                </div>
            </div>

            <!-- WHERE Conditions Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-filter"></i> WHERE Conditions
                </div>
                <div id="whereConditionsContainer">
                    <div class="where-condition-row">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Column</label>
                                <select name="where_conditions[0][column]" class="form-select column-select">
                                    <option value="">-- Select Column --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Operator</label>
                                <select name="where_conditions[0][operator]" class="form-select">
                                    <option value="=">Equals (=)</option>
                                    <option value="!=">Not Equals (!=)</option>
                                    <option value=">">Greater than (>)</option>
                                    <option value="<">Less than (<)</option>
                                    <option value=">=">Greater or Equal (>=)</option>
                                    <option value="<=">Less or Equal (<=)</option>
                                    <option value="LIKE">Contains (LIKE %...%)</option>
                                    <option value="NOT LIKE">Not Contains (NOT LIKE)</option>
                                    <option value="IN">In List (comma separated)</option>
                                    <option value="BETWEEN">Between (value1,value2)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Value</label>
                                <input type="text" name="where_conditions[0][value]" class="form-control" placeholder="Value">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-success btn-sm" onclick="addWhereCondition()">
                                    <i class="fas fa-plus"></i> Add Condition
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GROUP BY Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-object-group"></i> GROUP BY
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Group By Columns (comma-separated)</label>
                        <input type="text" name="group_by_columns" class="form-control" placeholder="e.g., role_id, status">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">HAVING Condition (optional)</label>
                        <input type="text" name="having_condition" class="form-control" placeholder="e.g., COUNT(*) > 5">
                    </div>
                </div>
            </div>

            <!-- ORDER BY Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-sort"></i> ORDER BY
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Order By Column</label>
                        <select name="order_by_column" class="form-select order-column-select">
                            <option value="">-- No Ordering --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Direction</label>
                        <select name="order_direction" class="form-select">
                            <option value="ASC">Ascending (A-Z, 1-9)</option>
                            <option value="DESC">Descending (Z-A, 9-1)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- LIMIT Section -->
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-list-ol"></i> LIMIT
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Limit Results</label>
                        <input type="number" name="limit" class="form-control" placeholder="e.g., 100" value="100">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4 mb-4">
                <button type="submit" class="btn btn-run-query me-3">
                    <i class="fas fa-play-circle"></i> Run Query
                </button>
                <button type="button" class="btn btn-reset" onclick="resetForm();">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>

        <!-- Results Section -->
        <?php if ($result): ?>
            <div class="query-builder-section">
                <div class="section-header">
                    <i class="fas fa-code"></i> Generated SQL Query
                </div>
                <div class="query-display"><?= htmlspecialchars($result['query']) ?></div>

                <div class="section-header">
                    <i class="fas fa-table"></i> Query Results (<?= count($result['data']) ?> rows)
                </div>
                <div class="result-section">
                    <table class="table table-striped table-hover">
                        <?php if (!empty($result['data'])): ?>
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <?php foreach (array_keys($result['data'][0]) as $column): ?>
                                        <th><?= htmlspecialchars($column) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['data'] as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?= htmlspecialchars($value ?? 'NULL') ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No results found</p>
                                    </td>
                                </tr>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tableFields = {
            'roles': ['role_id', 'role_name'],
            'users': ['user_id', 'name', 'email', 'role_id', 'join_date'],
            'incidents': ['incident_id', 'title', 'type', 'status', 'detected_date', 'resolved_date', 'reported_by'],
            'assets': ['asset_id', 'asset_name', 'asset_type', 'criticality'],
            'vulnerabilities': ['vuln_id', 'title', 'severity', 'report_date', 'reported_by'],
            'logs': ['log_id', 'user_id', 'activity', 'log_time']
        };

        let whereConditionCount = 1;

        // Handle JOIN type changes
        document.getElementById('joinType').addEventListener('change', function() {
            const joinType = this.value;
            const joinAliasField = document.getElementById('joinAliasField');
            const joinConditionField = document.getElementById('joinConditionField');
            const joinConditionInput = document.getElementById('joinConditionInput');

            // Show/hide fields based on join type
            if (joinType === 'SELF') {
                joinAliasField.style.display = 'block';
                joinConditionField.style.display = 'block';
                joinConditionInput.required = true;
            } else if (joinType === 'NATURAL' || joinType === 'CROSS') {
                joinAliasField.style.display = 'none';
                joinConditionField.style.display = 'none';
                joinConditionInput.value = ''; // Clear the condition
                joinConditionInput.required = false;
            } else if (joinType === '') {
                joinAliasField.style.display = 'none';
                joinConditionField.style.display = 'none';
                joinConditionInput.required = false;
            } else {
                joinAliasField.style.display = 'none';
                joinConditionField.style.display = 'block';
                joinConditionInput.required = true;
            }
        });

        // Update field dropdowns when table is selected
        document.getElementById('fromTable').addEventListener('change', function() {
            const table = this.value;
            updateFieldSelects(table);
        });

        function updateFieldSelects(table) {
            const columnSelects = document.querySelectorAll('.column-select, .order-column-select');

            columnSelects.forEach(select => {
                select.innerHTML = '<option value="">-- Select Column --</option>';
                if (table && tableFields[table]) {
                    tableFields[table].forEach(field => {
                        const option = document.createElement('option');
                        option.value = field;
                        option.textContent = field;
                        select.appendChild(option);
                    });
                }
            });
        }

        function addWhereCondition() {
            const container = document.getElementById('whereConditionsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'where-condition-row';
            newRow.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Column</label>
                        <select name="where_conditions[${whereConditionCount}][column]" class="form-select column-select">
                            <option value="">-- Select Column --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Operator</label>
                        <select name="where_conditions[${whereConditionCount}][operator]" class="form-select">
                            <option value="=">Equals (=)</option>
                            <option value="!=">Not Equals (!=)</option>
                            <option value=">">Greater than (>)</option>
                            <option value="<">Less than (<)</option>
                            <option value=">=">Greater or Equal (>=)</option>
                            <option value="<=">Less or Equal (<=)</option>
                            <option value="LIKE">Contains (LIKE %...%)</option>
                            <option value="NOT LIKE">Not Contains (NOT LIKE)</option>
                            <option value="IN">In List (comma separated)</option>
                            <option value="BETWEEN">Between (value1,value2)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Value</label>
                        <input type="text" name="where_conditions[${whereConditionCount}][value]" class="form-control" placeholder="Value">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.where-condition-row').remove()">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
            whereConditionCount++;

            // Update the new column select with current table fields
            const currentTable = document.getElementById('fromTable').value;
            if (currentTable) {
                updateFieldSelects(currentTable);
            }
        }

        function resetForm() {
            // Reset the form
            document.getElementById('queryBuilderForm').reset();

            // Reset WHERE conditions count
            whereConditionCount = 1;

            // Remove all extra WHERE condition rows except the first one
            const container = document.getElementById('whereConditionsContainer');
            const whereRows = container.querySelectorAll('.where-condition-row');
            whereRows.forEach((row, index) => {
                if (index > 0) {
                    row.remove();
                }
            });

            // Clear the first WHERE condition row
            const firstRow = container.querySelector('.where-condition-row');
            if (firstRow) {
                firstRow.querySelectorAll('input').forEach(input => input.value = '');
                firstRow.querySelectorAll('select').forEach(select => {
                    if (select.classList.contains('column-select')) {
                        select.innerHTML = '<option value="">-- Select Column --</option>';
                    } else {
                        select.selectedIndex = 0;
                    }
                });
            }

            // Clear all column select dropdowns
            document.querySelectorAll('.column-select, .order-column-select').forEach(select => {
                select.innerHTML = '<option value="">-- Select Column --</option>';
            });

            // Reload the page to clear PHP-rendered results
            window.location.href = window.location.pathname;
        }
    </script>
</body>
</html>