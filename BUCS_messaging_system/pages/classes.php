<?php
// pages/classes.php
require_once '../includes/config.php';
$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $name = sanitize($db,$_POST['classname']);
        $msg  = $db->query("INSERT INTO classes (classname) VALUES('$name')")
              ? '<div class="alert alert-success"><i class="fa fa-check"></i> Class added.</div>'
              : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }
    if ($action === 'update') {
        $id   = (int)$_POST['class_id'];
        $name = sanitize($db,$_POST['classname']);
        $msg  = $db->query("UPDATE classes SET classname='$name' WHERE class_id=$id")
              ? '<div class="alert alert-success"><i class="fa fa-check"></i> Class updated.</div>'
              : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }
    if ($action === 'delete') {
        $id  = (int)$_POST['class_id'];
        $msg = $db->query("DELETE FROM classes WHERE class_id=$id")
             ? '<div class="alert alert-success"><i class="fa fa-trash"></i> Class deleted.</div>'
             : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }
}

$classes = $db->query("
    SELECT c.*, COUNT(uc.id_no) member_count
    FROM classes c
    LEFT JOIN user_classes uc ON c.class_id = uc.class_id
    GROUP BY c.class_id ORDER BY c.classname");
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Classes – BUCS</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><i class="fa fa-chalkboard"></i> Classes Management</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add Class</button>
  </div>
  <?= $msg ?>
  <div class="section-card">
    <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID</th><th>Class Name</th><th>Members</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($c = $classes->fetch_assoc()): ?>
      <tr>
        <td><?= $c['class_id'] ?></td>
        <td><strong><?= htmlspecialchars($c['classname']) ?></strong></td>
        <td><span class="badge badge-read"><?= $c['member_count'] ?> users</span></td>
        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
        <td class="action-cell">
          <button class="btn btn-sm btn-edit" onclick='editClass(<?= json_encode($c) ?>)'><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?= $c['class_id'] ?>)"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- ADD MODAL -->
  <div id="addModal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header"><h3><i class="fa fa-plus"></i> Add Class</h3><button onclick="closeModal('addModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-group"><label>Class Name *</label><input type="text" name="classname" required placeholder="e.g. BSIT 3-A"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button></div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editModal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header"><h3><i class="fa fa-pen"></i> Edit Class</h3><button onclick="closeModal('editModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="class_id" id="edit_class_id">
        <div class="form-group"><label>Class Name *</label><input type="text" name="classname" id="edit_classname" required></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button></div>
      </form>
    </div>
  </div>

  <form id="deleteForm" method="POST" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="class_id" id="delete_id">
  </form>
</main>
<script src="../assets/js/app.js"></script>
<script>
function editClass(c){
  document.getElementById('edit_class_id').value = c.class_id;
  document.getElementById('edit_classname').value = c.classname;
  openModal('editModal');
}
function confirmDelete(id){
  if(confirm('Delete this class? Users enrolled will lose this class.')){
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}
</script>
</body></html>