<?php
require_once '../includes/config.php';
$pageTitle = 'Message – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];
$id  = (int)($_GET['id'] ?? 0);

// Fetch message — only if user is sender or receiver
$res = $db->query("
    SELECT m.*, u1.fname sf, u1.lname sl, u2.fname rf, u2.lname rl
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id_no
    JOIN users u2 ON m.receiver  = u2.id_no
    WHERE m.mess_id=$id AND (m.receiver='$uid' OR m.sender_id='$uid')
    LIMIT 1");

if (!$res || $res->num_rows === 0) {
    echo '<div class="page-wrap"><div class="alert alert-error"><i class="fa fa-ban"></i> Message not found or access denied.</div></div></body></html>';
    $db->close(); exit;
}

$m = $res->fetch_assoc();

// Mark as read if I'm the receiver
if ($m['receiver'] === $uid && $m['status'] === 'sent') {
    $db->query("UPDATE messages SET status='read' WHERE mess_id=$id");
    $m['status'] = 'read';
}

// Handle delete
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete'])) {
    $db->query("DELETE FROM messages WHERE mess_id=$id");
    $db->close();
    header('Location: inbox.php'); exit;
}

$db->close();
$is_mine = $m['sender_id'] === $uid;
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-envelope-open"></i> Message</h1>
    <div style="display:flex;gap:8px;">
      <a href="<?= $is_mine ? 'sent.php' : 'inbox.php' ?>" class="btn btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="compose.php?reply=<?= $is_mine ? $m['receiver'] : $m['sender_id'] ?>" class="btn btn-primary">
        <i class="fa fa-reply"></i> Reply
      </a>
    </div>
  </div>

  <div class="card">
    <!-- Message header -->
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap;">
      <div style="width:44px;height:44px;background:var(--teal-lt);border-radius:12px;display:grid;place-items:center;flex-shrink:0;font-size:16px;font-weight:700;color:var(--teal-dk)">
        <?= strtoupper(substr($m['sf'],0,1).substr($m['sl'],0,1)) ?>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:600;font-size:15px;color:var(--navy)">
          <?= htmlspecialchars($m['sf'].' '.$m['sl']) ?>
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:3px;">
          <span>To: <strong><?= htmlspecialchars($m['rf'].' '.$m['rl']) ?></strong></span>
          &nbsp;·&nbsp;
          <span><?= date('F j, Y \a\t g:i A', strtotime($m['sent_at'])) ?></span>
        </div>
      </div>
      <span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span>
    </div>

    <!-- Message body -->
    <div style="padding:28px 24px;min-height:160px;">
      <p style="font-size:15px;line-height:1.75;color:var(--text);white-space:pre-wrap;"><?= htmlspecialchars($m['message']) ?></p>
    </div>

    <!-- Actions -->
    <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;">
      <a href="compose.php?reply=<?= $is_mine ? $m['receiver'] : $m['sender_id'] ?>"
         class="btn btn-ghost btn-sm"><i class="fa fa-reply"></i> Reply</a>
      <?php if ($m['receiver'] === $uid || $m['sender_id'] === $uid): ?>
      <form method="POST" onsubmit="return confirm('Delete this message?')">
        <button name="delete" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
</body></html>
