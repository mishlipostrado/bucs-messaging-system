<?php
require_once '../includes/config.php';

// Already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $uname = sanitize($db, $_POST['uname'] ?? '');
    $pwd   = md5($_POST['pwd'] ?? '');

    $res = $db->query("SELECT * FROM users WHERE uname='$uname' AND pwd='$pwd' LIMIT 1");
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $_SESSION['user'] = [
            'id_no' => $user['id_no'],
            'fname' => $user['fname'],
            'lname' => $user['lname'],
            'uname' => $user['uname'],
        ];
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Invalid username or password.';
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – BUCS Messaging</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0d1f3c;
  --blue:#1a3a6b;
  --teal:#2ab5a5;
  --teal-light:#e1f5f3;
  --text:#0d1f3c;
  --muted:#6b7f9a;
  --border:#dde6f0;
  --bg:#f4f7fb;
  --white:#fff;
  --error:#e74c3c;
  --radius:14px;
}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  position:relative;
  overflow:hidden;
}
/* Decorative background blobs */
body::before,body::after{
  content:'';position:absolute;border-radius:50%;pointer-events:none;
}
body::before{
  width:600px;height:600px;
  background:radial-gradient(circle,rgba(42,181,165,.12) 0%,transparent 70%);
  top:-200px;right:-150px;
}
body::after{
  width:400px;height:400px;
  background:radial-gradient(circle,rgba(26,58,107,.08) 0%,transparent 70%);
  bottom:-100px;left:-100px;
}

.login-wrap{
  width:min(420px,95vw);
  position:relative;z-index:1;
  animation:fadeUp .5s ease both;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}

.login-brand{
  text-align:center;
  margin-bottom:28px;
}
.brand-icon{
  width:60px;height:60px;
  background:var(--navy);
  border-radius:18px;
  display:inline-grid;place-items:center;
  font-size:26px;color:var(--teal);
  margin-bottom:14px;
  box-shadow:0 8px 24px rgba(13,31,60,.25);
}
.brand-title{
  font-family:'DM Serif Display',serif;
  font-size:26px;color:var(--navy);
  display:block;line-height:1.1;
}
.brand-sub{
  font-size:13px;color:var(--muted);
  font-weight:300;letter-spacing:.04em;
  display:block;margin-top:4px;
}

.card{
  background:var(--white);
  border-radius:var(--radius);
  padding:36px 36px 28px;
  box-shadow:0 4px 40px rgba(13,31,60,.10);
  border:1px solid var(--border);
}
.card-title{
  font-size:17px;font-weight:600;
  color:var(--navy);margin-bottom:22px;
}

.form-group{margin-bottom:18px;position:relative;}
.form-group label{
  display:block;
  font-size:11px;font-weight:600;
  text-transform:uppercase;letter-spacing:.08em;
  color:var(--muted);margin-bottom:6px;
}
.input-wrap{position:relative;}
.input-wrap i{
  position:absolute;left:13px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:13px;pointer-events:none;
}
.input-wrap input{
  width:100%;padding:11px 13px 11px 38px;
  border:1.5px solid var(--border);
  border-radius:9px;font-family:inherit;font-size:14px;
  color:var(--text);background:var(--bg);outline:none;
  transition:border-color .15s,background .15s;
}
.input-wrap input:focus{border-color:var(--teal);background:#fff;}
.input-wrap .toggle-pwd{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--muted);cursor:pointer;font-size:13px;
  padding:0;line-height:1;
}

.error-msg{
  background:#fdecea;border-left:3px solid var(--error);
  padding:10px 14px;border-radius:6px;
  font-size:13px;color:#c0392b;
  margin-bottom:18px;display:flex;gap:8px;align-items:center;
}

.btn-login{
  width:100%;padding:12px;
  background:var(--navy);color:#fff;
  border:none;border-radius:9px;
  font-family:inherit;font-size:14px;font-weight:600;
  cursor:pointer;transition:background .18s,transform .15s;
  display:flex;align-items:center;justify-content:center;gap:8px;
  margin-top:4px;
}
.btn-login:hover{background:var(--blue);transform:translateY(-1px);}
.btn-login:active{transform:none;}

.login-footer{
  text-align:center;margin-top:20px;
  font-size:13px;color:var(--muted);
}
.login-footer a{color:var(--teal);font-weight:600;text-decoration:none;}
.login-footer a:hover{text-decoration:underline;}

.demo-hint{
  margin-top:22px;
  background:var(--teal-light);
  border-radius:9px;padding:12px 16px;
  font-size:12px;color:#1a8a7c;
  display:flex;gap:10px;align-items:flex-start;
}
.demo-hint i{margin-top:1px;flex-shrink:0;}
.demo-hint code{
  background:rgba(0,0,0,.07);padding:1px 5px;border-radius:4px;
  font-family:monospace;
}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-brand">
    <div class="brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="brand-title">BUCS Messaging</span>
    <span class="brand-sub">Bicol University College System</span>
  </div>

  <div class="card">
    <p class="card-title">Sign in to your account</p>

    <?php if ($error): ?>
    <div class="error-msg"><i class="fa fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <i class="fa fa-user"></i>
          <input type="text" name="uname" required autocomplete="username"
                 placeholder="Enter your username"
                 value="<?= htmlspecialchars($_POST['uname'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock"></i>
          <input type="password" name="pwd" id="pwd" required autocomplete="current-password"
                 placeholder="Enter your password">
          <button type="button" class="toggle-pwd" onclick="togglePwd()">
            <i class="fa fa-eye" id="pwd-eye"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-login">
        <i class="fa fa-arrow-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="login-footer">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>

  <div class="demo-hint">
    <i class="fa fa-circle-info"></i>
    <div>Demo credentials — username: <code>juandc</code> &nbsp;password: <code>password1</code></div>
  </div>
</div>
<script>
function togglePwd(){
  var i=document.getElementById('pwd');
  var e=document.getElementById('pwd-eye');
  if(i.type==='password'){i.type='text';e.className='fa fa-eye-slash';}
  else{i.type='password';e.className='fa fa-eye';}
}
</script>
</body>
</html>
