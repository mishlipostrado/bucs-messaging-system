<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];

$unread  = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

$classes = $db->query("
    SELECT c.*, COUNT(uc2.id_no) member_count
    FROM classes c
    JOIN user_classes uc  ON c.class_id = uc.class_id  AND uc.id_no='$uid'
    LEFT JOIN user_classes uc2 ON c.class_id = uc2.class_id
    GROUP BY c.class_id ORDER BY c.classname");

$db->close();

$pageTitle = 'My Classes – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-chalkboard"></i> My Classes</h1>
  </div>

  <?php if ($classes->num_rows === 0): ?>
    <div class="card">
      <div class="empty-state"><i class="fa fa-chalkboard"></i><p>You are not enrolled in any class yet.</p></div>
    </div>
  <?php else: ?>
    <div class="class-grid">
      <?php while ($cl = $classes->fetch_assoc()): ?>
        <div class="class-card">
          <div class="class-card-top">
            <div class="avatar avatar-md" style="background:var(--teal);color:#fff;margin-bottom:12px">
              <i class="fa fa-chalkboard-user"></i>
            </div>
            <div class="serif" style="font-size:18px;color:#fff"><?= htmlspecialchars($cl['classname']) ?></div>
          </div>
          <div class="class-card-body">
            <i class="fa fa-users text-muted"></i>
            <span><?= $cl['member_count'] ?> member<?= $cl['member_count'] != 1 ? 's' : '' ?></span>
            <span class="text-muted text-xs" style="margin-left:auto">
              Since <?= date('M Y', strtotime($cl['created_at'])) ?>
            </span>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
