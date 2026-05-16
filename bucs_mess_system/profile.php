<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];
$msg = '';

$unread = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = sanitize($db, $_POST['fname'] ?? '');
    $mname = sanitize($db, $_POST['mname'] ?? '');
    $lname = sanitize($db, $_POST['lname'] ?? '');
    $uname = sanitize($db, $_POST['uname'] ?? '');
    $sets  = "fname='$fname',mname='$mname',lname='$lname',uname='$uname'";

    if (!empty($_POST['pwd'])) {
        if ($_POST['pwd'] !== ($_POST['cpwd'] ?? '')) {
            $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Passwords do not match.</div>';
        } elseif (strlen($_POST['pwd']) < 6) {
            $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Password must be at least 6 characters.</div>';
        } else {
            $sets .= ",pwd='" . md5($_POST['pwd']) . "'";
        }
    }

    if (!$msg) {
        $db->query("UPDATE users SET $sets WHERE id_no='$uid'");
        $_SESSION['user']['fname'] = $fname;
        $_SESSION['user']['lname'] = $lname;
        $_SESSION['user']['uname'] = $uname;
        $me = $_SESSION['user'];
        $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> Profile updated successfully.</div>';
    }
}

$user    = $db->query("SELECT * FROM users WHERE id_no='$uid' LIMIT 1")->fetch_assoc();
$classes = $db->query("
    SELECT c.classname FROM classes c
    JOIN user_classes uc ON c.class_id = uc.class_id
    WHERE uc.id_no='$uid' ORDER BY c.classname");

$db->close();

$pageTitle = 'My Profile – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap page-wrap-sm">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-user"></i> My Profile</h1>
  </div>

  <?= $msg ?>

  <!-- Profile header -->
  <div class="card mb-24">
    <div style="padding:24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:18px;flex-wrap:wrap">
      <div class="avatar avatar-lg avatar-navy">
        <?= strtoupper(substr($user['fname'],0,1).substr($user['lname'],0,1)) ?>
      </div>
      <div>
        <div class="serif" style="font-size:20px;color:var(--navy)">
          <?= htmlspecialchars($user['fname'].' '.($user['mname']?$user['mname'].' ':'').$user['lname']) ?>
        </div>
        <div class="text-muted text-sm mt-4">
          <code>@<?= htmlspecialchars($user['uname']) ?></code>
          &nbsp;·&nbsp; ID: <?= htmlspecialchars($user['id_no']) ?>
        </div>
      </div>
    </div>

    <div class="card-body">
      <form method="POST" novalidate>
        <div class="form-2col">
          <div class="form-group">
            <label class="form-label">First Name <span class="req">*</span></label>
            <input type="text" name="fname" class="form-control" required
                   value="<?= htmlspecialchars($user['fname']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" name="mname" class="form-control"
                   value="<?= htmlspecialchars($user['mname'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name <span class="req">*</span></label>
            <input type="text" name="lname" class="form-control" required
                   value="<?= htmlspecialchars($user['lname']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Username <span class="req">*</span></label>
            <input type="text" name="uname" class="form-control" required
                   value="<?= htmlspecialchars($user['uname']) ?>">
          </div>
        </div>

        <div class="form-divider">Change Password</div>

        <div class="form-2col">
          <div class="form-group">
            <label class="form-label">New Password <small>(leave blank to keep)</small></label>
            <div class="input-icon-wrap">
              <i class="fa fa-lock input-icon"></i>
              <input type="password" id="pwd" name="pwd" class="form-control" placeholder="New password">
              <button type="button" class="input-icon-right" onclick="togglePwd('pwd','eye1')">
                <i class="fa fa-eye" id="eye1"></i>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <div class="input-icon-wrap">
              <i class="fa fa-lock input-icon"></i>
              <input type="password" id="cpwd" name="cpwd" class="form-control" placeholder="Repeat password">
              <button type="button" class="input-icon-right" onclick="togglePwd('cpwd','eye2')">
                <i class="fa fa-eye" id="eye2"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="flex gap-8" style="justify-content:flex-end;margin-top:8px">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Enrolled classes -->
  <div class="card">
    <div class="card-header">
      <span class="card-header-title"><i class="fa fa-chalkboard"></i> Enrolled Classes</span>
    </div>
    <div class="card-body">
      <?php if ($classes->num_rows === 0): ?>
        <p class="text-muted text-sm" style="text-align:center;padding:12px 0">Not enrolled in any class.</p>
      <?php else: ?>
        <div class="class-grid">
          <?php while ($cl = $classes->fetch_assoc()): ?>
            <div class="flex-center gap-8" style="background:var(--teal-lt);padding:12px 14px;border-radius:9px">
              <i class="fa fa-chalkboard-user" style="color:var(--teal-dk)"></i>
              <span class="font-600 text-sm"><?= htmlspecialchars($cl['classname']) ?></span>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
