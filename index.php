<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Management Database System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-shield-alt"></i> Security DB Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#crud"><i class="fas fa-edit"></i> CRUD Operations</a></li>
                    <li class="nav-item"><a class="nav-link" href="#constraints"><i class="fas fa-search-plus"></i> Query Builder</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Hero Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="hero-section text-center p-5 rounded">
                    <div class="hero-content">
                        <h1 class="display-4 text-white mb-3 fw-bold">
                            <i class="fas fa-database me-3"></i>Security Management Database
                        </h1>
                        <p class="lead text-white mb-0 fs-5">
                            CRUD Operations & Advanced Query Builder for Security Management
                        </p>
                        <div class="mt-4">
                            <span class="badge bg-light text-dark me-2">MySQL</span>
                            <span class="badge bg-light text-dark me-2">PHP</span>
                            <span class="badge bg-light text-dark me-2">Bootstrap</span>
                            <span class="badge bg-light text-dark">JavaScript</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRUD Operations Section -->
        <section id="crud" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-edit"></i> CRUD Operations</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group" id="crud-tabs" role="tablist">
                                <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#crud-roles" role="tab">
                                    <i class="fas fa-users-cog"></i> Roles
                                </a>
                                <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#crud-users" role="tab">
                                    <i class="fas fa-users"></i> Users
                                </a>
                                <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#crud-incidents" role="tab">
                                    <i class="fas fa-exclamation-triangle"></i> Incidents
                                </a>
                                <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#crud-assets" role="tab">
                                    <i class="fas fa-server"></i> Assets
                                </a>
                                <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#crud-vulnerabilities" role="tab">
                                    <i class="fas fa-bug"></i> Vulnerabilities
                                </a>
                                <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#crud-logs" role="tab">
                                    <i class="fas fa-clipboard-list"></i> Logs
                                </a>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content scrollable-section" id="crud-content">
                                <div class="tab-pane fade show active" id="crud-roles" role="tabpanel">
                                    <iframe src="operations/crud.php?table=roles" class="operation-frame"></iframe>
                                </div>
                                <div class="tab-pane fade" id="crud-users" role="tabpanel">
                                    <iframe src="operations/crud.php?table=users" class="operation-frame"></iframe>
                                </div>
                                <div class="tab-pane fade" id="crud-incidents" role="tabpanel">
                                    <iframe src="operations/crud.php?table=incidents" class="operation-frame"></iframe>
                                </div>
                                <div class="tab-pane fade" id="crud-assets" role="tabpanel">
                                    <iframe src="operations/crud.php?table=assets" class="operation-frame"></iframe>
                                </div>
                                <div class="tab-pane fade" id="crud-vulnerabilities" role="tabpanel">
                                    <iframe src="operations/crud.php?table=vulnerabilities" class="operation-frame"></iframe>
                                </div>
                                <div class="tab-pane fade" id="crud-logs" role="tabpanel">
                                    <iframe src="operations/crud.php?table=logs" class="operation-frame"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Constraints Section -->
        <section id="constraints" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3><i class="fas fa-search-plus"></i> Advanced Query Builder</h3>
                </div>
                <div class="card-body">
                    <div>
                        <iframe src="operations/constraints.php" class="operation-frame"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2025 Security Management Database System by Ruhan. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Auto-resize iframes to fit content without scrolling
        function resizeIframe(iframe) {
            try {
                iframe.style.height = iframe.contentWindow.document.documentElement.scrollHeight + 'px';
            } catch (e) {
                console.log('Cannot access iframe content');
            }
        }

        // Resize all iframes when they load
        document.addEventListener('DOMContentLoaded', function() {
            const iframes = document.querySelectorAll('.operation-frame');
            iframes.forEach(function(iframe) {
                iframe.addEventListener('load', function() {
                    resizeIframe(iframe);
                    // Re-check size after a short delay to catch dynamic content
                    setTimeout(() => resizeIframe(iframe), 500);
                    setTimeout(() => resizeIframe(iframe), 1000);
                    setTimeout(() => resizeIframe(iframe), 2000);
                });
            });

            // Listen for window resize
            window.addEventListener('resize', function() {
                iframes.forEach(resizeIframe);
            });
        });
    </script>
</body>
</html>