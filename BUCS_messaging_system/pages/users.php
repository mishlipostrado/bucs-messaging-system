<?php
// pages/users.php
require_once '../includes/config.php';
$db = getDB();
$msg = '';

// ── CREATE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $id    = sanitize($db,$_POST['id_no']);
        $fname = sanitize($db,$_POST['fname']);
        $mname = sanitize($db,$_POST['mname']);
        $lname = sanitize($db,$_POST['lname']);
        $uname = sanitize($db,$_POST['uname']);
        $pwd   = md5($_POST['pwd']);
        $sql   = "INSERT INTO users VALUES('$id','$fname','$mname','$lname','$uname','$pwd',NOW())";
        $msg   = $db->query($sql) ? '<div class="alert alert-success"><i class="fa fa-check"></i> User added successfully.</div>'
                                  : '<div class="alert alert-error"><i class="fa fa-xmark"></i> Error: '.$db->error.'</div>';
    }

    if ($action === 'update') {
        $id    = sanitize($db,$_POST['id_no']);
        $fname = sanitize($db,$_POST['fname']);
        $mname = sanitize($db,$_POST['mname']);
        $lname = sanitize($db,$_POST['lname']);
        $uname = sanitize($db,$_POST['uname']);
        $sets  = "fname='$fname',mname='$mname',lname='$lname',uname='$uname'";
        if (!empty($_POST['pwd'])) { $sets .= ",pwd='".md5($_POST['pwd'])."'"; }
        $sql = "UPDATE users SET $sets WHERE id_no='$id'";
        $msg = $db->query($sql) ? '<div class="alert alert-success"><i class="fa fa-check"></i> User updated.</div>'
                                : '<div class="alert alert-error"><i class="fa fa-xmark"></i> Error: '.$db->error.'</div>';
    }

    if ($action === 'delete') {
        $id  = sanitize($db,$_POST['id_no']);
        $sql = "DELETE FROM users WHERE id_no='$id'";
        $msg = $db->query($sql) ? '<div class="alert alert-success"><i class="fa fa-trash"></i> User deleted.</div>'
                                : '<div class="alert alert-error"><i class="fa fa-xmark"></i> Error: '.$db->error.'</div>';
    }
}

$users = $db->query("SELECT * FROM users ORDER BY id_no");
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users – BUCS</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><i class="fa fa-users"></i> Users Management</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add User</button>
  </div>

  <?= $msg ?>

  <div class="section-card">
    <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID No.</th><th>Full Name</th><th>Username</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($u = $users->fetch_assoc()): ?>
      <tr>
        <td><strong><?= htmlspecialchars($u['id_no']) ?></strong></td>
        <td><?= htmlspecialchars($u['fname'].' '.$u['mname'].' '.$u['lname']) ?></td>
        <td><code><?= htmlspecialchars($u['uname']) ?></code></td>
        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
        <td class="action-cell">
          <button class="btn btn-sm btn-edit" onclick='editUser(<?= json_encode($u) ?>)'><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-delete" onclick="confirmDelete('<?= $u['id_no'] ?>')"><i class="fa fa-trash"></i></button>
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
      <div class="modal-header"><h3><i class="fa fa-user-plus"></i> Add New User</h3><button onclick="closeModal('addModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group"><label>ID Number *</label><input type="text" name="id_no" required placeholder="e.g. 2024-0001"></div>
          <div class="form-group"><label>First Name *</label><input type="text" name="fname" required></div>
          <div class="form-group"><label>Middle Name</label><input type="text" name="mname"></div>
          <div class="form-group"><label>Last Name *</label><input type="text" name="lname" required></div>
          <div class="form-group"><label>Username *</label><input type="text" name="uname" required></div>
          <div class="form-group"><label>Password *</label><input type="password" name="pwd" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button></div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editModal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header"><h3><i class="fa fa-pen"></i> Edit User</h3><button onclick="closeModal('editModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id_no" id="edit_id_no">
        <div class="form-grid">
          <div class="form-group"><label>ID Number</label><input type="text" id="edit_id_display" readonly class="readonly-input"></div>
          <div class="form-group"><label>First Name *</label><input type="text" name="fname" id="edit_fname" required></div>
          <div class="form-group"><label>Middle Name</label><input type="text" name="mname" id="edit_mname"></div>
          <div class="form-group"><label>Last Name *</label><input type="text" name="lname" id="edit_lname" required></div>
          <div class="form-group"><label>Username *</label><input type="text" name="uname" id="edit_uname" required></div>
          <div class="form-group"><label>New Password <small>(leave blank to keep)</small></label><input type="password" name="pwd"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button></div>
      </form>
    </div>
  </div>

  <!-- DELETE FORM -->
  <form id="deleteForm" method="POST" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id_no" id="delete_id">
  </form>
</main>
<script src="../assets/js/app.js"></script>
<script>
function editUser(u){
  document.getElementById('edit_id_no').value = u.id_no;
  document.getElementById('edit_id_display').value = u.id_no;
  document.getElementById('edit_fname').value = u.fname;
  document.getElementById('edit_mname').value = u.mname||'';
  document.getElementById('edit_lname').value = u.lname;
  document.getElementById('edit_uname').value = u.uname;
  openModal('editModal');
}
function confirmDelete(id){
  if(confirm('Delete user '+id+'? This cannot be undone.')){
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}
</script>
</body></html>