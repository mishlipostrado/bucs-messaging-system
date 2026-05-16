<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];
$msg = '';

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 0) {
        $orig = basename($_FILES['file_upload']['name']);
        $safe = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $orig);
        $dest = UPLOAD_DIR . $safe;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $dest)) {
            $fn   = sanitize($db, $orig);
            $path = sanitize($db, 'uploads/' . $safe);
            $db->query("INSERT INTO files (filename,file,uploaded_by) VALUES('$fn','$path','$uid')");
            $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> File uploaded successfully.</div>';
        } else {
            $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Upload failed. Check folder permissions.</div>';
        }
    } else {
        $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> No file selected or file too large.</div>';
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $fid = (int)$_POST['file_id'];
    $row = $db->query("SELECT file FROM files WHERE file_id=$fid AND uploaded_by='$uid'")->fetch_assoc();
    if ($row) {
        @unlink(UPLOAD_DIR . basename($row['file']));
        $db->query("DELETE FROM files WHERE file_id=$fid AND uploaded_by='$uid'");
        $msg = '<div class="alert alert-success"><i class="fa fa-trash"></i> File deleted.</div>';
    }
}

$my_files  = $db->query("SELECT * FROM files WHERE uploaded_by='$uid' ORDER BY uploaded_at DESC");
$all_files = $db->query("
    SELECT f.*, u.fname, u.lname
    FROM files f JOIN users u ON f.uploaded_by = u.id_no
    WHERE f.uploaded_by != '$uid'
    ORDER BY f.uploaded_at DESC");

$db->close();

// File icon helper
function fileIcon($name) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $map = [
        'pdf'  => ['fa-file-pdf',        '#e74c3c'],
        'doc'  => ['fa-file-word',        '#2980b9'],
        'docx' => ['fa-file-word',        '#2980b9'],
        'xls'  => ['fa-file-excel',       '#27ae60'],
        'xlsx' => ['fa-file-excel',       '#27ae60'],
        'ppt'  => ['fa-file-powerpoint',  '#e67e22'],
        'pptx' => ['fa-file-powerpoint',  '#e67e22'],
        'jpg'  => ['fa-file-image',       '#9b59b6'],
        'jpeg' => ['fa-file-image',       '#9b59b6'],
        'png'  => ['fa-file-image',       '#9b59b6'],
        'zip'  => ['fa-file-zipper',      '#7f8c8d'],
        'txt'  => ['fa-file-lines',       '#95a5a6'],
    ];
    return $map[$ext] ?? ['fa-file', '#95a5a6'];
}

$pageTitle = 'Files – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-folder-open"></i> Files</h1>
    <button class="btn btn-primary" onclick="openModal('uploadModal')">
      <i class="fa fa-upload"></i> Upload File
    </button>
  </div>

  <?= $msg ?>

  <!-- My uploads -->
  <div class="card mb-24">
    <div class="card-header">
      <span class="card-header-title"><i class="fa fa-folder"></i> My Uploads</span>
      <span class="card-header-meta"><?= $my_files->num_rows ?> file(s)</span>
    </div>

    <?php if ($my_files->num_rows === 0): ?>
      <div class="empty-state" style="padding:36px 20px">
        <i class="fa fa-folder-open"></i><p>No files uploaded yet.</p>
      </div>
    <?php else: ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>File</th><th>Path</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody>
          <?php while ($f = $my_files->fetch_assoc()):
            [$icon, $clr] = fileIcon($f['filename']); ?>
            <tr>
              <td>
                <div class="file-row">
                  <i class="fa <?= $icon ?> file-icon" style="color:<?= $clr ?>"></i>
                  <span class="font-600"><?= htmlspecialchars($f['filename']) ?></span>
                </div>
              </td>
              <td><code><?= htmlspecialchars($f['file']) ?></code></td>
              <td class="text-muted text-sm"><?= date('M d, Y', strtotime($f['uploaded_at'])) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this file?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="file_id" value="<?= $f['file_id'] ?>">
                  <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Shared by others -->
  <div class="card">
    <div class="card-header">
      <span class="card-header-title"><i class="fa fa-users"></i> Shared by Others</span>
    </div>

    <?php if ($all_files->num_rows === 0): ?>
      <div class="empty-state" style="padding:36px 20px">
        <i class="fa fa-folder-open"></i><p>No shared files available.</p>
      </div>
    <?php else: ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>File</th><th>Uploaded By</th><th>Date</th></tr></thead>
          <tbody>
          <?php while ($f = $all_files->fetch_assoc()):
            [$icon, $clr] = fileIcon($f['filename']); ?>
            <tr>
              <td>
                <div class="file-row">
                  <i class="fa <?= $icon ?> file-icon" style="color:<?= $clr ?>"></i>
                  <span class="font-600"><?= htmlspecialchars($f['filename']) ?></span>
                </div>
              </td>
              <td>
                <div class="person-row">
                  <div class="avatar avatar-sm avatar-teal">
                    <?= strtoupper(substr($f['fname'],0,1).substr($f['lname'],0,1)) ?>
                  </div>
                  <?= htmlspecialchars($f['fname'].' '.$f['lname']) ?>
                </div>
              </td>
              <td class="text-muted text-sm"><?= date('M d, Y', strtotime($f['uploaded_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa fa-upload"></i> Upload File</h3>
      <button class="modal-close" onclick="closeModal('uploadModal')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <div class="form-group">
          <label class="form-label">Choose File <span class="req">*</span></label>
          <input type="file" name="file_upload" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeModal('uploadModal')">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
