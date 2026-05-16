<?php
// includes/navbar.php
// Requires $me (session user array) and $unread (int) to be set before include.
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="topnav">
  <a href="dashboard.php" class="nav-brand">
    <div class="nav-brand-icon"><i class="fa fa-comment-dots"></i></div>
    <span class="nav-brand-name">BUCS</span>
  </a>

  <div class="nav-spacer"></div>

  <div class="nav-links">
    <a href="dashboard.php" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>">
      <i class="fa fa-house"></i><span>Home</span>
    </a>
    <a href="inbox.php" class="nav-link <?= $page === 'inbox' ? 'active' : '' ?>">
      <i class="fa fa-inbox"></i><span>Inbox</span>
      <?php if (!empty($unread) && $unread > 0): ?>
        <span class="nav-badge"><?= $unread ?></span>
      <?php endif; ?>
    </a>
    <a href="sent.php" class="nav-link <?= $page === 'sent' ? 'active' : '' ?>">
      <i class="fa fa-paper-plane"></i><span>Sent</span>
    </a>
    <a href="files.php" class="nav-link <?= $page === 'files' ? 'active' : '' ?>">
      <i class="fa fa-folder-open"></i><span>Files</span>
    </a>
  </div>

  <div class="nav-user" tabindex="0">
    <div class="nav-avatar">
      <?= strtoupper(substr($me['fname'], 0, 1) . substr($me['lname'], 0, 1)) ?>
    </div>
    <span><?= htmlspecialchars($me['fname']) ?></span>
    <i class="fa fa-chevron-down text-xs" style="opacity:.6"></i>
    <div class="user-dropdown">
      <a href="profile.php"><i class="fa fa-user"></i> My Profile</a>
      <a href="classes.php"><i class="fa fa-chalkboard"></i> My Classes</a>
      <hr>
      <a href="logout.php" class="danger"><i class="fa fa-right-from-bracket"></i> Sign Out</a>
    </div>
  </div>
</nav>
