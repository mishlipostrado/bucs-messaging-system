<?php
require_once '../includes/config.php';
$pageTitle = 'Sent – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];

$msgs = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.receiver = u.id_no
    WHERE m.sender_id='$uid'
    ORDER BY m.sent_at DESC");
$db->close();
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-paper-plane"></i> Sent Messages</h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <div class="card">
    <?php if ($msgs->num_rows === 0): ?>
    <div class="empty"><i class="fa fa-paper-plane"></i><p>No sent messages yet.</p></div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>To</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($m = $msgs->fetch_assoc()): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:9px;">
            <div style="width:32px;height:32px;background:#f3e8ff;border-radius:8px;display:grid;place-items:center;flex-shrink:0;font-size:11px;font-weight:700;color:#7c3aed">
              <?= strtoupper(substr($m['fname'],0,1).substr($m['lname'],0,1)) ?>
            </div>
            <?= htmlspecialchars($m['fname'].' '.$m['lname']) ?>
          </div>
        </td>
        <td style="color:var(--muted);max-width:280px;">
          <?= htmlspecialchars(mb_substr($m['message'],0,70)) ?><?= mb_strlen($m['message'])>70?'…':'' ?>
        </td>
        <td><span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?= date('M d, Y g:i A', strtotime($m['sent_at'])) ?></td>
        <td>
          <a href="view_message.php?id=<?= $m['mess_id'] ?>" class="btn btn-ghost btn-sm"><i class="fa fa-eye"></i></a>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
</div>
</body></html>
