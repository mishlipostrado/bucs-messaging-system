<?php
require_once '../includes/config.php';
$pageTitle = 'Dashboard – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];

// Stats
$inbox_count  = $db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid'")->fetch_assoc()['c'];
$sent_count   = $db->query("SELECT COUNT(*) c FROM messages WHERE sender_id='$uid'")->fetch_assoc()['c'];
$files_count  = $db->query("SELECT COUNT(*) c FROM files WHERE uploaded_by='$uid'")->fetch_assoc()['c'];
$class_count  = $db->query("SELECT COUNT(*) c FROM user_classes WHERE id_no='$uid'")->fetch_assoc()['c'];

// Recent inbox (last 5)
$recent = $db->query("
    SELECT m.*, u.fname, u.lname
    FROM messages m JOIN users u ON m.sender_id = u.id_no
    WHERE m.receiver='$uid'
    ORDER BY m.sent_at DESC LIMIT 5");

// My classes
$classes = $db->query("
    SELECT c.classname FROM classes c
    JOIN user_classes uc ON c.class_id = uc.class_id
    WHERE uc.id_no='$uid'");

$db->close();
?>

<div class="page-wrap">
  <div class="page-head">
    <h1><i class="fa fa-house"></i>
      Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>,
      <?= htmlspecialchars($me['fname']) ?>!
    </h1>
    <a href="compose.php" class="btn btn-primary"><i class="fa fa-pen-to-square"></i> Compose</a>
  </div>

  <!-- STATS -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <?php
    $stats = [
      ['Inbox',   $inbox_count, 'fa-inbox',       '#1a3a6b', '#e3f0ff'],
      ['Sent',    $sent_count,  'fa-paper-plane',  '#0f6e56', '#e1f5f3'],
      ['Files',   $files_count, 'fa-folder-open',  '#7c4f00', '#fff3e0'],
      ['Classes', $class_count, 'fa-chalkboard',   '#4a1f6e', '#f3e8ff'],
    ];
    foreach($stats as [$label,$count,$icon,$clr,$bg]):
    ?>
    <div class="card" style="border:none;">
      <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:18px;">
        <div style="width:44px;height:44px;background:<?= $bg ?>;border-radius:10px;display:grid;place-items:center;flex-shrink:0;">
          <i class="fa <?= $icon ?>" style="color:<?= $clr ?>;font-size:18px;"></i>
        </div>
        <div>
          <div style="font-family:'DM Serif Display',serif;font-size:26px;color:var(--navy);line-height:1"><?= $count ?></div>
          <div style="font-size:12px;color:var(--muted);font-weight:500;text-transform:uppercase;letter-spacing:.06em"><?= $label ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start;">

    <!-- RECENT MESSAGES -->
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
        <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-clock-rotate-left" style="color:var(--teal);margin-right:7px"></i>Recent Messages</span>
        <a href="inbox.php" style="font-size:12px;color:var(--teal);font-weight:600;text-decoration:none;">View all →</a>
      </div>
      <?php if ($recent->num_rows === 0): ?>
      <div class="empty"><i class="fa fa-inbox"></i><p>No messages yet.</p></div>
      <?php else: ?>
      <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>From</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php while($r = $recent->fetch_assoc()): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:9px;">
              <div style="width:30px;height:30px;background:var(--teal-lt);border-radius:8px;display:grid;place-items:center;flex-shrink:0;font-size:12px;font-weight:700;color:var(--teal-dk)">
                <?= strtoupper(substr($r['fname'],0,1).substr($r['lname'],0,1)) ?>
              </div>
              <span style="font-weight:500"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></span>
            </div>
          </td>
          <td style="color:var(--muted);max-width:200px;">
            <?= htmlspecialchars(mb_substr($r['message'],0,50)) ?><?= mb_strlen($r['message'])>50?'…':'' ?>
          </td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?= date('M d, g:i A', strtotime($r['sent_at'])) ?></td>
          <td><a href="view_message.php?id=<?= $r['mess_id'] ?>" class="btn btn-ghost btn-sm"><i class="fa fa-eye"></i></a></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      </div>
      <?php endif; ?>
    </div>

    <!-- SIDEBAR: MY CLASSES + QUICK COMPOSE -->
    <div style="display:flex;flex-direction:column;gap:14px;">

      <!-- Quick Compose -->
      <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
          <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-pen" style="color:var(--teal);margin-right:7px"></i>Quick Message</span>
        </div>
        <div class="card-body">
          <a href="compose.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:8px;">
            <i class="fa fa-paper-plane"></i> New Message
          </a>
          <a href="files.php?action=upload" class="btn btn-ghost" style="width:100%;justify-content:center;">
            <i class="fa fa-upload"></i> Upload File
          </a>
        </div>
      </div>

      <!-- My Classes -->
      <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
          <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-chalkboard" style="color:var(--teal);margin-right:7px"></i>My Classes</span>
        </div>
        <div class="card-body" style="padding:12px;">
          <?php if ($classes->num_rows === 0): ?>
          <p style="color:var(--muted);font-size:13px;text-align:center;padding:12px 0">Not enrolled in any class.</p>
          <?php else: ?>
          <?php while($cl = $classes->fetch_assoc()): ?>
          <div style="display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;margin-bottom:4px;background:var(--bg);">
            <i class="fa fa-circle-dot" style="color:var(--teal);font-size:10px"></i>
            <span style="font-size:13px;font-weight:500"><?= htmlspecialchars($cl['classname']) ?></span>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
// Close dropdown on outside click
document.addEventListener('click', function(e){
  if(!e.target.closest('.nav-user')) {
    document.querySelectorAll('.user-dropdown').forEach(d => d.style.display='');
  }
});
</script>
</body></html>
