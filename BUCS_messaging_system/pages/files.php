<?php
// pages/files.php
require_once '../includes/config.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $uploader = sanitize($db,$_POST['uploaded_by']);
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error']===0) {
            $orig  = basename($_FILES['file_upload']['name']);
            $safe  = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$orig);
            $dest  = UPLOAD_DIR . $safe;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $dest)) {
                $fn   = sanitize($db,$orig);
                $path = sanitize($db,'uploads/'.$safe);
                $db->query("INSERT INTO files (filename,file,uploaded_by) VALUES('$fn','$path','$uploader')");
                $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> File uploaded.</div>';
            } else {
                $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Upload failed.</div>';
            }
        } else {
            // Demo / no actual upload on this server – just record manually
            $fn   = sanitize($db,$_POST['filename']);
            $path = sanitize($db,'uploads/'.time().'_'.preg_replace('/\s/','',$fn));
            $db->query("INSERT INTO files (filename,file,uploaded_by) VALUES('$fn','$path','$uploader')");
            $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> File record added.</div>';
        }
    }

    if ($action === 'update') {
        $id = (int)$_POST['file_id'];
        $fn = sanitize($db,$_POST['filename']);
        $db->query("UPDATE files SET filename='$fn' WHERE file_id=$id");
        $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> Filename updated.</div>';
    }

    if ($action === 'delete') {
        $id  = (int)$_POST['file_id'];
        $row = $db->query("SELECT file FROM files WHERE file_id=$id")->fetch_assoc();
        if ($row && file_exists('../'.$row['file'])) @unlink('../'.$row['file']);
        $db->query("DELETE FROM files WHERE file_id=$id");
        $msg = '<div class="alert alert-success"><i class="fa fa-trash"></i> File deleted.</div>';
    }
}

$files = $db->query("
    SELECT f.*, u.fname, u.lname
    FROM files f JOIN users u ON f.uploaded_by = u.id_no
    ORDER BY f.uploaded_at DESC");
$users = $db->query("SELECT id_no, fname, lname FROM users ORDER BY lname");
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Files – BUCS</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><i class="fa fa-file-alt"></i> Files Management</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa fa-upload"></i> Upload File</button>
  </div>
  <?= $msg ?>
  <div class="section-card">
    <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID</th><th>Filename</th><th>Uploaded By</th><th>Path</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($f = $files->fetch_assoc()): ?>
      <tr>
        <td><?= $f['file_id'] ?></td>
        <td><i class="fa fa-file-circle-check" style="color:var(--teal)"></i> <?= htmlspecialchars($f['filename']) ?></td>
        <td><?= htmlspecialchars($f['fname'].' '.$f['lname']) ?></td>
        <td><code style="font-size:.75rem;opacity:.7"><?= htmlspecialchars($f['file']) ?></code></td>
        <td><?= date('M d, Y', strtotime($f['uploaded_at'])) ?></td>
        <td class="action-cell">
          <button class="btn btn-sm btn-edit" onclick='editFile(<?= json_encode($f) ?>)'><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?= $f['file_id'] ?>)"><i class="fa fa-trash"></i></button>
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
      <div class="modal-header"><h3><i class="fa fa-upload"></i> Upload File</h3><button onclick="closeModal('addModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create">
        <div class="form-group"><label>Uploaded By *</label>
          <select name="uploaded_by" required>
            <option value="">Select User...</option>
            <?php $users->data_seek(0); while($u=$users->fetch_assoc()): ?>
            <option value="<?= $u['id_no'] ?>"><?= htmlspecialchars($u['fname'].' '.$u['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>File *</label><input type="file" name="file_upload"></div>
        <div class="form-group"><label>Filename (manual / if no upload)</label><input type="text" name="filename" placeholder="document.pdf"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button></div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editModal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header"><h3><i class="fa fa-pen"></i> Rename File</h3><button onclick="closeModal('editModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="file_id" id="edit_file_id">
        <div class="form-group"><label>Filename *</label><input type="text" name="filename" id="edit_filename" required></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button></div>
      </form>
    </div>
  </div>

  <form id="deleteForm" method="POST" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="file_id" id="delete_id">
  </form>
</main>
<script src="../assets/js/app.js"></script>
<script>
function editFile(f){
  document.getElementById('edit_file_id').value = f.file_id;
  document.getElementById('edit_filename').value = f.filename;
  openModal('editModal');
}
function confirmDelete(id){
  if(confirm('Delete this file record?')){
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}
</script>
</body></html>