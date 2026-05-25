<?php
require_once 'includes/config.php';

$message = "";

if (isset($_POST['signup'])) {

    $db = getDB();

    $id_no = sanitize($db, $_POST['id_no']);
    $fname = sanitize($db, $_POST['fname']);
    $mname = sanitize($db, $_POST['mname']);
    $lname = sanitize($db, $_POST['lname']);
    $uname = sanitize($db, $_POST['uname']);

    $pwd = md5($_POST['password']);

    $sql = "INSERT INTO users
            (id_no, fname, mname, lname, uname, pwd)
            VALUES
            ('$id_no','$fname','$mname','$lname','$uname','$pwd')";

    if ($db->query($sql)) {

        $message = "Signup successful!";

    } else {

        $message = "Error: " . $db->error;
    }

    $db->close();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Sign Up</title>

    <link rel="icon"
          type="image/png"
          href="assets/pictures/icon.png">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body class="auth-body">

<div class="container">

    <!-- LEFT -->
    <div class="left-panel">

        <img src="assets/pictures/icon.png"
             class="side-logo">

        <h1>BUCS</h1>

        <p>Messaging System</p>

    </div>

    <!-- RIGHT -->
    <div class="right-panel">

        <h2>
            <i class="fa fa-user-plus"></i>
            Sign Up
        </h2>

        <?php if ($message != ""): ?>

            <p id="message">
                <?php echo $message; ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <div class="input-group">

                <label>ID Number</label>

                <div class="input-icon-wrap">

                    <i class="fa fa-id-card input-icon"></i>

                    <input type="text"
                           name="id_no"
                           required>

                </div>
            </div>

            <div class="input-group">

                <label>First Name</label>

                <div class="input-icon-wrap">

                    <i class="fa fa-user input-icon"></i>

                    <input type="text"
                           name="fname"
                           required>

                </div>
            </div>

            <div class="input-group">

                <label>Middle Name</label>

                <div class="input-icon-wrap">

                    <i class="fa fa-user input-icon"></i>

                    <input type="text"
                           name="mname">

                </div>
            </div>

            <div class="input-group">

                <label>Last Name</label>

                <div class="input-icon-wrap">

                    <i class="fa fa-user input-icon"></i>

                    <input type="text"
                           name="lname"
                           required>

                </div>
            </div>

            <div class="input-group">

                <label>Username</label>

                <div class="input-icon-wrap">

                    <i class="fa fa-at input-icon"></i>

                    <input type="text"
                           name="uname"
                           required>

                </div>
            </div>

            <div class="input-group">

                <label>Password</label>

                <div class="input-icon-wrap password-container">

                    <i class="fa fa-lock input-icon"></i>

                    <input type="password"
              		name="password"
               		id="password"
               		placeholder="Enter Password"
              		required>

                    <span id="showPassword">
                        👁️
                    </span>

                </div>
            </div>

            <button type="submit" name="signup">

                <i class="fa fa-user-plus"></i>

                Sign Up

            </button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</div>

<script src="assets/js/app.js"></script>

</body>
</html>