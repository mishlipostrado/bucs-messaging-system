<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];
$id  = (int)($_GET['id'] ?? 0);

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

$res = $db->query("
    SELECT m.*, u1.fname sf, u1.lname sl, u2.fname rf, u2.lname rl
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id_no
    JOIN users u2 ON m.receiver  = u2.id_no
    WHERE m.mess_id=$id AND (m.receiver='$uid' OR m.sender_id='$uid')
    LIMIT 1");

if (!$res || $res->num_rows === 0) {
    $db->close();
    $pageTitle = 'Not Found – BUCS';
    include 'includes/head.php';
    include 'includes/navbar.php';
    echo '<div class="page-wrap"><div class="alert alert-error"><i class="fa fa-ban"></i> Message not found or access denied.</div></div>';
    echo '<script src="assets/js/app.js"></script></body></html>';
    exit;
}

$m = $res->fetch_assoc();

// Mark as read if I'm the receiver
if ($m['receiver'] === $uid && $m['status'] === 'sent') {
    $db->query("UPDATE messages SET status='read' WHERE mess_id=$id");
    $m['status'] = 'read';
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $db->query("DELETE FROM messages WHERE mess_id=$id AND (receiver='$uid' OR sender_id='$uid')");
    $db->close();
    header('Location: inbox.php'); exit;
}

$db->close();
$is_mine   = $m['sender_id'] === $uid;
$reply_to  = $is_mine ? $m['receiver'] : $m['sender_id'];

$pageTitle = 'Message – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap page-wrap-sm">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-envelope-open"></i> Message</h1>
    <div class="flex gap-8">
      <a href="<?= $is_mine ? 'sent.php' : 'inbox.php' ?>" class="btn btn-ghost">
        <i class="fa fa-arrow-left"></i> Back
      </a>
      <a href="compose.php?reply=<?= $reply_to ?>" class="btn btn-primary">
        <i class="fa fa-reply"></i> Reply
      </a>
    </div>
  </div>

  <div class="card">
    <!-- Header -->
    <div class="msg-view-header">
      <div class="avatar avatar-md avatar-teal">
        <?= strtoupper(substr($m['sf'],0,1).substr($m['sl'],0,1)) ?>
      </div>
      <div style="flex:1;min-width:0">
        <div class="font-600" style="font-size:15px;color:var(--navy)">
          <?= htmlspecialchars($m['sf'].' '.$m['sl']) ?>
        </div>
        <div class="text-sm text-muted mt-4">
          To: <strong><?= htmlspecialchars($m['rf'].' '.$m['rl']) ?></strong>
          &nbsp;·&nbsp;
          <?= date('F j, Y \a\t g:i A', strtotime($m['sent_at'])) ?>
        </div>
      </div>
      <span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span>
    </div>

    <!-- Body -->
    <div class="msg-view-body"><?= htmlspecialchars($m['message']) ?></div>

    <!-- Footer -->
    <div class="msg-view-footer">
      <a href="compose.php?reply=<?= $reply_to ?>" class="btn btn-ghost btn-sm">
        <i class="fa fa-reply"></i> Reply
      </a>
      <form method="POST" onsubmit="return confirm('Delete this message?')" style="display:inline">
        <button name="delete" class="btn btn-danger btn-sm">
          <i class="fa fa-trash"></i> Delete
        </button>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
