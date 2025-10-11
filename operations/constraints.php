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
            case 'where':
                $result = handleWhereClause($conn, $_POST);
                break;
            case 'order_by':
                $result = handleOrderBy($conn, $_POST);
                break;
            case 'group_by':
                $result = handleGroupBy($conn, $_POST);
                break;
            case 'having':
                $result = handleHaving($conn, $_POST);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleWhereClause($conn, $data) {
    global $query;
    $table = $data['table'];
    $field = $data['field'];
    $operator = $data['operator'];
    $value = $data['value'];

    $operators = [
        '=' => '=', '!=' => '!=', '>' => '>', '<' => '<',
        '>=' => '>=', '<=' => '<=', 'LIKE' => 'LIKE', 'NOT LIKE' => 'NOT LIKE'
    ];

    if ($operator === 'LIKE' || $operator === 'NOT LIKE') {
        $value = '%' . $value . '%';
    }

    $query = "SELECT * FROM {$table} WHERE {$field} {$operators[$operator]} :value";

    if (!empty($data['limit'])) {
        $query .= " LIMIT " . intval($data['limit']);
    }

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':value', $value);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleOrderBy($conn, $data) {
    global $query;
    $table = $data['table'];
    $orderField = $data['order_field'];
    $orderDirection = $data['order_direction'];

    $query = "SELECT * FROM {$table} ORDER BY {$orderField} {$orderDirection}";

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

function handleGroupBy($conn, $data) {
    global $query;
    $table = $data['table'];
    $groupField = $data['group_field'];
    $aggregateFunction = $data['aggregate_function'];
    $aggregateField = $data['aggregate_field'];

    $query = "SELECT {$groupField}, {$aggregateFunction}({$aggregateField}) as result
              FROM {$table}
              GROUP BY {$groupField}";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleHaving($conn, $data) {
    global $query;
    $table = $data['table'];
    $groupField = $data['group_field'];
    $aggregateFunction = $data['aggregate_function'];
    $aggregateField = $data['aggregate_field'];
    $havingOperator = $data['having_operator'];
    $havingValue = $data['having_value'];

    $query = "SELECT {$groupField}, {$aggregateFunction}({$aggregateField}) as result
              FROM {$table}
              GROUP BY {$groupField}
              HAVING {$aggregateFunction}({$aggregateField}) {$havingOperator} :having_value";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':having_value', $havingValue);
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
    <title>Constraints Operations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .operation-section { border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 15px; }
        .result-section { max-height: 300px; overflow-y: auto; }
        .query-display { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h4><i class="fas fa-filter"></i> Constraints Operations</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- WHERE Clause Section -->
        <div class="operation-section">
            <h5><i class="fas fa-search"></i> WHERE Clause</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="where">
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
                    <select name="operator" class="form-select form-select-sm" required>
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value=">=">>=</option>
                        <option value="<="><=</option>
                        <option value="LIKE">LIKE</option>
                        <option value="NOT LIKE">NOT LIKE</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="value" class="form-control form-control-sm" placeholder="Value" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="limit" class="form-control form-control-sm" placeholder="Limit (optional)">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm">Execute</button>
                </div>
            </form>
        </div>

        <!-- ORDER BY Section -->
        <div class="operation-section">
            <h5><i class="fas fa-sort"></i> ORDER BY</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="order_by">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <select name="order_field" class="form-select form-select-sm" required>
                        <option value="">Select Field</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="order_direction" class="form-select form-select-sm" required>
                        <option value="ASC">ASC</option>
                        <option value="DESC">DESC</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="limit" class="form-control form-control-sm" placeholder="Limit (optional)">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-sm">Execute</button>
                </div>
            </form>
        </div>

        <!-- GROUP BY Section -->
        <div class="operation-section">
            <h5><i class="fas fa-layer-group"></i> GROUP BY</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="group_by">
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
                    <select name="group_field" class="form-select form-select-sm" required>
                        <option value="">Group By Field</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="aggregate_function" class="form-select form-select-sm" required>
                        <option value="COUNT">COUNT</option>
                        <option value="SUM">SUM</option>
                        <option value="AVG">AVG</option>
                        <option value="MIN">MIN</option>
                        <option value="MAX">MAX</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="aggregate_field" class="form-select form-select-sm" required>
                        <option value="">Aggregate Field</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning btn-sm">Execute</button>
                </div>
            </form>
        </div>

        <!-- HAVING Section -->
        <div class="operation-section">
            <h5><i class="fas fa-filter"></i> HAVING Clause</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="having">
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
                <div class="col-md-1">
                    <select name="group_field" class="form-select form-select-sm" required>
                        <option value="">Group Field</option>
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
                <div class="col-md-1">
                    <select name="having_operator" class="form-select form-select-sm" required>
                        <option value=">">&gt;</option>
                        <option value="<">&lt;</option>
                        <option value=">=">&gt;=</option>
                        <option value="<=">&lt;=</option>
                        <option value="=">=</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="having_value" class="form-control form-control-sm" placeholder="Value" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">Execute</button>
                </div>
            </form>
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
                const fieldSelects = form.querySelectorAll('select[name="field"], select[name="order_field"], select[name="group_field"], select[name="aggregate_field"]');

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
    </script>
</body>
</html>