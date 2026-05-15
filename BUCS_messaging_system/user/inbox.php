<?php
require_once '../includes/config.php';
$pageTitle = 'Inbox – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];

// Filter
$filter = sanitize($db, $_GET['filter'] ?? 'all');
$where  = "m.receiver='$uid'";
if ($filter === 'unread') $where .= " AND m.status='sent'";
if ($filter === 'read')   $where .= " AND m.status='read'";

$msgs = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.sender_id = u.id_no
    WHERE $where ORDER BY m.sent_at DESC");

$db->close();
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-inbox"></i> Inbox</h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <!-- Filter tabs -->
  <div style="display:flex;gap:6px;margin-bottom:18px;">
    <?php foreach(['all'=>'All','unread'=>'Unread','read'=>'Read'] as $k=>$v): ?>
    <a href="?filter=<?= $k ?>"
       style="padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
              background:<?= $filter===$k?'var(--navy)':'var(--white)' ?>;
              color:<?= $filter===$k?'#fff':'var(--muted)' ?>;
              border:1.5px solid <?= $filter===$k?'var(--navy)':'var(--border)' ?>;">
      <?= $v ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <?php if ($msgs->num_rows === 0): ?>
    <div class="empty"><i class="fa fa-inbox"></i><p>No messages here.</p></div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>From</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while($m = $msgs->fetch_assoc()): $unread = $m['status']==='sent'; ?>
      <tr style="<?= $unread?'background:#fafcff;font-weight:500':'' ?>">
        <td>
          <div style="display:flex;align-items:center;gap:9px;">
            <?php if($unread): ?><span style="width:8px;height:8px;background:var(--teal);border-radius:50%;flex-shrink:0;display:inline-block"></span><?php endif; ?>
            <div style="width:32px;height:32px;background:var(--teal-lt);border-radius:8px;display:grid;place-items:center;flex-shrink:0;font-size:11px;font-weight:700;color:var(--teal-dk)">
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
          <div class="act">
            <a href="view_message.php?id=<?= $m['mess_id'] ?>" class="btn btn-ghost btn-sm" title="Read"><i class="fa fa-eye"></i></a>
            <a href="compose.php?reply=<?= $m['sender_id'] ?>" class="btn btn-ghost btn-sm" title="Reply"><i class="fa fa-reply"></i></a>
          </div>
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
