<?php

$current = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$nav = [
  ['index.php',            'Dashboard',  'fa-gauge-high',   '..'],
  ['pages/users.php',      'Users',      'fa-users',        ''],
  ['pages/files.php',      'Files',      'fa-file-alt',     ''],
  ['pages/messages.php',   'Messages',   'fa-envelope',     ''],
  ['pages/classes.php',    'Classes',    'fa-chalkboard',   ''],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo"><i class="fa fa-comment-dots"></i></div>
    <div class="brand-text">
      <span class="brand-name">BUCS</span>
      <span class="brand-sub">Messaging System</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach($nav as [$href,$label,$icon,$base]):
      $is_active = (strpos($_SERVER['PHP_SELF'], str_replace('../','',$href)) !== false) ||
                   ($href==='index.php' && $current==='index.php');
    ?>
    <a href="<?= $base ? $base.'/'.$href : $href ?>" class="nav-item <?= $is_active?'active':'' ?>">
      <i class="fa <?= $icon ?>"></i>
      <span><?= $label ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user"><i class="fa fa-shield-halved"></i> Admin Panel</div>
  </div>
</aside>