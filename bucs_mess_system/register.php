<?php
require_once 'includes/config.php';

if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $id    = sanitize($db, $_POST['id_no']  ?? '');
    $fname = sanitize($db, $_POST['fname']  ?? '');
    $mname = sanitize($db, $_POST['mname']  ?? '');
    $lname = sanitize($db, $_POST['lname']  ?? '');
    $uname = sanitize($db, $_POST['uname']  ?? '');
    $pwd   = $_POST['pwd']  ?? '';
    $cpwd  = $_POST['cpwd'] ?? '';

    if (!$id || !$fname || !$lname || !$uname || !$pwd) {
        $error = 'Please fill in all required fields.';
    } elseif ($pwd !== $cpwd) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pwd) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $chk = $db->query("SELECT id_no FROM users WHERE id_no='$id' OR uname='$uname' LIMIT 1");
        if ($chk && $chk->num_rows > 0) {
            $error = 'ID number or username already exists.';
        } else {
            $hp = md5($pwd);
            $db->query("INSERT INTO users (id_no,fname,mname,lname,uname,pwd)
                        VALUES('$id','$fname','$mname','$lname','$uname','$hp')");
            $success = 'Account created! You can now <a href="login.php">sign in</a>.';
        }
    }
    $db->close();
}

$pageTitle = 'Register – BUCS Messaging';
$bodyClass = 'auth-body';
include 'includes/head.php';
?>

<div class="auth-blob-1"></div>
<div class="auth-blob-2"></div>

<div class="auth-wrap">
  <div class="auth-brand">
    <div class="auth-brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="auth-brand-name">BUCS Messaging</span>
    <span class="auth-brand-sub">Create your account</span>
  </div>

  <div class="auth-card">
    <p class="auth-card-title"><i class="fa fa-user-plus"></i> New Student Registration</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><i class="fa fa-circle-check"></i><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <!-- ID -->
      <div class="form-group">
        <label class="form-label">ID Number <span class="req">*</span></label>
        <div class="input-icon-wrap">
          <i class="fa fa-id-card input-icon"></i>
          <input type="text" name="id_no" class="form-control" required placeholder="e.g. 2024-0001"
                 value="<?= htmlspecialchars($_POST['id_no'] ?? '') ?>">
        </div>
      </div>

      <!-- Name -->
      <div class="form-2col">
        <div class="form-group">
          <label class="form-label">First Name <span class="req">*</span></label>
          <div class="input-icon-wrap">
            <i class="fa fa-user input-icon"></i>
            <input type="text" name="fname" class="form-control" required placeholder="Juan"
                   value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Middle Name</label>
          <div class="input-icon-wrap">
            <i class="fa fa-user input-icon"></i>
            <input type="text" name="mname" class="form-control" placeholder="(optional)"
                   value="<?= htmlspecialchars($_POST['mname'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Last Name <span class="req">*</span></label>
        <div class="input-icon-wrap">
          <i class="fa fa-user input-icon"></i>
          <input type="text" name="lname" class="form-control" required placeholder="dela Cruz"
                 value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>">
        </div>
      </div>

      <div class="form-divider">Account Details</div>

      <div class="form-group">
        <label class="form-label">Username <span class="req">*</span></label>
        <div class="input-icon-wrap">
          <i class="fa fa-at input-icon"></i>
          <input type="text" name="uname" class="form-control" required placeholder="Choose a username"
                 value="<?= htmlspecialchars($_POST['uname'] ?? '') ?>">
        </div>
      </div>

      <div class="form-2col">
        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <div class="input-icon-wrap">
            <i class="fa fa-lock input-icon"></i>
            <input type="password" id="pwd" name="pwd" class="form-control" required placeholder="Min. 6 chars">
            <button type="button" class="input-icon-right" onclick="togglePwd('pwd','eye1')">
              <i class="fa fa-eye" id="eye1"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span class="req">*</span></label>
          <div class="input-icon-wrap">
            <i class="fa fa-lock input-icon"></i>
            <input type="password" id="cpwd" name="cpwd" class="form-control" required placeholder="Repeat">
            <button type="button" class="input-icon-right" onclick="togglePwd('cpwd','eye2')">
              <i class="fa fa-eye" id="eye2"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="form-group mt-8">
        <button type="submit" class="btn btn-primary btn-full">
          <i class="fa fa-user-plus"></i> Create Account
        </button>
      </div>
    </form>

    <div class="auth-footer">Already have an account? <a href="login.php">Sign in</a></div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
