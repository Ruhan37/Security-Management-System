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
        switch ($operation) {
            case 'simple_select':
                $result = handleSimpleSelect($conn, $_POST);
                break;
            case 'pattern_matching':
                $result = handlePatternMatching($conn, $_POST);
                break;
            case 'aggregate_functions':
                $result = handleAggregateFunctions($conn, $_POST);
                break;
            case 'group_by_having':
                $result = handleGroupByHaving($conn, $_POST);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleSimpleSelect($conn, $data) {
    global $query;
    $table = $data['table'];
    $fields = $data['fields'] ?? ['*'];

    if (is_array($fields)) {
        $fieldsList = implode(', ', $fields);
    } else {
        $fieldsList = $fields === '*' ? '*' : $fields;
    }

    $query = "SELECT {$fieldsList} FROM {$table}";

    if (!empty($data['where_condition'])) {
        $query .= " WHERE " . $data['where_condition'];
    }

    if (!empty($data['order_by'])) {
        $query .= " ORDER BY " . $data['order_by'];
        if (!empty($data['order_direction'])) {
            $query .= " " . $data['order_direction'];
        }
    }

    if (!empty($data['limit'])) {
        $query .= " LIMIT " . intval($data['limit']);
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handlePatternMatching($conn, $data) {
    global $query;
    $table = $data['table'];
    $field = $data['field'];
    $pattern_type = $data['pattern_type'];
    $pattern_value = $data['pattern_value'];

    $conditions = [];

    switch ($pattern_type) {
        case 'starts_with':
            $conditions[] = "{$field} LIKE '{$pattern_value}%'";
            break;
        case 'ends_with':
            $conditions[] = "{$field} LIKE '%{$pattern_value}'";
            break;
        case 'contains':
            $conditions[] = "{$field} LIKE '%{$pattern_value}%'";
            break;
        case 'regex':
            $conditions[] = "{$field} REGEXP '{$pattern_value}'";
            break;
        case 'not_contains':
            $conditions[] = "{$field} NOT LIKE '%{$pattern_value}%'";
            break;
        case 'exact_length':
            $conditions[] = "CHAR_LENGTH({$field}) = " . intval($pattern_value);
            break;
    }

    $query = "SELECT * FROM {$table}";
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    if (!empty($data['limit'])) {
        $query .= " LIMIT " . intval($data['limit']);
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleAggregateFunctions($conn, $data) {
    global $query;
    $table = $data['table'];
    $function = $data['function'];
    $field = $data['field'];

    $functions = [];
    if (is_array($function)) {
        foreach ($function as $func) {
            if ($func === 'COUNT') {
                $functions[] = "COUNT(*) as count_all";
            } else {
                $functions[] = "{$func}({$field}) as " . strtolower($func) . "_result";
            }
        }
    } else {
        if ($function === 'COUNT') {
            $functions[] = "COUNT(*) as count_all";
        } else {
            $functions[] = "{$function}({$field}) as " . strtolower($function) . "_result";
        }
    }

    $query = "SELECT " . implode(', ', $functions) . " FROM {$table}";

    if (!empty($data['where_condition'])) {
        $query .= " WHERE " . $data['where_condition'];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleGroupByHaving($conn, $data) {
    global $query;
    $table = $data['table'];
    $group_fields = $data['group_fields'];
    $aggregate_function = $data['aggregate_function'];
    $aggregate_field = $data['aggregate_field'];

    $groupList = is_array($group_fields) ? implode(', ', $group_fields) : $group_fields;

    $query = "SELECT {$groupList}, {$aggregate_function}({$aggregate_field}) as result_value
              FROM {$table}
              GROUP BY {$groupList}";

    if (!empty($data['having_condition'])) {
        $query .= " HAVING " . $data['having_condition'];
    }

    if (!empty($data['order_by_result'])) {
        $query .= " ORDER BY result_value " . $data['order_direction'];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function getTableFields($table) {
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
    <title>SELECT Commands & Aggregate Functions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .operation-section { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .result-section { max-height: 300px; overflow-y: auto; }
        .query-display { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h4><i class="fas fa-chart-bar"></i> SELECT Commands & Aggregate Functions</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Simple SELECT Section -->
        <div class="operation-section">
            <h5><i class="fas fa-search"></i> Simple SELECT Operations</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="simple_select">
                <div class="col-md-2">
                    <select name="table" class="form-select form-select-sm" required>
                        <option value="">Select Table</option>
                        <option value="roles">Roles</option>
                        <option value="users">Users</option>
                        <option value="incidents">Incidents</option>
                        <option value="assets">Assets</option>
                        <option value="vulnerabilities">Vulnerabilities</option>
                        <option value="logs">Logs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="fields" class="form-control form-control-sm" placeholder="Fields (* for all)" value="*">
                </div>
                <div class="col-md-2">
                    <input type="text" name="where_condition" class="form-control form-control-sm" placeholder="WHERE condition">
                </div>
                <div class="col-md-2">
                    <input type="text" name="order_by" class="form-control form-control-sm" placeholder="ORDER BY field">
                </div>
                <div class="col-md-1">
                    <select name="order_direction" class="form-select form-select-sm">
                        <option value="ASC">ASC</option>
                        <option value="DESC">DESC</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="limit" class="form-control form-control-sm" placeholder="Limit">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm">Execute SELECT</button>
                </div>
            </form>
        </div>

        <!-- Pattern Matching Section -->
        <div class="operation-section">
            <h5><i class="fas fa-search-plus"></i> Pattern Matching</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="pattern_matching">
                <div class="col-md-2">
                    <select name="table" class="form-select form-select-sm" required>
                        <option value="">Select Table</option>
                        <option value="roles">Roles</option>
                        <option value="users">Users</option>
                        <option value="incidents">Incidents</option>
                        <option value="assets">Assets</option>
                        <option value="vulnerabilities">Vulnerabilities</option>
                        <option value="logs">Logs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="field" class="form-select form-select-sm" required>
                        <option value="">Select Field</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="pattern_type" class="form-select form-select-sm" required>
                        <option value="starts_with">Starts With</option>
                        <option value="ends_with">Ends With</option>
                        <option value="contains">Contains</option>
                        <option value="not_contains">Not Contains</option>
                        <option value="regex">Regex Pattern</option>
                        <option value="exact_length">Exact Length</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="pattern_value" class="form-control form-control-sm" placeholder="Pattern/Value" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="limit" class="form-control form-control-sm" placeholder="Limit">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-sm">Execute Pattern</button>
                </div>
            </form>
        </div>

        <!-- Aggregate Functions Section -->
        <div class="operation-section">
            <h5><i class="fas fa-calculator"></i> Aggregate Functions</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="aggregate_functions">
                <div class="col-md-2">
                    <select name="table" class="form-select form-select-sm" required>
                        <option value="">Select Table</option>
                        <option value="roles">Roles</option>
                        <option value="users">Users</option>
                        <option value="incidents">Incidents</option>
                        <option value="assets">Assets</option>
                        <option value="vulnerabilities">Vulnerabilities</option>
                        <option value="logs">Logs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="function[]" class="form-select form-select-sm" multiple required>
                        <option value="COUNT">COUNT</option>
                        <option value="SUM">SUM</option>
                        <option value="AVG">AVG</option>
                        <option value="MIN">MIN</option>
                        <option value="MAX">MAX</option>
                    </select>
                    <small class="text-muted">Hold Ctrl for multiple</small>
                </div>
                <div class="col-md-2">
                    <select name="field" class="form-select form-select-sm">
                        <option value="">Select Field</option>
                    </select>
                    <small class="text-muted">For COUNT use any</small>
                </div>
                <div class="col-md-3">
                    <input type="text" name="where_condition" class="form-control form-control-sm" placeholder="WHERE condition (optional)">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning btn-sm">Execute Aggregates</button>
                </div>
            </form>
        </div>

        <!-- GROUP BY with HAVING Section -->
        <div class="operation-section">
            <h5><i class="fas fa-layer-group"></i> GROUP BY with HAVING</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="group_by_having">
                <div class="col-md-2">
                    <select name="table" class="form-select form-select-sm" required>
                        <option value="">Select Table</option>
                        <option value="users">Users</option>
                        <option value="incidents">Incidents</option>
                        <option value="assets">Assets</option>
                        <option value="vulnerabilities">Vulnerabilities</option>
                        <option value="logs">Logs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="group_fields" class="form-select form-select-sm" required>
                        <option value="">Group By Field</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="aggregate_function" class="form-select form-select-sm" required>
                        <option value="COUNT">COUNT</option>
                        <option value="SUM">SUM</option>
                        <option value="AVG">AVG</option>
                        <option value="MIN">MIN</option>
                        <option value="MAX">MAX</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="aggregate_field" class="form-select form-select-sm" required>
                        <option value="">Agg Field</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="having_condition" class="form-control form-control-sm" placeholder="HAVING condition">
                </div>
                <div class="col-md-1">
                    <select name="order_direction" class="form-select form-select-sm">
                        <option value="">No Order</option>
                        <option value="ASC">ASC</option>
                        <option value="DESC">DESC</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="checkbox" name="order_by_result" class="form-check-input mt-2" title="Order by result">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">Execute Group</button>
                </div>
            </form>
        </div>

        <!-- Pre-defined Queries Section -->
        <div class="operation-section">
            <h5><i class="fas fa-magic"></i> Common Aggregate Queries</h5>
            <div class="row g-2">
                <div class="col-md-3">
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="executeCommonQuery('user_count')">
                        Count Users by Role
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-success btn-sm w-100" onclick="executeCommonQuery('incident_stats')">
                        Incident Statistics
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-warning btn-sm w-100" onclick="executeCommonQuery('asset_criticality')">
                        Asset Criticality Count
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-info btn-sm w-100" onclick="executeCommonQuery('vulnerability_severity')">
                        Vulnerability by Severity
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <?php if ($result): ?>
            <div class="mt-4">
                <h5><i class="fas fa-code"></i> Generated Query</h5>
                <div class="query-display mb-3"><?= htmlspecialchars($query) ?></div>

                <h5><i class="fas fa-table"></i> Results (<?= count($result['data']) ?> rows)</h5>
                <div class="result-section">
                    <table class="table table-striped table-sm">
                        <?php if (!empty($result['data'])): ?>
                            <thead class="table-dark">
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
                                            <td><?= htmlspecialchars($value) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                            <tr><td colspan="100%" class="text-center">No results found</td></tr>
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

        // Update field dropdowns when table is selected
        document.querySelectorAll('select[name="table"]').forEach(select => {
            select.addEventListener('change', function() {
                const table = this.value;
                const form = this.closest('form');
                const fieldSelects = form.querySelectorAll('select[name="field"], select[name="group_fields"], select[name="aggregate_field"]');

                fieldSelects.forEach(fieldSelect => {
                    fieldSelect.innerHTML = '<option value="">Select Field</option>';
                    if (table && tableFields[table]) {
                        tableFields[table].forEach(field => {
                            const option = document.createElement('option');
                            option.value = field;
                            option.textContent = field;
                            fieldSelect.appendChild(option);
                        });
                    }
                });
            });
        });

        function executeCommonQuery(type) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const operationInput = document.createElement('input');
            operationInput.name = 'operation';

            switch(type) {
                case 'user_count':
                    operationInput.value = 'group_by_having';
                    form.appendChild(createInput('table', 'users'));
                    form.appendChild(createInput('group_fields', 'role_id'));
                    form.appendChild(createInput('aggregate_function', 'COUNT'));
                    form.appendChild(createInput('aggregate_field', 'user_id'));
                    break;
                case 'incident_stats':
                    operationInput.value = 'group_by_having';
                    form.appendChild(createInput('table', 'incidents'));
                    form.appendChild(createInput('group_fields', 'status'));
                    form.appendChild(createInput('aggregate_function', 'COUNT'));
                    form.appendChild(createInput('aggregate_field', 'incident_id'));
                    break;
                case 'asset_criticality':
                    operationInput.value = 'group_by_having';
                    form.appendChild(createInput('table', 'assets'));
                    form.appendChild(createInput('group_fields', 'criticality'));
                    form.appendChild(createInput('aggregate_function', 'COUNT'));
                    form.appendChild(createInput('aggregate_field', 'asset_id'));
                    break;
                case 'vulnerability_severity':
                    operationInput.value = 'group_by_having';
                    form.appendChild(createInput('table', 'vulnerabilities'));
                    form.appendChild(createInput('group_fields', 'severity'));
                    form.appendChild(createInput('aggregate_function', 'COUNT'));
                    form.appendChild(createInput('aggregate_field', 'vuln_id'));
                    break;
            }

            form.appendChild(operationInput);
            document.body.appendChild(form);
            form.submit();
        }

        function createInput(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            return input;
        }
    </script>
</body>
</html>