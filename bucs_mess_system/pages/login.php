<?php
require_once '../includes/config.php';

if (isset($_POST['login'])) {

    $db = getDB();

    $uname = sanitize($db, $_POST['uname']);
    $pwd = md5($_POST['password']);

    $sql = "SELECT * FROM users
            WHERE uname='$uname'
            AND pwd='$pwd'
            LIMIT 1";

    $result = $db->query($sql);

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $_SESSION['user'] = [
            'id_no' => $row['id_no'],
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'uname' => $row['uname']
        ];

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Invalid username or password!";
    }

    $db->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <link rel="icon" type="image/png" href="../assets/icon.png">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

    <div class="left-panel">

        <img src="../assets/icon.png" class="side-logo">

        <h1>BUCS</h1>

        <p>Messaging System</p>

    </div>

    <div class="right-panel">

        <h2><i class="fa fa-right-to-bracket"></i> Login</h2>

        <?php if (isset($error)): ?>
            <p id="message"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">

                <label>Username</label>

                <div class="password-container">

                    <i class="fa fa-user"></i>

                    <input type="text"
                           name="uname"
                           placeholder="Enter Username"
                           required>

                </div>
            </div>

            <div class="input-group">

                <label>Password</label>

                <div class="password-container">

                    <i class="fa fa-lock"></i>

                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="Enter Password"
                           required>

                    <span id="showPassword">👁️</span>

                </div>
            </div>

            <button type="submit" name="login">
                <i class="fa fa-right-to-bracket"></i> Login
            </button>

        </form>

        <p>
            No account?
            <a href="../index.php">Sign Up</a>
        </p>

    </div>

</div>

<script>
const password = document.getElementById("password");
const showPassword = document.getElementById("showPassword");

showPassword.addEventListener("click", () => {

    if(password.type === "password"){
        password.type = "text";
        showPassword.textContent = "🙈";
    } else {
        password.type = "password";
        showPassword.textContent = "👁️";
    }

});
</script>

</body>
</html>