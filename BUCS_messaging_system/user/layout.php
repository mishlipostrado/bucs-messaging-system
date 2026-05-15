<?php
// user/includes/auth.php — session guard + shared layout header
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); exit;
}
$me = $_SESSION['user'];

// Unread message count for badge
$db2 = getDB();
$uid = $me['id_no'];
$unread_res = $db2->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'");
$unread = $unread_res ? (int)$unread_res->fetch_assoc()['c'] : 0;
$db2->close();

// Which page are we on?
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'BUCS Messaging' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0d1f3c;--blue:#1a3a6b;--teal:#2ab5a5;--teal-dk:#1a9d8e;
  --teal-lt:#e1f5f3;--text:#0d1f3c;--muted:#6b7f9a;
  --border:#dde6f0;--bg:#f4f7fb;--white:#fff;
  --error:#e74c3c;--success:#27ae60;--warn:#f39c12;
  --nav-h:62px;--radius:12px;
  --shadow:0 2px 16px rgba(13,31,60,.09);
  --shadow-lg:0 6px 32px rgba(13,31,60,.14);
}
html{font-size:14px;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

/* ── TOP NAV ── */
.topnav{
  position:sticky;top:0;z-index:100;
  height:var(--nav-h);
  background:var(--navy);
  display:flex;align-items:center;
  padding:0 28px;gap:20px;
  box-shadow:0 2px 12px rgba(0,0,0,.2);
}
.nav-brand{
  display:flex;align-items:center;gap:10px;
  text-decoration:none;flex-shrink:0;
}
.nav-brand-icon{
  width:36px;height:36px;background:var(--teal);border-radius:10px;
  display:grid;place-items:center;font-size:16px;color:#fff;
}
.nav-brand-name{
  font-family:'DM Serif Display',serif;font-size:18px;color:#fff;
}
.nav-spacer{flex:1;}
.nav-links{display:flex;align-items:center;gap:4px;}
.nav-link{
  display:flex;align-items:center;gap:7px;padding:7px 14px;
  border-radius:8px;color:rgba(255,255,255,.65);
  text-decoration:none;font-size:13px;font-weight:500;
  transition:background .15s,color .15s;position:relative;
}
.nav-link:hover{background:rgba(255,255,255,.1);color:#fff;}
.nav-link.active{background:var(--teal);color:#fff;}
.nav-link .badge{
  position:absolute;top:4px;right:6px;
  background:var(--error);color:#fff;
  font-size:9px;font-weight:700;
  min-width:16px;height:16px;border-radius:8px;
  display:grid;place-items:center;padding:0 3px;
}
.nav-user{
  display:flex;align-items:center;gap:10px;
  padding:6px 14px 6px 10px;
  border-radius:8px;color:rgba(255,255,255,.8);
  font-size:13px;cursor:pointer;
  border:1px solid rgba(255,255,255,.12);
  position:relative;
}
.nav-avatar{
  width:30px;height:30px;background:var(--teal);border-radius:8px;
  display:grid;place-items:center;font-size:13px;color:#fff;font-weight:600;flex-shrink:0;
}
.nav-user:hover .user-dropdown{display:block;}
.user-dropdown{
  display:none;position:absolute;top:calc(100% + 8px);right:0;
  background:var(--white);border-radius:var(--radius);
  border:1px solid var(--border);box-shadow:var(--shadow-lg);
  min-width:180px;overflow:hidden;z-index:200;
}
.user-dropdown a{
  display:flex;align-items:center;gap:10px;padding:11px 16px;
  text-decoration:none;color:var(--text);font-size:13px;
  transition:background .12s;
}
.user-dropdown a:hover{background:var(--bg);}
.user-dropdown a.danger{color:var(--error);}
.user-dropdown hr{border:none;border-top:1px solid var(--border);margin:4px 0;}

/* ── PAGE WRAPPER ── */
.page-wrap{max-width:960px;margin:0 auto;padding:28px 20px;}
.page-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:24px;gap:12px;flex-wrap:wrap;
}
.page-head h1{
  font-family:'DM Serif Display',serif;
  font-size:24px;color:var(--navy);
  display:flex;align-items:center;gap:10px;
}
.page-head h1 i{color:var(--teal);font-size:20px;}

/* ── CARDS ── */
.card{
  background:var(--white);border-radius:var(--radius);
  border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;
}
.card-body{padding:22px;}

/* ── BUTTONS ── */
.btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 20px;border-radius:8px;border:none;
  font-family:inherit;font-size:13px;font-weight:600;
  cursor:pointer;transition:all .15s;text-decoration:none;
}
.btn-primary{background:var(--teal);color:#fff;box-shadow:0 3px 10px rgba(42,181,165,.28);}
.btn-primary:hover{background:var(--teal-dk);transform:translateY(-1px);}
.btn-navy{background:var(--navy);color:#fff;}
.btn-navy:hover{background:var(--blue);}
.btn-ghost{background:transparent;color:var(--muted);border:1.5px solid var(--border);}
.btn-ghost:hover{border-color:var(--teal);color:var(--teal);}
.btn-sm{padding:6px 14px;font-size:12px;}
.btn-danger{background:#fdecea;color:var(--error);}
.btn-danger:hover{background:var(--error);color:#fff;}

/* ── ALERTS ── */
.alert{
  padding:11px 16px;border-radius:8px;font-size:13px;font-weight:500;
  display:flex;align-items:center;gap:9px;margin-bottom:18px;
}
.alert-success{background:#e8f8f0;border-left:3px solid var(--success);color:#1a6b3a;}
.alert-error{background:#fdecea;border-left:3px solid var(--error);color:#a93226;}
.alert-info{background:var(--teal-lt);border-left:3px solid var(--teal);color:#0f6e56;}

/* ── BADGES ── */
.badge{
  display:inline-block;padding:2px 9px;border-radius:20px;
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;
}
.badge-sent{background:#e3f0ff;color:#1a5299;}
.badge-read{background:var(--teal-lt);color:#0f6e56;}
.badge-deleted{background:#fdecea;color:var(--error);}

/* ── FORMS ── */
.form-group{margin-bottom:16px;}
.form-group label{
  display:block;font-size:11px;font-weight:600;
  text-transform:uppercase;letter-spacing:.07em;
  color:var(--muted);margin-bottom:6px;
}
.form-group input,.form-group select,.form-group textarea{
  width:100%;padding:10px 13px;
  border:1.5px solid var(--border);border-radius:8px;
  font-family:inherit;font-size:13.5px;color:var(--text);
  background:var(--bg);outline:none;
  transition:border-color .15s,background .15s;
}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{
  border-color:var(--teal);background:#fff;
}
textarea{resize:vertical;}
.form-2col{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

/* ── TABLE ── */
.tbl-wrap{overflow-x:auto;}
table.tbl{width:100%;border-collapse:collapse;font-size:13px;}
table.tbl thead tr{background:var(--bg);}
table.tbl th{
  padding:10px 14px;text-align:left;
  font-size:10.5px;font-weight:700;text-transform:uppercase;
  letter-spacing:.08em;color:var(--muted);white-space:nowrap;
}
table.tbl td{padding:12px 14px;border-top:1px solid var(--border);vertical-align:middle;}
table.tbl tbody tr:hover{background:#f8faff;}
table.tbl .act{white-space:nowrap;display:flex;gap:6px;}

/* ── EMPTY STATE ── */
.empty{
  text-align:center;padding:56px 20px;color:var(--muted);
}
.empty i{font-size:42px;opacity:.25;display:block;margin-bottom:14px;}
.empty p{font-size:14px;}

/* ── MODAL ── */
.modal-overlay{
  display:none;position:fixed;inset:0;
  background:rgba(13,31,60,.5);backdrop-filter:blur(4px);
  z-index:500;align-items:center;justify-content:center;
}
.modal-overlay.open{display:flex;}
.modal{
  background:var(--white);border-radius:16px;
  width:min(500px,95vw);
  box-shadow:var(--shadow-lg);
  animation:modalIn .22s ease both;overflow:hidden;
}
@keyframes modalIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.modal-hd{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 22px;background:var(--navy);color:#fff;
}
.modal-hd h3{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px;}
.modal-hd h3 i{color:var(--teal);}
.modal-hd button{background:none;border:none;color:rgba(255,255,255,.6);font-size:18px;cursor:pointer;}
.modal-body{padding:22px;}
.modal-ft{display:flex;gap:10px;justify-content:flex-end;padding-top:14px;border-top:1px solid var(--border);margin-top:14px;}

@media(max-width:600px){
  .topnav{padding:0 14px;}
  .nav-brand-name{display:none;}
  .nav-link span{display:none;}
  .page-wrap{padding:16px 12px;}
  .form-2col{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="topnav">
  <a href="dashboard.php" class="nav-brand">
    <div class="nav-brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="nav-brand-name">BUCS</span>
  </a>
  <div class="nav-spacer"></div>
  <div class="nav-links">
    <a href="dashboard.php" class="nav-link <?= $page==='dashboard'?'active':'' ?>">
      <i class="fa fa-house"></i><span>Home</span>
    </a>
    <a href="inbox.php" class="nav-link <?= $page==='inbox'?'active':'' ?>">
      <i class="fa fa-inbox"></i><span>Inbox</span>
      <?php if($unread > 0): ?>
      <span class="badge"><?= $unread ?></span>
      <?php endif; ?>
    </a>
    <a href="sent.php" class="nav-link <?= $page==='sent'?'active':'' ?>">
      <i class="fa fa-paper-plane"></i><span>Sent</span>
    </a>
    <a href="files.php" class="nav-link <?= $page==='files'?'active':'' ?>">
      <i class="fa fa-folder-open"></i><span>Files</span>
    </a>
  </div>
  <div class="nav-user" tabindex="0">
    <div class="nav-avatar"><?= strtoupper(substr($me['fname'],0,1).substr($me['lname'],0,1)) ?></div>
    <span><?= htmlspecialchars($me['fname']) ?></span>
    <i class="fa fa-chevron-down" style="font-size:10px;opacity:.6"></i>
    <div class="user-dropdown">
      <a href="profile.php"><i class="fa fa-user"></i> My Profile</a>
      <a href="classes.php"><i class="fa fa-chalkboard"></i> My Classes</a>
      <hr>
      <a href="logout.php" class="danger"><i class="fa fa-right-from-bracket"></i> Sign Out</a>
    </div>
  </div>
</nav>
