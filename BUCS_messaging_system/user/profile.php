<?php
require_once '../includes/config.php';
$pageTitle = 'My Profile – BUCS Messaging';
include 'layout.php';

$db  = getDB();
$uid = $me['id_no'];
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fname = sanitize($db,$_POST['fname']??'');
    $mname = sanitize($db,$_POST['mname']??'');
    $lname = sanitize($db,$_POST['lname']??'');
    $uname = sanitize($db,$_POST['uname']??'');
    $sets  = "fname='$fname',mname='$mname',lname='$lname',uname='$uname'";
    if (!empty($_POST['pwd'])) {
        if ($_POST['pwd'] !== $_POST['cpwd']) {
            $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Passwords do not match.</div>';
        } elseif (strlen($_POST['pwd']) < 6) {
            $msg = '<div class="alert alert-error"><i class="fa fa-xmark"></i> Password must be at least 6 characters.</div>';
        } else {
            $sets .= ",pwd='".md5($_POST['pwd'])."'";
        }
    }
    if (!$msg) {
        $db->query("UPDATE users SET $sets WHERE id_no='$uid'");
        // Update session
        $_SESSION['user']['fname'] = $fname;
        $_SESSION['user']['lname'] = $lname;
        $_SESSION['user']['uname'] = $uname;
        $msg = '<div class="alert alert-success"><i class="fa fa-check"></i> Profile updated successfully.</div>';
    }
}

$user = $db->query("SELECT * FROM users WHERE id_no='$uid' LIMIT 1")->fetch_assoc();

// Classes
$classes = $db->query("
    SELECT c.* FROM classes c
    JOIN user_classes uc ON c.class_id = uc.class_id
    WHERE uc.id_no='$uid'");

$db->close();
?>

<div class="page-wrap" style="max-width:700px">
  <div class="page-head">
    <h1><i class="fa fa-user"></i> My Profile</h1>
  </div>

  <?= $msg ?>

  <!-- Profile card -->
  <div class="card" style="margin-bottom:20px">
    <div style="padding:24px;display:flex;align-items:center;gap:18px;border-bottom:1px solid var(--border);">
      <div style="width:64px;height:64px;background:var(--navy);border-radius:16px;display:grid;place-items:center;font-size:24px;font-weight:700;color:var(--teal);flex-shrink:0;">
        <?= strtoupper(substr($user['fname'],0,1).substr($user['lname'],0,1)) ?>
      </div>
      <div>
        <div style="font-family:'DM Serif Display',serif;font-size:20px;color:var(--navy)">
          <?= htmlspecialchars($user['fname'].' '.$user['mname'].' '.$user['lname']) ?>
        </div>
        <div style="font-size:13px;color:var(--muted);margin-top:3px;">
          <code style="background:var(--bg);padding:2px 8px;border-radius:5px;">@<?= htmlspecialchars($user['uname']) ?></code>
          &nbsp;·&nbsp; ID: <?= htmlspecialchars($user['id_no']) ?>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" novalidate>
        <div class="form-2col">
          <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="fname" required value="<?= htmlspecialchars($user['fname']) ?>">
          </div>
          <div class="form-group">
            <label>Middle Name</label>
            <input type="text" name="mname" value="<?= htmlspecialchars($user['mname'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="lname" required value="<?= htmlspecialchars($user['lname']) ?>">
          </div>
          <div class="form-group">
            <label>Username *</label>
            <input type="text" name="uname" required value="<?= htmlspecialchars($user['uname']) ?>">
          </div>
          <div class="form-group">
            <label>New Password <small style="font-weight:400;text-transform:none">(leave blank to keep)</small></label>
            <input type="password" name="pwd" placeholder="New password">
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="cpwd" placeholder="Repeat new password">
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:4px;">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- My Classes -->
  <div class="card">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
      <span style="font-weight:600;color:var(--navy);font-size:14px;"><i class="fa fa-chalkboard" style="color:var(--teal);margin-right:7px"></i>My Classes</span>
    </div>
    <div class="card-body">
      <?php if ($classes->num_rows === 0): ?>
      <p style="color:var(--muted);text-align:center;padding:20px 0;font-size:13px">You are not enrolled in any class.</p>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
        <?php while($cl = $classes->fetch_assoc()): ?>
        <div style="background:var(--teal-lt);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:9px;">
          <i class="fa fa-chalkboard-user" style="color:var(--teal-dk);font-size:15px"></i>
          <span style="font-size:13px;font-weight:600;color:var(--navy)"><?= htmlspecialchars($cl['classname']) ?></span>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body></html>
