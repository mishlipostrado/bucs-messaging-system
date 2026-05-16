<?php
require_once 'includes/config.php';
$me = auth_guard();

$db  = getDB();
$uid = $me['id_no'];

$unread   = (int)$db->query("SELECT COUNT(*) c FROM messages WHERE receiver='$uid' AND status='sent'")->fetch_assoc()['c'];
$reply_to = sanitize($db, $_GET['reply'] ?? '');
$success  = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = sanitize($db, $_POST['receiver'] ?? '');
    $message  = sanitize($db, $_POST['message']  ?? '');

    if (!$receiver || !$message) {
        $error = 'Please fill in all fields.';
    } elseif ($receiver === $uid) {
        $error = 'You cannot send a message to yourself.';
    } else {
        $chk = $db->query("SELECT id_no FROM users WHERE id_no='$receiver' LIMIT 1");
        if (!$chk || $chk->num_rows === 0) {
            $error = 'Recipient not found.';
        } else {
            $db->query("INSERT INTO messages (sender_id,receiver,message,status)
                        VALUES('$uid','$receiver','$message','sent')");
            $success  = 'Message sent successfully!';
            $reply_to = '';
        }
    }
}

$users = $db->query("SELECT id_no, fname, lname FROM users WHERE id_no != '$uid' ORDER BY lname, fname");
$db->close();

$pageTitle = 'Compose – BUCS Messaging';
include 'includes/head.php';
include 'includes/navbar.php';
?>

<div class="page-wrap page-wrap-sm">
  <div class="page-head">
    <h1 class="page-title"><i class="fa fa-pen-to-square"></i> Compose</h1>
    <a href="inbox.php" class="btn btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><i class="fa fa-circle-check"></i><?= $success ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" novalidate>
        <div class="form-group">
          <label class="form-label">To <span class="req">*</span></label>
          <select name="receiver" class="form-control" required>
            <option value="">Select recipient…</option>
            <?php while ($u = $users->fetch_assoc()): ?>
              <option value="<?= $u['id_no'] ?>"
                <?= ($reply_to === $u['id_no'] || ($_POST['receiver'] ?? '') === $u['id_no']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['fname'].' '.$u['lname']) ?> (<?= $u['id_no'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Message <span class="req">*</span></label>
          <textarea name="message" class="form-control" rows="7" required
                    placeholder="Write your message here…"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <div class="flex gap-8" style="justify-content:flex-end">
          <a href="inbox.php" class="btn btn-ghost">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-paper-plane"></i> Send Message
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
