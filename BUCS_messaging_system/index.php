<?php
// index.php  –  BUCS Messaging System Dashboard
require_once 'includes/config.php';
$db = getDB();

// ── quick stats ────────────────────────────────────────────────
$stats = [];
foreach (['users'=>'users','files'=>'files','messages'=>'messages','classes'=>'classes'] as $k=>$t){
    $r = $db->query("SELECT COUNT(*) c FROM $t");
    $stats[$k] = $r->fetch_assoc()['c'];
}
$recent_msgs = $db->query("
    SELECT m.*, u1.fname s_fname, u1.lname s_lname, u2.fname r_fname, u2.lname r_lname
    FROM messages m
    JOIN users u1 ON m.sender_id   = u1.id_no
    JOIN users u2 ON m.receiver    = u2.id_no
    ORDER BY m.sent_at DESC LIMIT 5");
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BUCS Messaging System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><i class="fa fa-gauge-high"></i> Dashboard</h1>
    <span class="topbar-date"><?= date('l, F j, Y') ?></span>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <?php
    $cards = [
      ['Users',    $stats['users'],    'fa-users',        'card-blue',   'pages/users.php'],
      ['Files',    $stats['files'],    'fa-file-alt',     'card-teal',   'pages/files.php'],
      ['Messages', $stats['messages'], 'fa-envelope',     'card-navy',   'pages/messages.php'],
      ['Classes',  $stats['classes'],  'fa-chalkboard',   'card-slate',  'pages/classes.php'],
    ];
    foreach($cards as [$label,$count,$icon,$cls,$link]):
    ?>
    <a href="<?= $link ?>" class="stat-card <?= $cls ?>">
      <div class="stat-icon"><i class="fa <?= $icon ?>"></i></div>
      <div class="stat-body">
        <span class="stat-count"><?= $count ?></span>
        <span class="stat-label"><?= $label ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Recent Messages -->
  <div class="section-card">
    <div class="section-header">
      <h2><i class="fa fa-clock-rotate-left"></i> Recent Messages</h2>
      <a href="pages/messages.php" class="btn-link">View All →</a>
    </div>
    <div class="table-wrap">
    <table class="data-table">
      <thead><tr>
        <th>#</th><th>From</th><th>To</th><th>Message</th><th>Status</th><th>Date</th>
      </tr></thead>
      <tbody>
      <?php while($row = $recent_msgs->fetch_assoc()): ?>
      <tr>
        <td><?= $row['mess_id'] ?></td>
        <td><?= htmlspecialchars($row['s_fname'].' '.$row['s_lname']) ?></td>
        <td><?= htmlspecialchars($row['r_fname'].' '.$row['r_lname']) ?></td>
        <td class="msg-preview"><?= htmlspecialchars(substr($row['message'],0,60)) ?>…</td>
        <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
        <td><?= date('M d, Y g:i A', strtotime($row['sent_at'])) ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>
</main>
</body>
</html>