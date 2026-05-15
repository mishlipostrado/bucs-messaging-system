<?php
require_once '../includes/config.php';

if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $id    = sanitize($db, $_POST['id_no']   ?? '');
    $fname = sanitize($db, $_POST['fname']   ?? '');
    $mname = sanitize($db, $_POST['mname']   ?? '');
    $lname = sanitize($db, $_POST['lname']   ?? '');
    $uname = sanitize($db, $_POST['uname']   ?? '');
    $pwd   = $_POST['pwd']    ?? '';
    $cpwd  = $_POST['cpwd']   ?? '';

    // Basic validation
    if (!$id || !$fname || !$lname || !$uname || !$pwd) {
        $error = 'Please fill in all required fields.';
    } elseif ($pwd !== $cpwd) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pwd) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate
        $chk = $db->query("SELECT id_no FROM users WHERE id_no='$id' OR uname='$uname' LIMIT 1");
        if ($chk && $chk->num_rows > 0) {
            $error = 'ID number or username already exists.';
        } else {
            $hpwd = md5($pwd);
            $db->query("INSERT INTO users (id_no,fname,mname,lname,uname,pwd)
                        VALUES('$id','$fname','$mname','$lname','$uname','$hpwd')");
            $success = 'Account created! You can now <a href="login.php">sign in</a>.';
        }
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register – BUCS Messaging</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0d1f3c;--blue:#1a3a6b;--teal:#2ab5a5;
  --text:#0d1f3c;--muted:#6b7f9a;--border:#dde6f0;
  --bg:#f4f7fb;--white:#fff;
  --error:#e74c3c;--success:#2ecc71;--radius:14px;
}
body{
  font-family:'DM Sans',sans-serif;background:var(--bg);
  min-height:100vh;display:flex;align-items:center;
  justify-content:center;padding:24px 0;position:relative;overflow-x:hidden;
}
body::before{
  content:'';position:fixed;width:700px;height:700px;border-radius:50%;pointer-events:none;
  background:radial-gradient(circle,rgba(42,181,165,.10) 0%,transparent 70%);
  top:-250px;right:-200px;
}
.reg-wrap{width:min(480px,95vw);position:relative;z-index:1;animation:fadeUp .5s ease both;}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

.login-brand{text-align:center;margin-bottom:22px;}
.brand-icon{
  width:56px;height:56px;background:var(--navy);border-radius:16px;
  display:inline-grid;place-items:center;font-size:24px;color:var(--teal);
  margin-bottom:12px;box-shadow:0 8px 24px rgba(13,31,60,.2);
}
.brand-title{font-family:'DM Serif Display',serif;font-size:24px;color:var(--navy);display:block;}
.brand-sub{font-size:12px;color:var(--muted);font-weight:300;display:block;margin-top:3px;}

.card{
  background:var(--white);border-radius:var(--radius);
  padding:32px 36px 26px;
  box-shadow:0 4px 40px rgba(13,31,60,.09);border:1px solid var(--border);
}
.card-title{font-size:16px;font-weight:600;color:var(--navy);margin-bottom:20px;}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.span-2{grid-column:span 2;}

.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group label{
  font-size:11px;font-weight:600;text-transform:uppercase;
  letter-spacing:.08em;color:var(--muted);
}
.form-group label span{color:var(--error);}
.input-wrap{position:relative;}
.input-wrap i.icon{
  position:absolute;left:12px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:13px;pointer-events:none;
}
.input-wrap input{
  width:100%;padding:10px 12px 10px 36px;
  border:1.5px solid var(--border);border-radius:8px;
  font-family:inherit;font-size:13.5px;color:var(--text);
  background:var(--bg);outline:none;
  transition:border-color .15s,background .15s;
}
.input-wrap input:focus{border-color:var(--teal);background:#fff;}
.toggle-pwd{
  position:absolute;right:11px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--muted);cursor:pointer;font-size:13px;padding:0;
}

.alert{
  padding:11px 14px;border-radius:8px;font-size:13px;
  margin-bottom:18px;display:flex;gap:8px;align-items:flex-start;
}
.alert-error{background:#fdecea;border-left:3px solid var(--error);color:#c0392b;}
.alert-success{background:#e8f8f0;border-left:3px solid var(--success);color:#1a7a44;}
.alert a{color:inherit;font-weight:600;}

.btn-reg{
  width:100%;padding:12px;background:var(--teal);color:#fff;
  border:none;border-radius:9px;font-family:inherit;font-size:14px;font-weight:600;
  cursor:pointer;transition:background .18s,transform .15s;
  display:flex;align-items:center;justify-content:center;gap:8px;margin-top:8px;
}
.btn-reg:hover{background:#1a9d8e;transform:translateY(-1px);}

.card-foot{text-align:center;margin-top:18px;font-size:13px;color:var(--muted);}
.card-foot a{color:var(--navy);font-weight:600;text-decoration:none;}
.card-foot a:hover{text-decoration:underline;}

.divider{
  display:flex;align-items:center;gap:10px;
  font-size:11px;color:var(--muted);text-transform:uppercase;
  letter-spacing:.06em;margin:18px 0;
}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

@media(max-width:480px){.form-grid{grid-template-columns:1fr;}.span-2{grid-column:span 1;}}
</style>
</head>
<body>
<div class="reg-wrap">
  <div class="login-brand">
    <div class="brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="brand-title">BUCS Messaging</span>
    <span class="brand-sub">Create your account</span>
  </div>

  <div class="card">
    <p class="card-title"><i class="fa fa-user-plus" style="color:var(--teal);margin-right:7px"></i>New Student Registration</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fa fa-circle-check"></i><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-grid">
        <div class="form-group span-2">
          <label>ID Number <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-id-card icon"></i>
            <input type="text" name="id_no" required placeholder="e.g. 2024-0001"
                   value="<?= htmlspecialchars($_POST['id_no'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>First Name <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-user icon"></i>
            <input type="text" name="fname" required placeholder="Juan"
                   value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Middle Name</label>
          <div class="input-wrap">
            <i class="fa fa-user icon"></i>
            <input type="text" name="mname" placeholder="(optional)"
                   value="<?= htmlspecialchars($_POST['mname'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group span-2">
          <label>Last Name <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-user icon"></i>
            <input type="text" name="lname" required placeholder="dela Cruz"
                   value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>">
          </div>
        </div>

        <div class="divider span-2">Account Details</div>

        <div class="form-group span-2">
          <label>Username <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-at icon"></i>
            <input type="text" name="uname" required placeholder="Choose a username"
                   value="<?= htmlspecialchars($_POST['uname'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Password <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-lock icon"></i>
            <input type="password" name="pwd" id="pwd" required placeholder="Min. 6 characters">
            <button type="button" class="toggle-pwd" onclick="togglePwd('pwd','eye1')">
              <i class="fa fa-eye" id="eye1"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label>Confirm Password <span>*</span></label>
          <div class="input-wrap">
            <i class="fa fa-lock icon"></i>
            <input type="password" name="cpwd" id="cpwd" required placeholder="Repeat password">
            <button type="button" class="toggle-pwd" onclick="togglePwd('cpwd','eye2')">
              <i class="fa fa-eye" id="eye2"></i>
            </button>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-reg">
        <i class="fa fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="card-foot">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>
<script>
function togglePwd(id,eye){
  var i=document.getElementById(id);
  var e=document.getElementById(eye);
  if(i.type==='password'){i.type='text';e.className='fa fa-eye-slash';}
  else{i.type='password';e.className='fa fa-eye';}
}
</script>
</body>
</html>
