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
            case 'inner_join':
                $result = handleInnerJoin($conn, $_POST);
                break;
            case 'left_join':
                $result = handleLeftJoin($conn, $_POST);
                break;
            case 'right_join':
                $result = handleRightJoin($conn, $_POST);
                break;
            case 'full_join':
                $result = handleFullJoin($conn, $_POST);
                break;
            case 'cross_join':
                $result = handleCrossJoin($conn, $_POST);
                break;
            case 'natural_join':
                $result = handleNaturalJoin($conn, $_POST);
                break;
            case 'self_join':
                $result = handleSelfJoin($conn, $_POST);
                break;
            case 'equi_join':
                $result = handleEquiJoin($conn, $_POST);
                break;
            case 'non_equi_join':
                $result = handleNonEquiJoin($conn, $_POST);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleInnerJoin($conn, $data) {
    global $query;
    $join_type = $data['join_type'] ?? 'users_roles';

    switch ($join_type) {
        case 'users_roles':
            $query = "SELECT u.user_id, u.name, u.email, r.role_name
                      FROM users u
                      INNER JOIN roles r ON u.role_id = r.role_id";
            break;
        case 'incidents_users':
            $query = "SELECT i.incident_id, i.title, i.status, u.name as reporter
                      FROM incidents i
                      INNER JOIN users u ON i.reported_by = u.user_id";
            break;
        case 'incidents_assets':
            $query = "SELECT i.title, a.asset_name, a.asset_type, a.criticality
                      FROM incidents i
                      INNER JOIN incident_assets ia ON i.incident_id = ia.incident_id
                      INNER JOIN assets a ON ia.asset_id = a.asset_id";
            break;
        case 'vulnerabilities_users':
            $query = "SELECT v.title, v.severity, u.name as reporter, u.email
                      FROM vulnerabilities v
                      INNER JOIN users u ON v.reported_by = u.user_id";
            break;
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

function handleLeftJoin($conn, $data) {
    global $query;
    $join_type = $data['join_type'] ?? 'users_incidents';

    switch ($join_type) {
        case 'users_incidents':
            $query = "SELECT u.name, u.email, COUNT(i.incident_id) as incident_count
                      FROM users u
                      LEFT JOIN incidents i ON u.user_id = i.reported_by
                      GROUP BY u.user_id, u.name, u.email";
            break;
        case 'assets_incidents':
            $query = "SELECT a.asset_name, a.asset_type, COUNT(ia.incident_id) as incident_count
                      FROM assets a
                      LEFT JOIN incident_assets ia ON a.asset_id = ia.asset_id
                      GROUP BY a.asset_id, a.asset_name, a.asset_type";
            break;
        case 'roles_users':
            $query = "SELECT r.role_name, COUNT(u.user_id) as user_count
                      FROM roles r
                      LEFT JOIN users u ON r.role_id = u.role_id
                      GROUP BY r.role_id, r.role_name";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleRightJoin($conn, $data) {
    global $query;
    // MySQL supports RIGHT JOIN
    $query = "SELECT r.role_name, u.name, u.email
              FROM users u
              RIGHT JOIN roles r ON u.role_id = r.role_id";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleFullJoin($conn, $data) {
    global $query;
    // MySQL doesn't support FULL OUTER JOIN directly, so we simulate it with UNION
    $query = "SELECT u.name, r.role_name, 'User with Role' as join_type
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.role_id
              UNION
              SELECT u.name, r.role_name, 'Role without User' as join_type
              FROM users u
              RIGHT JOIN roles r ON u.role_id = r.role_id
              WHERE u.user_id IS NULL";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Simulated FULL OUTER JOIN using UNION"
    ];
}

function handleCrossJoin($conn, $data) {
    global $query;
    $join_type = $data['join_type'] ?? 'roles_assets';

    switch ($join_type) {
        case 'roles_assets':
            $query = "SELECT r.role_name, a.asset_name, a.criticality
                      FROM roles r
                      CROSS JOIN assets a
                      LIMIT 20"; // Limit to prevent too many results
            break;
        case 'small_cross':
            $query = "SELECT r1.role_name as role1, r2.role_name as role2
                      FROM roles r1
                      CROSS JOIN roles r2
                      WHERE r1.role_id != r2.role_id";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleNaturalJoin($conn, $data) {
    global $query;
    // MySQL supports NATURAL JOIN, but it's based on columns with same names
    $query = "SELECT u.name, u.email, r.role_name
              FROM users u
              NATURAL JOIN roles r";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Natural join on matching column names (role_id)"
    ];
}

function handleSelfJoin($conn, $data) {
    global $query;
    $join_type = $data['join_type'] ?? 'users_same_role';

    switch ($join_type) {
        case 'users_same_role':
            $query = "SELECT u1.name as user1, u2.name as user2, r.role_name
                      FROM users u1
                      INNER JOIN users u2 ON u1.role_id = u2.role_id AND u1.user_id != u2.user_id
                      INNER JOIN roles r ON u1.role_id = r.role_id
                      WHERE u1.user_id < u2.user_id"; // Prevent duplicates
            break;
        case 'incidents_same_reporter':
            $query = "SELECT i1.title as incident1, i2.title as incident2, u.name as reporter
                      FROM incidents i1
                      INNER JOIN incidents i2 ON i1.reported_by = i2.reported_by AND i1.incident_id != i2.incident_id
                      INNER JOIN users u ON i1.reported_by = u.user_id
                      WHERE i1.incident_id < i2.incident_id";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query
    ];
}

function handleEquiJoin($conn, $data) {
    global $query;
    // Equi-join uses equality operator in JOIN condition
    $query = "SELECT u.name, r.role_name, i.title, v.title as vulnerability
              FROM users u
              INNER JOIN roles r ON u.role_id = r.role_id  -- Equi-join condition
              LEFT JOIN incidents i ON u.user_id = i.reported_by  -- Equi-join condition
              LEFT JOIN vulnerabilities v ON u.user_id = v.reported_by  -- Equi-join condition
              WHERE u.user_id <= 3"; // Limit results

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Multiple equi-joins using = operator"
    ];
}

function handleNonEquiJoin($conn, $data) {
    global $query;
    $join_type = $data['join_type'] ?? 'date_range';

    switch ($join_type) {
        case 'date_range':
            $query = "SELECT u.name, u.join_date, i.title, i.detected_date
                      FROM users u
                      INNER JOIN incidents i ON u.join_date < i.detected_date  -- Non-equi join
                      WHERE u.user_id <= 3 AND i.incident_id <= 3";
            break;
        case 'severity_comparison':
            $query = "SELECT v1.title as vuln1, v1.severity as sev1,
                             v2.title as vuln2, v2.severity as sev2
                      FROM vulnerabilities v1
                      INNER JOIN vulnerabilities v2 ON v1.vuln_id != v2.vuln_id
                      AND v1.report_date > v2.report_date  -- Non-equi join
                      WHERE v1.vuln_id <= 3 AND v2.vuln_id <= 3";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query . " -- Non-equi join using comparison operators"
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOIN Operations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .operation-section { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .result-section { max-height: 300px; overflow-y: auto; }
        .query-display { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 11px; }
        .join-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h4><i class="fas fa-link"></i> JOIN Operations</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="join-grid">
            <!-- INNER JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-intersection"></i> INNER JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="inner_join">
                    <select name="join_type" class="form-select form-select-sm mb-2" required>
                        <option value="users_roles">Users → Roles</option>
                        <option value="incidents_users">Incidents → Users</option>
                        <option value="incidents_assets">Incidents → Assets</option>
                        <option value="vulnerabilities_users">Vulnerabilities → Users</option>
                    </select>
                    <input type="number" name="limit" class="form-control form-control-sm mb-2" placeholder="Limit (optional)">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Execute INNER JOIN</button>
                </form>
            </div>

            <!-- LEFT JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-arrow-left"></i> LEFT JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="left_join">
                    <select name="join_type" class="form-select form-select-sm mb-2" required>
                        <option value="users_incidents">Users ← Incidents</option>
                        <option value="assets_incidents">Assets ← Incidents</option>
                        <option value="roles_users">Roles ← Users</option>
                    </select>
                    <button type="submit" class="btn btn-success btn-sm w-100">Execute LEFT JOIN</button>
                </form>
            </div>

            <!-- RIGHT JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-arrow-right"></i> RIGHT JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="right_join">
                    <button type="submit" class="btn btn-warning btn-sm w-100">Execute RIGHT JOIN</button>
                    <small class="text-muted">Users → Roles (all roles)</small>
                </form>
            </div>

            <!-- FULL OUTER JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-expand-arrows-alt"></i> FULL OUTER JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="full_join">
                    <button type="submit" class="btn btn-info btn-sm w-100">Execute FULL JOIN</button>
                    <small class="text-muted">Simulated with UNION</small>
                </form>
            </div>

            <!-- CROSS JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-times"></i> CROSS JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="cross_join">
                    <select name="join_type" class="form-select form-select-sm mb-2" required>
                        <option value="roles_assets">Roles × Assets</option>
                        <option value="small_cross">Roles × Roles (≠)</option>
                    </select>
                    <button type="submit" class="btn btn-danger btn-sm w-100">Execute CROSS JOIN</button>
                    <small class="text-muted">Cartesian product</small>
                </form>
            </div>

            <!-- NATURAL JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-leaf"></i> NATURAL JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="natural_join">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">Execute NATURAL JOIN</button>
                    <small class="text-muted">Users ⟕ Roles on role_id</small>
                </form>
            </div>
        </div>

        <!-- Special JOIN Types -->
        <div class="join-grid mt-3">
            <!-- SELF JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-recycle"></i> SELF JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="self_join">
                    <select name="join_type" class="form-select form-select-sm mb-2" required>
                        <option value="users_same_role">Users with Same Role</option>
                        <option value="incidents_same_reporter">Incidents by Same Reporter</option>
                    </select>
                    <button type="submit" class="btn btn-success btn-sm w-100">Execute SELF JOIN</button>
                </form>
            </div>

            <!-- EQUI JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-equals"></i> EQUI JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="equi_join">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Execute EQUI JOIN</button>
                    <small class="text-muted">Multiple tables with = conditions</small>
                </form>
            </div>

            <!-- NON-EQUI JOIN Section -->
            <div class="operation-section">
                <h5><i class="fas fa-not-equal"></i> NON-EQUI JOIN</h5>
                <form method="POST">
                    <input type="hidden" name="operation" value="non_equi_join">
                    <select name="join_type" class="form-select form-select-sm mb-2" required>
                        <option value="date_range">Date Range Comparison</option>
                        <option value="severity_comparison">Severity Comparison</option>
                    </select>
                    <button type="submit" class="btn btn-warning btn-sm w-100">Execute NON-EQUI JOIN</button>
                </form>
            </div>
        </div>

        <!-- JOIN Examples and Reference -->
        <div class="operation-section mt-4">
            <h5><i class="fas fa-book"></i> JOIN Reference & Examples</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>Common JOIN Patterns</h6>
                    <ul class="list-unstyled">
                        <li><strong>INNER JOIN:</strong> Records that exist in both tables</li>
                        <li><strong>LEFT JOIN:</strong> All records from left table + matching from right</li>
                        <li><strong>RIGHT JOIN:</strong> All records from right table + matching from left</li>
                        <li><strong>FULL OUTER:</strong> All records from both tables</li>
                        <li><strong>CROSS JOIN:</strong> Cartesian product of both tables</li>
                        <li><strong>SELF JOIN:</strong> Join table with itself</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Quick Examples</h6>
                    <div class="accordion" id="joinExamples">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#innerExample">
                                    INNER JOIN Example
                                </button>
                            </h2>
                            <div id="innerExample" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <code>
                                        SELECT u.name, r.role_name<br>
                                        FROM users u<br>
                                        INNER JOIN roles r ON u.role_id = r.role_id;
                                    </code>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#leftExample">
                                    LEFT JOIN Example
                                </button>
                            </h2>
                            <div id="leftExample" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <code>
                                        SELECT u.name, COUNT(i.incident_id)<br>
                                        FROM users u<br>
                                        LEFT JOIN incidents i ON u.user_id = i.reported_by<br>
                                        GROUP BY u.user_id;
                                    </code>
                                </div>
                            </div>
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
</body>
</html>