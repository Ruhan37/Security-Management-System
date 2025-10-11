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
            case 'subquery':
                $result = handleSubquery($conn, $_POST);
                break;
            case 'union':
                $result = handleUnion($conn, $_POST);
                break;
            case 'intersect':
                $result = handleIntersect($conn, $_POST);
                break;
            case 'except':
                $result = handleExcept($conn, $_POST);
                break;
            case 'create_view':
                $result = handleCreateView($conn, $_POST);
                break;
            case 'view_operations':
                $result = handleViewOperations($conn, $_POST);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleSubquery($conn, $data) {
    global $query;
    $subquery_type = $data['subquery_type'];

    switch ($subquery_type) {
        case 'exists':
            $query = "SELECT u.name, u.email
                      FROM users u
                      WHERE EXISTS (
                          SELECT 1 FROM incidents i
                          WHERE i.reported_by = u.user_id
                      )";
            break;

        case 'in':
            $query = "SELECT * FROM users
                      WHERE role_id IN (
                          SELECT role_id FROM roles
                          WHERE role_name IN ('Administrator', 'Security Analyst')
                      )";
            break;

        case 'not_in':
            $query = "SELECT * FROM assets
                      WHERE asset_id NOT IN (
                          SELECT DISTINCT asset_id FROM incident_assets
                      )";
            break;

        case 'all':
            $query = "SELECT * FROM vulnerabilities
                      WHERE report_date >= ALL (
                          SELECT report_date FROM vulnerabilities
                          WHERE severity = 'Critical'
                      )";
            break;

        case 'any':
            $query = "SELECT * FROM incidents
                      WHERE detected_date > ANY (
                          SELECT detected_date FROM incidents
                          WHERE status = 'Resolved'
                      )";
            break;

        case 'correlated':
            $query = "SELECT u1.name, u1.email, u1.join_date
                      FROM users u1
                      WHERE u1.join_date = (
                          SELECT MIN(u2.join_date)
                          FROM users u2
                          WHERE u2.role_id = u1.role_id
                      )";
            break;

        case 'scalar':
            $query = "SELECT name, email,
                     (SELECT COUNT(*) FROM incidents WHERE reported_by = users.user_id) as incident_count
                      FROM users";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleUnion($conn, $data) {
    global $query;
    $union_type = $data['union_type'];

    switch ($union_type) {
        case 'basic':
            $query = "SELECT 'User' as type, name as entity_name, email as contact
                      FROM users
                      UNION
                      SELECT 'Asset' as type, asset_name as entity_name, asset_type as contact
                      FROM assets";
            break;

        case 'all':
            $query = "SELECT title as item, 'Incident' as category FROM incidents
                      UNION ALL
                      SELECT title as item, 'Vulnerability' as category FROM vulnerabilities";
            break;

        case 'with_order':
            $query = "SELECT name as entity, join_date as date_field FROM users
                      UNION
                      SELECT asset_name as entity, NULL as date_field FROM assets
                      ORDER BY entity";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleIntersect($conn, $data) {
    global $query;
    // MySQL doesn't support INTERSECT directly, so we simulate it
    $query = "SELECT u.user_id, u.name
              FROM users u
              WHERE EXISTS (
                  SELECT 1 FROM incidents i WHERE i.reported_by = u.user_id
              )
              AND EXISTS (
                  SELECT 1 FROM vulnerabilities v WHERE v.reported_by = u.user_id
              )";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Simulated INTERSECT: Users who reported both incidents and vulnerabilities"
    ];
}

function handleExcept($conn, $data) {
    global $query;
    // MySQL doesn't support EXCEPT directly, so we simulate it
    $query = "SELECT u.user_id, u.name
              FROM users u
              WHERE EXISTS (
                  SELECT 1 FROM incidents i WHERE i.reported_by = u.user_id
              )
              AND NOT EXISTS (
                  SELECT 1 FROM vulnerabilities v WHERE v.reported_by = u.user_id
              )";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Simulated EXCEPT: Users who reported incidents but not vulnerabilities"
    ];
}

function handleCreateView($conn, $data) {
    global $query;
    $view_name = $data['view_name'];
    $view_query = $data['view_query'];

    // Drop view if exists
    $dropQuery = "DROP VIEW IF EXISTS {$view_name}";
    $conn->exec($dropQuery);

    // Create view
    $query = "CREATE VIEW {$view_name} AS {$view_query}";
    $conn->exec($query);

    // Show the created view data
    $selectQuery = "SELECT * FROM {$view_name} LIMIT 10";
    $stmt = $conn->prepare($selectQuery);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query,
        'message' => "View '{$view_name}' created successfully!"
    ];
}

function handleViewOperations($conn, $data) {
    global $query;
    $view_operation = $data['view_operation'];

    switch ($view_operation) {
        case 'list_views':
            $query = "SELECT TABLE_NAME as view_name, VIEW_DEFINITION as definition
                      FROM INFORMATION_SCHEMA.VIEWS
                      WHERE TABLE_SCHEMA = 'security_management_db'";
            break;

        case 'security_summary':
            // Create a comprehensive security summary view
            $viewName = 'security_summary_view';
            $dropQuery = "DROP VIEW IF EXISTS {$viewName}";
            $conn->exec($dropQuery);

            $createQuery = "CREATE VIEW {$viewName} AS
                           SELECT
                               u.name as reporter_name,
                               r.role_name,
                               COUNT(DISTINCT i.incident_id) as incident_count,
                               COUNT(DISTINCT v.vuln_id) as vulnerability_count,
                               COUNT(DISTINCT l.log_id) as log_count
                           FROM users u
                           LEFT JOIN roles r ON u.role_id = r.role_id
                           LEFT JOIN incidents i ON i.reported_by = u.user_id
                           LEFT JOIN vulnerabilities v ON v.reported_by = u.user_id
                           LEFT JOIN logs l ON l.user_id = u.user_id
                           GROUP BY u.user_id, u.name, r.role_name";

            $conn->exec($createQuery);
            $query = "SELECT * FROM {$viewName}";
            break;

        case 'incident_details':
            $viewName = 'incident_details_view';
            $dropQuery = "DROP VIEW IF EXISTS {$viewName}";
            $conn->exec($dropQuery);

            $createQuery = "CREATE VIEW {$viewName} AS
                           SELECT
                               i.incident_id,
                               i.title,
                               i.type,
                               i.status,
                               u.name as reporter_name,
                               GROUP_CONCAT(a.asset_name) as affected_assets
                           FROM incidents i
                           LEFT JOIN users u ON i.reported_by = u.user_id
                           LEFT JOIN incident_assets ia ON i.incident_id = ia.incident_id
                           LEFT JOIN assets a ON ia.asset_id = a.asset_id
                           GROUP BY i.incident_id, i.title, i.type, i.status, u.name";

            $conn->exec($createQuery);
            $query = "SELECT * FROM {$viewName}";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subqueries, Set Operations & Views</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .operation-section { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .result-section { max-height: 300px; overflow-y: auto; }
        .query-display { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 11px; }
        .predefined-queries { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h4><i class="fas fa-code-branch"></i> Subqueries, Set Operations & Views</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($result['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($result['message']) ?></div>
        <?php endif; ?>

        <!-- Subqueries Section -->
        <div class="operation-section">
            <h5><i class="fas fa-sitemap"></i> Subqueries</h5>
            <form method="POST" class="row g-2">
                <input type="hidden" name="operation" value="subquery">
                <div class="col-md-3">
                    <select name="subquery_type" class="form-select form-select-sm" required>
                        <option value="">Select Subquery Type</option>
                        <option value="exists">EXISTS - Users with incidents</option>
                        <option value="in">IN - Admin/Analyst users</option>
                        <option value="not_in">NOT IN - Unused assets</option>
                        <option value="all">ALL - Vulnerabilities after critical ones</option>
                        <option value="any">ANY - Incidents after resolved ones</option>
                        <option value="correlated">CORRELATED - First users by role</option>
                        <option value="scalar">SCALAR - User incident counts</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary btn-sm">Execute Subquery</button>
                    <small class="text-muted ms-2">Predefined subquery examples demonstrating different types</small>
                </div>
            </form>
        </div>

        <!-- Set Operations Section -->
        <div class="operation-section">
            <h5><i class="fas fa-object-group"></i> Set Operations</h5>
            <div class="row g-2">
                <!-- UNION -->
                <div class="col-md-4">
                    <h6>UNION Operations</h6>
                    <form method="POST">
                        <input type="hidden" name="operation" value="union">
                        <select name="union_type" class="form-select form-select-sm mb-2" required>
                            <option value="basic">Basic UNION</option>
                            <option value="all">UNION ALL</option>
                            <option value="with_order">UNION with ORDER</option>
                        </select>
                        <button type="submit" class="btn btn-success btn-sm w-100">Execute UNION</button>
                    </form>
                </div>

                <!-- INTERSECT (Simulated) -->
                <div class="col-md-4">
                    <h6>INTERSECT (Simulated)</h6>
                    <form method="POST">
                        <input type="hidden" name="operation" value="intersect">
                        <button type="submit" class="btn btn-warning btn-sm w-100">Execute INTERSECT</button>
                        <small class="text-muted">Users in both incidents & vulnerabilities</small>
                    </form>
                </div>

                <!-- EXCEPT (Simulated) -->
                <div class="col-md-4">
                    <h6>EXCEPT (Simulated)</h6>
                    <form method="POST">
                        <input type="hidden" name="operation" value="except">
                        <button type="submit" class="btn btn-info btn-sm w-100">Execute EXCEPT</button>
                        <small class="text-muted">Users in incidents but not vulnerabilities</small>
                    </form>
                </div>
            </div>
        </div>

        <!-- Views Section -->
        <div class="operation-section">
            <h5><i class="fas fa-eye"></i> Views Management</h5>

            <!-- Create Custom View -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <h6>Create Custom View</h6>
                    <form method="POST">
                        <input type="hidden" name="operation" value="create_view">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" name="view_name" class="form-control form-control-sm" placeholder="View Name" required>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="view_query" class="form-control form-control-sm"
                                       placeholder="SELECT query (without CREATE VIEW)" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Create View</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Predefined View Operations -->
            <div class="row">
                <div class="col-md-12">
                    <h6>Predefined Views & Operations</h6>
                    <form method="POST">
                        <input type="hidden" name="operation" value="view_operations">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select name="view_operation" class="form-select form-select-sm" required>
                                    <option value="">Select Operation</option>
                                    <option value="list_views">List All Views</option>
                                    <option value="security_summary">Security Summary View</option>
                                    <option value="incident_details">Incident Details View</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-success btn-sm">Execute View Operation</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Complex Query Examples -->
        <div class="operation-section">
            <h5><i class="fas fa-magic"></i> Complex Query Examples</h5>
            <div class="predefined-queries">
                <button class="btn btn-outline-primary btn-sm" onclick="executeComplexQuery('nested_subquery')">
                    Nested Subqueries
                </button>
                <button class="btn btn-outline-success btn-sm" onclick="executeComplexQuery('union_subquery')">
                    UNION with Subquery
                </button>
                <button class="btn btn-outline-warning btn-sm" onclick="executeComplexQuery('case_when')">
                    CASE WHEN Statements
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="executeComplexQuery('window_functions')">
                    Window Functions
                </button>
            </div>
        </div>

        <!-- Sample Queries Reference -->
        <div class="operation-section">
            <h5><i class="fas fa-book"></i> Query Examples</h5>
            <div class="accordion" id="queryExamples">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#subqueryExamples">
                            Subquery Examples
                        </button>
                    </h2>
                    <div id="subqueryExamples" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <code>
                                -- EXISTS Example<br>
                                SELECT name FROM users u WHERE EXISTS (SELECT 1 FROM incidents i WHERE i.reported_by = u.user_id);<br><br>

                                -- IN Example<br>
                                SELECT * FROM assets WHERE asset_id IN (SELECT asset_id FROM incident_assets);<br><br>

                                -- Correlated Subquery<br>
                                SELECT name, (SELECT COUNT(*) FROM logs WHERE user_id = users.user_id) as log_count FROM users;
                            </code>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setExamples">
                            Set Operations Examples
                        </button>
                    </h2>
                    <div id="setExamples" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <code>
                                -- UNION Example<br>
                                SELECT name as entity FROM users UNION SELECT asset_name as entity FROM assets;<br><br>

                                -- Simulated INTERSECT<br>
                                SELECT user_id FROM (SELECT reported_by as user_id FROM incidents) t1
                                WHERE EXISTS (SELECT 1 FROM vulnerabilities WHERE reported_by = t1.user_id);
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <?php if ($result): ?>
            <div class="mt-4">
                <h5><i class="fas fa-code"></i> Generated Query</h5>
                <div class="query-display mb-3"><?= htmlspecialchars($result['query']) ?></div>

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
        function executeComplexQuery(type) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            // Add operation type
            const operationInput = document.createElement('input');
            operationInput.type = 'hidden';
            operationInput.name = 'operation';
            operationInput.value = 'subquery';
            form.appendChild(operationInput);

            // Add subquery type based on the complex query type
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'subquery_type';

            switch(type) {
                case 'nested_subquery':
                    typeInput.value = 'correlated';
                    break;
                case 'union_subquery':
                    form.removeChild(operationInput);
                    operationInput.value = 'union';
                    form.appendChild(operationInput);

                    const unionTypeInput = document.createElement('input');
                    unionTypeInput.type = 'hidden';
                    unionTypeInput.name = 'union_type';
                    unionTypeInput.value = 'basic';
                    form.appendChild(unionTypeInput);
                    break;
                default:
                    typeInput.value = 'exists';
            }

            if (type !== 'union_subquery') {
                form.appendChild(typeInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>