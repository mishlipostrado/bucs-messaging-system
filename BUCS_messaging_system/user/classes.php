<?php
require_once '../includes/config.php';
$pageTitle = 'My Classes – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];

$classes = $db->query("
    SELECT c.*, COUNT(uc2.id_no) member_count
    FROM classes c
    JOIN user_classes uc ON c.class_id = uc.class_id AND uc.id_no='$uid'
    LEFT JOIN user_classes uc2 ON c.class_id = uc2.class_id
    GROUP BY c.class_id ORDER BY c.classname");

$db->close();
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-chalkboard"></i> My Classes</h1>
  </div>

  <?php if ($classes->num_rows === 0): ?>
  <div class="card"><div class="empty"><i class="fa fa-chalkboard"></i><p>You are not enrolled in any class yet.</p></div></div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
    <?php while($cl = $classes->fetch_assoc()): ?>
    <div class="card" style="border:none;">
      <div style="background:var(--navy);padding:20px;border-radius:12px 12px 0 0;">
        <div style="width:42px;height:42px;background:var(--teal);border-radius:10px;display:grid;place-items:center;margin-bottom:12px;">
          <i class="fa fa-chalkboard-user" style="color:#fff;font-size:18px"></i>
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:18px;color:#fff"><?= htmlspecialchars($cl['classname']) ?></div>
      </div>
      <div style="padding:14px 18px;display:flex;align-items:center;gap:8px;">
        <i class="fa fa-users" style="color:var(--muted);font-size:12px"></i>
        <span style="font-size:13px;color:var(--muted)"><?= $cl['member_count'] ?> member<?= $cl['member_count']!=1?'s':'' ?></span>
        <span style="margin-left:auto;font-size:11px;color:var(--muted)">Since <?= date('M Y', strtotime($cl['created_at'])) ?></span>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>
</body></html>
