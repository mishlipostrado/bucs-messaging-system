<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

$stats = [
    'inbox'   => (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid'")->fetch_assoc()['c'],
    'sent'    => (int)$db->query("SELECT COUNT(*) c FROM messages WHERE sender_id='$uid'")->fetch_assoc()['c'],
    'files'   => (int)$db->query("SELECT COUNT(*) c FROM files WHERE uploaded_by='$uid'")->fetch_assoc()['c'],
    'classes' => (int)$db->query("SELECT COUNT(*) c FROM user_classes WHERE id_no='$uid'")->fetch_assoc()['c'],
];

$recent = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.sender_id = u.id_no
    WHERE m.receiver='$uid'
    ORDER BY m.sent_at DESC LIMIT 5");

$classes = $db->query("
    SELECT c.classname FROM classes c
    JOIN user_classes uc ON c.class_id = uc.class_id
    WHERE uc.id_no='$uid' ORDER BY c.classname");

$db->close();

$pageTitle = 'Dashboard – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap">

  <!-- Page heading -->
  <div class="page-head">
    <h1 class="page-title">
      <i class="fa fa-house"></i>
      Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>,
      <?= htmlspecialchars($me['fname']) ?>!
    </h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <!-- Stats -->
  <div class="stats-grid mb-24">
    <a href="inbox.php" class="stat-card">
      <div class="stat-icon stat-icon-blue"><i class="fa fa-inbox"></i></div>
      <div>
        <span class="stat-count"><?= $stats['inbox'] ?></span>
        <span class="stat-label">Inbox</span>
      </div>
    </a>
    <a href="sent.php" class="stat-card">
      <div class="stat-icon stat-icon-teal"><i class="fa fa-paper-plane"></i></div>
      <div>
        <span class="stat-count"><?= $stats['sent'] ?></span>
        <span class="stat-label">Sent</span>
      </div>
    </a>
    <a href="files.php" class="stat-card">
      <div class="stat-icon stat-icon-amber"><i class="fa fa-folder-open"></i></div>
      <div>
        <span class="stat-count"><?= $stats['files'] ?></span>
        <span class="stat-label">Files</span>
      </div>
    </a>
    <a href="classes.php" class="stat-card">
      <div class="stat-icon stat-icon-purple"><i class="fa fa-chalkboard"></i></div>
      <div>
        <span class="stat-count"><?= $stats['classes'] ?></span>
        <span class="stat-label">Classes</span>
      </div>
    </a>
  </div>

  <!-- Two-column layout -->
  <div class="dash-grid">

    <!-- Recent Messages -->
    <div class="card">
      <div class="card-header">
        <span class="card-header-title"><i class="fa fa-clock-rotate-left"></i> Recent Messages</span>
        <a href="inbox.php" class="btn btn-ghost btn-sm">View all</a>
      </div>

      <?php if ($recent->num_rows === 0): ?>
        <div class="empty-state"><i class="fa fa-inbox"></i><p>No messages yet.</p></div>
      <?php else: ?>
        <div class="tbl-wrap">
          <table class="tbl">
            <thead>
              <tr><th>From</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
            <?php while ($r = $recent->fetch_assoc()): ?>
              <tr>
                <td>
                  <div class="person-row">
                    <div class="avatar avatar-sm avatar-teal">
                      <?= strtoupper(substr($r['fname'],0,1).substr($r['lname'],0,1)) ?>
                    </div>
                    <span class="name"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></span>
                  </div>
                </td>
                <td class="tbl-preview">
                  <?= htmlspecialchars(mb_substr($r['message'],0,50)) ?><?= mb_strlen($r['message'])>50?'…':'' ?>
                </td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td class="text-muted text-sm nowrap"><?= date('M d, g:i A', strtotime($r['sent_at'])) ?></td>
                <td>
                  <a href="view_message.php?id=<?= $r['mess_id'] ?>" class="btn btn-ghost btn-sm">
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

    <!-- Sidebar -->
    <div class="dash-sidebar">

      <!-- Quick actions -->
      <div class="card">
        <div class="card-header">
          <span class="card-header-title"><i class="fa fa-bolt"></i> Quick Actions</span>
        </div>
        <div class="card-body">
          <a href="compose.php" class="btn btn-primary btn-full mb-18">
            <i class="fa fa-paper-plane"></i> New Message
          </a>
          <a href="files.php" class="btn btn-ghost btn-full">
            <i class="fa fa-upload"></i> Upload File
          </a>
        </div>
      </div>

      <!-- My classes -->
      <div class="card">
        <div class="card-header">
          <span class="card-header-title"><i class="fa fa-chalkboard"></i> My Classes</span>
        </div>
        <div class="card-body">
          <?php if ($classes->num_rows === 0): ?>
            <p class="text-muted text-sm" style="text-align:center;padding:10px 0">Not enrolled in any class.</p>
          <?php else: ?>
            <?php while ($cl = $classes->fetch_assoc()): ?>
              <div class="flex-center gap-8" style="padding:8px 10px;background:var(--bg);border-radius:8px;margin-bottom:6px;">
                <i class="fa fa-circle-dot" style="color:var(--teal);font-size:10px"></i>
                <span class="font-600 text-sm"><?= htmlspecialchars($cl['classname']) ?></span>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
