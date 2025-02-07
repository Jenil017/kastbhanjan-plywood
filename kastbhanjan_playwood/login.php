<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'jemish' && $password === '9898') {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login.css">
    <title>Admin Login</title>
</head>
<body>
    <div class="background-image">
        <div class="login-container">
            <h1>Kastbhanjan Playwood</h1>
            <h2>Admin Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </div>
</body>
</html>
