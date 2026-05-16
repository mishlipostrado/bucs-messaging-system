<?php
require_once 'includes/config.php';

if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $uname = sanitize($db, $_POST['uname'] ?? '');
    $pwd   = md5($_POST['pwd'] ?? '');
    $res   = $db->query("SELECT * FROM users WHERE uname='$uname' AND pwd='$pwd' LIMIT 1");
    if ($res && $res->num_rows === 1) {
        $u = $res->fetch_assoc();
        $_SESSION['user'] = [
            'id_no' => $u['id_no'],
            'fname' => $u['fname'],
            'lname' => $u['lname'],
            'uname' => $u['uname'],
        ];
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Invalid username or password.';
    }
    $db->close();
}

$pageTitle  = 'Sign In – BUCS Messaging';
$bodyClass  = 'auth-body';
include 'includes/head.php';
?>

<div class="auth-blob-1"></div>
<div class="auth-blob-2"></div>

<div class="auth-wrap">
  <!-- Brand -->
  <div class="auth-brand">
    <div class="auth-brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="auth-brand-name">BUCS Messaging</span>
    <span class="auth-brand-sub">Bicol University College System</span>
  </div>

  <!-- Card -->
  <div class="auth-card">
    <p class="auth-card-title"><i class="fa fa-arrow-right-to-bracket"></i> Sign in to your account</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group">
        <label class="form-label">Username <span class="req">*</span></label>
        <div class="input-icon-wrap">
          <i class="fa fa-user input-icon"></i>
          <input type="text" name="uname" class="form-control" required
                 placeholder="Enter your username"
                 value="<?= htmlspecialchars($_POST['uname'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password <span class="req">*</span></label>
        <div class="input-icon-wrap">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" id="pwd" name="pwd" class="form-control" required placeholder="Enter your password">
          <button type="button" class="input-icon-right" onclick="togglePwd('pwd','eye1')">
            <i class="fa fa-eye" id="eye1"></i>
          </button>
        </div>
      </div>
      <div class="form-group mt-8">
        <button type="submit" class="btn btn-navy btn-full">
          <i class="fa fa-arrow-right-to-bracket"></i> Sign In
        </button>
      </div>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>

  <div class="demo-hint">
    <i class="fa fa-circle-info"></i>
    <div>Demo: username <code>juandc</code> &nbsp;|&nbsp; password <code>password1</code></div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
