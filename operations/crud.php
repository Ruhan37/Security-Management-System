<?php
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$table = $_GET['table'] ?? 'roles';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'];

    try {
        switch ($operation) {
            case 'create':
                handleCreate($conn, $table, $_POST);
                break;
            case 'update':
                handleUpdate($conn, $table, $_POST);
                break;
            case 'delete':
                handleDelete($conn, $table, $_POST);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get table structure and data
$tableConfig = getTableConfig($table);
$data = getData($conn, $table);
$foreignKeyData = getForeignKeyData($conn, $table);

function handleCreate($conn, $table, $data) {
    $config = getTableConfig($table);
    $fields = array_keys($config['fields']);
    $primaryKey = $config['primary_key'];

    // Remove primary key from fields if it's auto-increment
    if ($config['auto_increment']) {
        $fields = array_filter($fields, function($field) use ($primaryKey) {
            return $field !== $primaryKey;
        });
    }

    $placeholders = ':' . implode(', :', $fields);
    $fieldsList = implode(', ', $fields);

    $sql = "INSERT INTO {$table} ({$fieldsList}) VALUES ({$placeholders})";
    $stmt = $conn->prepare($sql);

    foreach ($fields as $field) {
        $stmt->bindValue(':' . $field, $data[$field]);
    }

    $stmt->execute();
    header("Location: crud.php?table={$table}&success=created");
    exit;
}

function handleUpdate($conn, $table, $data) {
    $config = getTableConfig($table);
    $fields = array_keys($config['fields']);
    $primaryKey = $config['primary_key'];

    // Remove primary key from fields to update
    $updateFields = array_filter($fields, function($field) use ($primaryKey) {
        return $field !== $primaryKey;
    });

    $setClause = implode(', ', array_map(function($field) {
        return "{$field} = :{$field}";
    }, $updateFields));

    $sql = "UPDATE {$table} SET {$setClause} WHERE {$primaryKey} = :pk";
    $stmt = $conn->prepare($sql);

    foreach ($updateFields as $field) {
        $stmt->bindValue(':' . $field, $data[$field]);
    }
    $stmt->bindValue(':pk', $data[$primaryKey]);

    $stmt->execute();
    header("Location: crud.php?table={$table}&success=updated");
    exit;
}

function handleDelete($conn, $table, $data) {
    $config = getTableConfig($table);
    $primaryKey = $config['primary_key'];

    $sql = "DELETE FROM {$table} WHERE {$primaryKey} = :pk";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':pk', $data['id']);

    $stmt->execute();
    header("Location: crud.php?table={$table}&success=deleted");
    exit;
}

function getData($conn, $table) {
    $sql = "SELECT * FROM {$table}";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getForeignKeyData($conn, $table) {
    $config = getTableConfig($table);
    $foreignKeys = [];

    foreach ($config['foreign_keys'] ?? [] as $field => $ref) {
        $sql = "SELECT * FROM {$ref['table']}";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $foreignKeys[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $foreignKeys;
}

function getTableConfig($table) {
    $configs = [
        'roles' => [
            'primary_key' => 'role_id',
            'auto_increment' => true,
            'fields' => [
                'role_id' => ['type' => 'int', 'label' => 'Role ID'],
                'role_name' => ['type' => 'varchar', 'label' => 'Role Name', 'required' => true]
            ]
        ],
        'users' => [
            'primary_key' => 'user_id',
            'auto_increment' => true,
            'fields' => [
                'user_id' => ['type' => 'int', 'label' => 'User ID'],
                'name' => ['type' => 'varchar', 'label' => 'Name', 'required' => true],
                'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                'role_id' => ['type' => 'int', 'label' => 'Role', 'required' => true],
                'join_date' => ['type' => 'date', 'label' => 'Join Date', 'required' => true]
            ],
            'foreign_keys' => [
                'role_id' => ['table' => 'roles', 'key' => 'role_id', 'display' => 'role_name']
            ]
        ],
        'incidents' => [
            'primary_key' => 'incident_id',
            'auto_increment' => true,
            'fields' => [
                'incident_id' => ['type' => 'int', 'label' => 'Incident ID'],
                'title' => ['type' => 'varchar', 'label' => 'Title', 'required' => true],
                'type' => ['type' => 'varchar', 'label' => 'Type', 'required' => true],
                'status' => ['type' => 'varchar', 'label' => 'Status', 'required' => true],
                'detected_date' => ['type' => 'date', 'label' => 'Detected Date', 'required' => true],
                'resolved_date' => ['type' => 'date', 'label' => 'Resolved Date'],
                'reported_by' => ['type' => 'int', 'label' => 'Reported By', 'required' => true]
            ],
            'foreign_keys' => [
                'reported_by' => ['table' => 'users', 'key' => 'user_id', 'display' => 'name']
            ]
        ],
        'assets' => [
            'primary_key' => 'asset_id',
            'auto_increment' => true,
            'fields' => [
                'asset_id' => ['type' => 'int', 'label' => 'Asset ID'],
                'asset_name' => ['type' => 'varchar', 'label' => 'Asset Name', 'required' => true],
                'asset_type' => ['type' => 'varchar', 'label' => 'Asset Type', 'required' => true],
                'criticality' => ['type' => 'varchar', 'label' => 'Criticality', 'required' => true]
            ]
        ],
        'vulnerabilities' => [
            'primary_key' => 'vuln_id',
            'auto_increment' => true,
            'fields' => [
                'vuln_id' => ['type' => 'int', 'label' => 'Vulnerability ID'],
                'title' => ['type' => 'varchar', 'label' => 'Title', 'required' => true],
                'severity' => ['type' => 'varchar', 'label' => 'Severity', 'required' => true],
                'report_date' => ['type' => 'date', 'label' => 'Report Date', 'required' => true],
                'reported_by' => ['type' => 'int', 'label' => 'Reported By', 'required' => true]
            ],
            'foreign_keys' => [
                'reported_by' => ['table' => 'users', 'key' => 'user_id', 'display' => 'name']
            ]
        ],
        'logs' => [
            'primary_key' => 'log_id',
            'auto_increment' => true,
            'fields' => [
                'log_id' => ['type' => 'int', 'label' => 'Log ID'],
                'user_id' => ['type' => 'int', 'label' => 'User', 'required' => true],
                'activity' => ['type' => 'text', 'label' => 'Activity', 'required' => true],
                'log_time' => ['type' => 'timestamp', 'label' => 'Log Time']
            ],
            'foreign_keys' => [
                'user_id' => ['table' => 'users', 'key' => 'user_id', 'display' => 'name']
            ]
        ]
    ];

    return $configs[$table];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Operations - <?= ucfirst($table) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .table-responsive { max-height: 300px; overflow-y: auto; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Record <?= htmlspecialchars($_GET['success']) ?> successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error: <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <h5><i class="fas fa-plus"></i> Add New <?= ucfirst(substr($table, 0, -1)) ?></h5>
                <form method="POST" class="mb-4">
                    <input type="hidden" name="operation" value="create">
                    <?php foreach ($tableConfig['fields'] as $field => $config): ?>
                        <?php if ($field === $tableConfig['primary_key'] && $tableConfig['auto_increment']) continue; ?>
                        <div class="mb-2">
                            <label class="form-label"><?= $config['label'] ?></label>
                            <?php if (isset($tableConfig['foreign_keys'][$field])): ?>
                                <?php $fk = $tableConfig['foreign_keys'][$field]; ?>
                                <select name="<?= $field ?>" class="form-select form-select-sm" <?= $config['required'] ?? false ? 'required' : '' ?>>
                                    <option value="">Select <?= $config['label'] ?></option>
                                    <?php foreach ($foreignKeyData[$field] as $row): ?>
                                        <option value="<?= $row[$fk['key']] ?>">
                                            <?= htmlspecialchars($row[$fk['display']]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= $config['type'] === 'varchar' ? 'text' : $config['type'] ?>"
                                       name="<?= $field ?>"
                                       class="form-control form-control-sm"
                                       <?= $config['required'] ?? false ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </form>
            </div>

            <div class="col-md-7">
                <h5><i class="fas fa-list"></i> <?= ucfirst($table) ?> List</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead class="table-dark">
                            <tr>
                                <?php foreach ($tableConfig['fields'] as $field => $config): ?>
                                    <th><?= $config['label'] ?></th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($tableConfig['fields'] as $field => $config): ?>
                                        <td>
                                            <?php if (isset($tableConfig['foreign_keys'][$field])): ?>
                                                <?php
                                                $fk = $tableConfig['foreign_keys'][$field];
                                                $fkRow = array_filter($foreignKeyData[$field], function($fkRow) use ($fk, $row, $field) {
                                                    return $fkRow[$fk['key']] == $row[$field];
                                                });
                                                $fkRow = reset($fkRow);
                                                echo htmlspecialchars($fkRow[$fk['display']] ?? '');
                                                ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($row[$field]) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button class="btn btn-warning btn-sm me-1" onclick="editRecord(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="operation" value="delete">
                                            <input type="hidden" name="id" value="<?= $row[$tableConfig['primary_key']] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit <?= ucfirst(substr($table, 0, -1)) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="operation" value="update">
                        <div id="editFormFields"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tableConfig = <?= json_encode($tableConfig) ?>;
        const foreignKeyData = <?= json_encode($foreignKeyData) ?>;

        function editRecord(record) {
            const formFields = document.getElementById('editFormFields');
            formFields.innerHTML = '';

            Object.keys(tableConfig.fields).forEach(field => {
                const config = tableConfig.fields[field];
                const div = document.createElement('div');
                div.className = 'mb-3';

                const label = document.createElement('label');
                label.className = 'form-label';
                label.textContent = config.label;
                div.appendChild(label);

                let input;
                if (tableConfig.foreign_keys && tableConfig.foreign_keys[field]) {
                    const fk = tableConfig.foreign_keys[field];
                    input = document.createElement('select');
                    input.className = 'form-select';

                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = `Select ${config.label}`;
                    input.appendChild(defaultOption);

                    foreignKeyData[field].forEach(fkRow => {
                        const option = document.createElement('option');
                        option.value = fkRow[fk.key];
                        option.textContent = fkRow[fk.display];
                        if (fkRow[fk.key] == record[field]) option.selected = true;
                        input.appendChild(option);
                    });
                } else {
                    input = document.createElement('input');
                    input.className = 'form-control';
                    input.type = config.type === 'varchar' ? 'text' : config.type;
                    input.value = record[field] || '';
                }

                input.name = field;
                if (config.required) input.required = true;

                div.appendChild(input);
                formFields.appendChild(div);
            });

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>