<?php
// pages/messages.php
require_once '../includes/config.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $sender   = sanitize($db,$_POST['sender_id']);
        $receiver = sanitize($db,$_POST['receiver']);
        $message  = sanitize($db,$_POST['message']);
        $status   = 'sent';
        $sql = "INSERT INTO messages (sender_id,receiver,message,status) VALUES('$sender','$receiver','$message','$status')";
        $msg = $db->query($sql)
             ? '<div class="alert alert-success"><i class="fa fa-check"></i> Message sent.</div>'
             : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }

    if ($action === 'update') {
        $id      = (int)$_POST['mess_id'];
        $message = sanitize($db,$_POST['message']);
        $status  = sanitize($db,$_POST['status']);
        $msg = $db->query("UPDATE messages SET message='$message',status='$status' WHERE mess_id=$id")
             ? '<div class="alert alert-success"><i class="fa fa-check"></i> Message updated.</div>'
             : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }

    if ($action === 'delete') {
        $id  = (int)$_POST['mess_id'];
        $msg = $db->query("DELETE FROM messages WHERE mess_id=$id")
             ? '<div class="alert alert-success"><i class="fa fa-trash"></i> Message deleted.</div>'
             : '<div class="alert alert-error"><i class="fa fa-xmark"></i> '.$db->error.'</div>';
    }
}

$messages = $db->query("
    SELECT m.*, u1.fname s_fname, u1.lname s_lname, u2.fname r_fname, u2.lname r_lname
    FROM messages m
    JOIN users u1 ON m.sender_id  = u1.id_no
    JOIN users u2 ON m.receiver   = u2.id_no
    ORDER BY m.sent_at DESC");
$users = $db->query("SELECT id_no,fname,lname FROM users ORDER BY lname");
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Messages – BUCS</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><i class="fa fa-envelope"></i> Messages Management</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa fa-paper-plane"></i> New Message</button>
  </div>
  <?= $msg ?>
  <div class="section-card">
    <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>ID</th><th>From</th><th>To</th><th>Message</th><th>Status</th><th>Sent</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($m = $messages->fetch_assoc()): ?>
      <tr>
        <td><?= $m['mess_id'] ?></td>
        <td><?= htmlspecialchars($m['s_fname'].' '.$m['s_lname']) ?></td>
        <td><?= htmlspecialchars($m['r_fname'].' '.$m['r_lname']) ?></td>
        <td class="msg-preview"><?= htmlspecialchars(substr($m['message'],0,55)) ?><?= strlen($m['message'])>55?'…':'' ?></td>
        <td><span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
        <td><?= date('M d, Y g:i A', strtotime($m['sent_at'])) ?></td>
        <td class="action-cell">
          <button class="btn btn-sm btn-edit" onclick='editMsg(<?= json_encode($m) ?>)'><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?= $m['mess_id'] ?>)"><i class="fa fa-trash"></i></button>
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
      <div class="modal-header"><h3><i class="fa fa-paper-plane"></i> Send Message</h3><button onclick="closeModal('addModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group">
            <label>Sender *</label>
            <select name="sender_id" required>
              <option value="">Select Sender...</option>
              <?php $users->data_seek(0); while($u=$users->fetch_assoc()): ?>
              <option value="<?= $u['id_no'] ?>"><?= htmlspecialchars($u['fname'].' '.$u['lname']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Receiver *</label>
            <select name="receiver" required>
              <option value="">Select Receiver...</option>
              <?php $users->data_seek(0); while($u=$users->fetch_assoc()): ?>
              <option value="<?= $u['id_no'] ?>"><?= htmlspecialchars($u['fname'].' '.$u['lname']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-group"><label>Message *</label><textarea name="message" rows="4" required placeholder="Type your message here..."></textarea></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send</button></div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editModal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header"><h3><i class="fa fa-pen"></i> Edit Message</h3><button onclick="closeModal('editModal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="mess_id" id="edit_mess_id">
        <div class="form-group"><label>Message *</label><textarea name="message" id="edit_message" rows="4" required></textarea></div>
        <div class="form-group"><label>Status *</label>
          <select name="status" id="edit_status">
            <option value="sent">Sent</option>
            <option value="read">Read</option>
            <option value="deleted">Deleted</option>
          </select>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button></div>
      </form>
    </div>
  </div>

  <form id="deleteForm" method="POST" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="mess_id" id="delete_id">
  </form>
</main>
<script src="../assets/js/app.js"></script>
<script>
function editMsg(m){
  document.getElementById('edit_mess_id').value = m.mess_id;
  document.getElementById('edit_message').value = m.message;
  document.getElementById('edit_status').value  = m.status;
  openModal('editModal');
}
function confirmDelete(id){
  if(confirm('Delete this message?')){
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}
</script>
</body></html>