<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$error = '';

// Add document
if (isset($_POST['add_document'])) {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $error = 'Document name is required.';
    } else {
        $exists = $pdo->prepare("SELECT id FROM documents WHERE name = ?");
        $exists->execute([$name]);
        if ($exists->fetch()) {
            $error = 'That document already exists.';
        } else {
            $pdo->prepare("INSERT INTO documents (name) VALUES (?)")->execute([$name]);
            header('Location: documents.php?msg=added');
            exit();
        }
    }
}

// Update document
if (isset($_POST['update_document'])) {
    $doc_id = intval($_POST['doc_id']);
    $name   = trim($_POST['name']);
    if (empty($name)) {
        $error = 'Document name is required.';
    } else {
        // Check duplicate excluding self
        $exists = $pdo->prepare("SELECT id FROM documents WHERE name = ? AND id != ?");
        $exists->execute([$name, $doc_id]);
        if ($exists->fetch()) {
            $error = 'That document name already exists.';
        } else {
            $pdo->prepare("UPDATE documents SET name = ? WHERE id = ?")->execute([$name, $doc_id]);
            header('Location: documents.php?msg=updated');
            exit();
        }
    }
}

// Delete document
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);

    // Block if document has active appointments
    $active = $pdo->prepare("
        SELECT COUNT(*) FROM appointments
        WHERE document_id = ? AND status IN ('pending','confirmed')
    ");
    $active->execute([$del_id]);
    if ($active->fetchColumn() > 0) {
        $error = 'Cannot delete — this document has active appointments.';
    } else {
        $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$del_id]);
        header('Location: documents.php?msg=deleted');
        exit();
    }
}

$documents = $pdo->query("
    SELECT d.*, COUNT(a.id) as total_appointments
    FROM documents d
    LEFT JOIN appointments a ON a.document_id = d.id
    GROUP BY d.id
    ORDER BY d.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Document Management</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="/govsched/includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/govsched/pages/admin/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i><p>Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i><p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="slots.php" class="nav-link">
                            <i class="nav-icon fas fa-clock"></i><p>Slot Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="documents.php" class="nav-link active">
                            <i class="nav-icon fas fa-file-alt"></i><p>Documents</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="account.php" class="nav-link">
                            <i class="nav-icon fas fa-user-cog"></i><p>Account</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Document Management</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php if (isset($_GET['msg'])): ?>
                <?php
                $msgs = [
                    'added'   => ['success', 'Document added successfully.'],
                    'updated' => ['success', 'Document updated successfully.'],
                    'deleted' => ['warning', 'Document deleted.'],
                ];
                if (isset($msgs[$_GET['msg']])):
                    [$type, $text] = $msgs[$_GET['msg']];
                ?>
                <div class="alert alert-<?= $type ?> alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $text ?>
                </div>
                <?php endif; endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="row">

                    <!-- Add Document -->
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Add Document</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="documents.php">
                                    <div class="form-group">
                                        <label>Document Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="e.g. Voter's ID" required>
                                    </div>
                                    <button type="submit" name="add_document" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus"></i> Add Document
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Table -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt mr-1"></i> All Documents
                                    <span class="badge badge-primary ml-2"><?= count($documents) ?></span>
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document Name</th>
                                            <th>Total Appointments</th>
                                            <th>Edit</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $i => $doc): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td id="name_display_<?= $doc['id'] ?>">
                                                <?= htmlspecialchars($doc['name']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= $doc['total_appointments'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Inline edit form -->
                                                <form method="POST" action="documents.php"
                                                      class="form-inline" id="edit_form_<?= $doc['id'] ?>"
                                                      style="display:none;">
                                                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                                    <input type="text" name="name"
                                                           class="form-control form-control-sm mr-2"
                                                           value="<?= htmlspecialchars($doc['name']) ?>"
                                                           style="width:180px;" required>
                                                    <button type="submit" name="update_document"
                                                            class="btn btn-success btn-sm mr-1">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-secondary btn-sm"
                                                            onclick="cancelEdit(<?= $doc['id'] ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-warning btn-sm"
                                                        id="edit_btn_<?= $doc['id'] ?>"
                                                        onclick="showEdit(<?= $doc['id'] ?>)">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <a href="documents.php?delete=<?= $doc['id'] ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Delete this document?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($documents)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                No documents yet.
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>GovSched</strong> &copy; <?= date('Y') ?>
    </footer>
</div>
<script src="../../assets/plugins/jquery/jquery.min.js"></script>
<script src="../../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/dist/js/adminlte.min.js"></script>
<script>
function showEdit(id) {
    $('#edit_form_' + id).show();
    $('#edit_btn_' + id).hide();
    $('#name_display_' + id).hide();
}

function cancelEdit(id) {
    $('#edit_form_' + id).hide();
    $('#edit_btn_' + id).show();
    $('#name_display_' + id).show();
}
</script>
</body>
</html>