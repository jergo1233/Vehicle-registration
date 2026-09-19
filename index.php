<?php
session_start();
include("connection.php");

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password FROM account_management_page WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }

    } else {
        $error = "Invalid username or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
   
</head>
<body>

<div id="login">
    
<form method="POST" style="box-shadow: 0 2px 4px rgba(0, 0, 1, 0.486);  background-color: rgba(255, 255, 246, 0.89);">
    <center>
        <h2>Login</h2>

        <?php if(isset($error)) : ?>
            <p style="color: red; text-align:center;"><?= $error; ?></p>
        <?php endif; ?>

        <h3 style="color: green;">VEHICLE REGISTRATION RECORDS MANAGEMENT SYSTEM</h3>
    </center>

    <label for="usernameForLogin">Username:</label><br> 
    <input type="text" placeholder="Enter your username" name="username" id="usernameForLogin" autocomplete="off" required>
    <span id="usernameErrorLogin" class="error"></span><br><br>

    <label for="passwordForLogin">Password:</label><br> 
    <input type="password" placeholder="Enter your password" name="password" id="passwordForLogin" required>
    <span id="passwordErrorLogin" class="error"></span><br><br>
     
    <button class="b" type="submit" name="login">Login</button>
</form>

</div>

</body>
</html>
