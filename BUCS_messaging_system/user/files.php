<?php
require_once '../includes/config.php';
$pageTitle = 'Files – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];
$msg = '';

// Upload
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='upload') {
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 0) {
        $orig = basename($_FILES['file_upload']['name']);
        $safe = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$orig);
        $dest = UPLOAD_DIR . $safe;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0775,true);
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $dest)) {
            $fn   = sanitize($db,$orig);
            $path = sanitize($db,'uploads/'.$safe);
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
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='delete') {
    $fid = (int)$_POST['file_id'];
    $row = $db->query("SELECT file FROM files WHERE file_id=$fid AND uploaded_by='$uid'")->fetch_assoc();
    if ($row) {
        @unlink('../'.$row['file']);
        $db->query("DELETE FROM files WHERE file_id=$fid AND uploaded_by='$uid'");
        $msg = '<div class="alert alert-success"><i class="fa fa-trash"></i> File deleted.</div>';
    }
}

// Show upload modal if ?action=upload
$open_upload = isset($_GET['action']) && $_GET['action']==='upload';

// All files (own + others)
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
        'pdf'=>['fa-file-pdf','#e74c3c'],
        'doc'=>['fa-file-word','#2980b9'],'docx'=>['fa-file-word','#2980b9'],
        'xls'=>['fa-file-excel','#27ae60'],'xlsx'=>['fa-file-excel','#27ae60'],
        'ppt'=>['fa-file-powerpoint','#e67e22'],'pptx'=>['fa-file-powerpoint','#e67e22'],
        'jpg'=>['fa-file-image','#9b59b6'],'jpeg'=>['fa-file-image','#9b59b6'],
        'png'=>['fa-file-image','#9b59b6'],'gif'=>['fa-file-image','#9b59b6'],
        'zip'=>['fa-file-zipper','#7f8c8d'],'rar'=>['fa-file-zipper','#7f8c8d'],
        'txt'=>['fa-file-lines','#95a5a6'],
    ];
    return $map[$ext] ?? ['fa-file','#95a5a6'];
}
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-folder-open"></i> Files</h1>
    <button class="btn btn-primary" onclick="openModal('uploadModal')">
      <i class="fa fa-upload"></i> Upload File
    </button>
  </div>

  <?= $msg ?>

  <!-- My Files -->
  <div class="card" style="margin-bottom:20px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
      <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-folder" style="color:var(--teal);margin-right:7px"></i>My Uploads</span>
      <span style="font-size:12px;color:var(--muted)"><?= $my_files->num_rows ?> file(s)</span>
    </div>
    <?php if ($my_files->num_rows === 0): ?>
    <div class="empty" style="padding:36px 20px"><i class="fa fa-folder-open"></i><p>No files uploaded yet.</p></div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>File</th><th>Path</th><th>Date Uploaded</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($f = $my_files->fetch_assoc()):
        [$icon,$clr] = fileIcon($f['filename']); ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa <?= $icon ?>" style="color:<?= $clr ?>;font-size:20px;flex-shrink:0"></i>
            <span style="font-weight:500"><?= htmlspecialchars($f['filename']) ?></span>
          </div>
        </td>
        <td><code style="font-size:11px;color:var(--muted);background:var(--bg);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($f['file']) ?></code></td>
        <td style="color:var(--muted);font-size:12px"><?= date('M d, Y', strtotime($f['uploaded_at'])) ?></td>
        <td>
          <form method="POST" onsubmit="return confirm('Delete this file?')" style="display:inline">
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

  <!-- Shared Files -->
  <div class="card">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
      <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-users" style="color:var(--teal);margin-right:7px"></i>Shared by Others</span>
    </div>
    <?php if ($all_files->num_rows === 0): ?>
    <div class="empty" style="padding:36px 20px"><i class="fa fa-folder-open"></i><p>No shared files available.</p></div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>File</th><th>Uploaded By</th><th>Date</th></tr></thead>
      <tbody>
      <?php while($f = $all_files->fetch_assoc()):
        [$icon,$clr] = fileIcon($f['filename']); ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa <?= $icon ?>" style="color:<?= $clr ?>;font-size:20px;flex-shrink:0"></i>
            <span style="font-weight:500"><?= htmlspecialchars($f['filename']) ?></span>
          </div>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:28px;height:28px;background:var(--teal-lt);border-radius:7px;display:grid;place-items:center;font-size:10px;font-weight:700;color:var(--teal-dk);">
              <?= strtoupper(substr($f['fname'],0,1).substr($f['lname'],0,1)) ?>
            </div>
            <?= htmlspecialchars($f['fname'].' '.$f['lname']) ?>
          </div>
        </td>
        <td style="color:var(--muted);font-size:12px"><?= date('M d, Y', strtotime($f['uploaded_at'])) ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal-overlay <?= $open_upload?'open':'' ?>">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="fa fa-upload"></i> Upload File</h3>
      <button onclick="closeModal('uploadModal')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <div class="form-group">
          <label>Choose File *</label>
          <input type="file" name="file_upload" required>
        </div>
        <div class="modal-ft">
          <button type="button" class="btn btn-ghost" onclick="closeModal('uploadModal')">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
document.addEventListener('click',function(e){if(e.target.classList.contains('modal-overlay'))e.target.classList.remove('open');});
</script>
</body></html>
