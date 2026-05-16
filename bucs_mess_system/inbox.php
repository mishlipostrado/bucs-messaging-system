<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

$filter = sanitize($db, $_GET['filter'] ?? 'all');
$where  = "m.receiver='$uid'";
if ($filter === 'unread') $where .= " AND m.status='sent'";
if ($filter === 'read')   $where .= " AND m.status='read'";

$msgs = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.sender_id = u.id_no
    WHERE $where ORDER BY m.sent_at DESC");

$db->close();

$pageTitle = 'Inbox – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-inbox"></i> Inbox</h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <!-- Filter tabs -->
  <div class="filter-tabs">
    <a href="?filter=all"    class="filter-tab <?= $filter==='all'   ?'active':'' ?>">All</a>
    <a href="?filter=unread" class="filter-tab <?= $filter==='unread'?'active':'' ?>">Unread</a>
    <a href="?filter=read"   class="filter-tab <?= $filter==='read'  ?'active':'' ?>">Read</a>
  </div>

  <div class="card">
    <?php if ($msgs->num_rows === 0): ?>
      <div class="empty-state"><i class="fa fa-inbox"></i><p>No messages here.</p></div>
    <?php else: ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr><th>From</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php while ($m = $msgs->fetch_assoc()):
            $is_unread = $m['status'] === 'sent'; ?>
            <tr <?= $is_unread ? 'style="font-weight:600;background:#fafcff"' : '' ?>>
              <td>
                <div class="person-row">
                  <?php if ($is_unread): ?>
                    <span class="unread-dot"></span>
                  <?php endif; ?>
                  <div class="avatar avatar-sm avatar-teal">
                    <?= strtoupper(substr($m['fname'],0,1).substr($m['lname'],0,1)) ?>
                  </div>
                  <span class="name"><?= htmlspecialchars($m['fname'].' '.$m['lname']) ?></span>
                </div>
              </td>
              <td class="tbl-preview">
                <?= htmlspecialchars(mb_substr($m['message'],0,65)) ?><?= mb_strlen($m['message'])>65?'…':'' ?>
              </td>
              <td><span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
              <td class="text-muted text-sm nowrap"><?= date('M d, Y g:i A', strtotime($m['sent_at'])) ?></td>
              <td>
                <div class="td-actions">
                  <a href="view_message.php?id=<?= $m['mess_id'] ?>" class="btn btn-ghost btn-sm" title="Read">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a href="compose.php?reply=<?= $m['sender_id'] ?>" class="btn btn-ghost btn-sm" title="Reply">
                    <i class="fa fa-reply"></i>
                  </a>
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

<script src="assets/js/app.js"></script>
</body>
</html>
