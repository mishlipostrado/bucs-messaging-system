<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

$search = sanitize($db, $_GET['search'] ?? '');
$where  = "m.sender_id='$uid'";
if ($search !== '') {
    $where .= " AND (CONCAT(u.fname, ' ', u.lname) LIKE '%$search%' OR m.message LIKE '%$search%')";
}

$msgs = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.receiver = u.id_no
    WHERE $where
    ORDER BY m.sent_at DESC");

$db->close();

$pageTitle = 'Sent – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-paper-plane"></i> Sent Messages</h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <form action="" method="get" class="search-row">
    <input type="search" name="search" class="form-control" placeholder="Search recipient or message..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
  </form>

  <div class="card">
    <?php if ($msgs->num_rows === 0): ?>
      <div class="empty-state"><i class="fa fa-paper-plane"></i><p>No sent messages yet.</p></div>
    <?php else: ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr><th>To</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php while ($m = $msgs->fetch_assoc()): ?>
            <tr>
              <td>
                <div class="person-row">
                  <div class="avatar avatar-sm avatar-purple">
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
                <a href="view_message.php?id=<?= $m['mess_id'] ?>" class="btn btn-ghost btn-sm">
                  <i class="fa fa-eye"></i>
                </a>
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
