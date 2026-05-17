<?php
session_start();
require "connection.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$error_edit_message = "";
$error_add_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            // UPDATE LOGIC (Kapag may user_id)
                            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                                $user_id = $_POST['user_id'];
                                $sql_fetch = "SELECT fullname, username, email FROM account_management_page WHERE user_id = ?";
                                $stmt_fetch = $conn->prepare($sql_fetch);
                                $stmt_fetch->bind_param("i", $user_id);
                                $stmt_fetch->execute();
                                $result_fetch = $stmt_fetch->get_result();
                                $current_data = $result_fetch->fetch_assoc();
                                $stmt_fetch->close();
                            
                                $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : $current_data['fullname'];
                                $username = isset($_POST['username']) ? trim($_POST['username']) : $current_data['username'];
                                $email    = isset($_POST['email']) ? trim($_POST['email']) : $current_data['email'];
                            
                                $fullname = !empty($fullname) ? $fullname : $current_data['fullname'];
                                $username = !empty($username) ? $username : $current_data['username'];
                                $email    = !empty($email)    ? $email    : $current_data['email'];
                            
                                $password = $_POST['password'] ?? '';
                                $confirm = $_POST['confirm_password'] ?? '';
                            
                                $sql = "UPDATE account_management_page SET fullname = ?, username = ?, email = ?";
                                $params = [&$fullname, &$username, &$email];
                                $types = "sss"; 

                                // Password Update Logic
                                if (!empty($password)) {
                                    if ($password !== $confirm) {
                                        $error_edit_message = "New Password does not match confirmation!";
                                    } else {
                                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                                        $sql .= ", password = ?";
                                        $params[] = &$hashed;
                                        $types .= "s";
                                    }
                                }

                                $image = NULL;
                                if (empty($error_edit_message) && isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK && $_FILES['Image']['size'] > 0) {
                                    $image_temp = file_get_contents($_FILES['Image']['tmp_name']);
                                    $image = $image_temp;

                                    $sql = "UPDATE account_management_page SET profile = ?, fullname = ?, username = ?, email = ?";
                                    $types = "s" . $types;
                                    $new_params = [&$image, &$fullname, &$username, &$email];

                                    // Idagdag ang password kung meron
                                    if (!empty($password) && $password === $confirm) {
                                        $new_params[] = &$hashed;
                                    }
                                    $params = $new_params; 
                                }

                                // 5. Execution ng Query
                                if (empty($error_edit_message)) {
                                    $sql .= " WHERE user_id = ?";
                                    $types .= "i";
                                    $params[] = &$user_id; 
                                    $stmt = $conn->prepare($sql);
                                    array_unshift($params, $types); 
                                     $bind_names = $params; 

                                    if (call_user_func_array([$stmt, 'bind_param'], $bind_names)) {
                                        if ($stmt->execute()) {
                                            header("Location: account_page.php?updated=1");
                                            exit;
                                        } else {
                                            $error_edit_message = "Error updating record: " . $stmt->error;
                                        }
                                    } else {
                                        $error_edit_message = "Binding parameters failed.";
                                    }
                                    $stmt->close();
                                }

} else {
             //insert new records
             $fullname = $_POST['fullname'] ?? '';
             $username = $_POST['username'] ?? '';
             $email = $_POST['email'] ?? '';
             $password = $_POST['password'] ?? '';
             $confirm = $_POST['confirm_password'] ?? '';
             $image = NULL;
     
           if ($password !== $confirm) {
                 $error_add_message = "Password does not match!";
                 } else {
                 $hashed = password_hash($password, PASSWORD_DEFAULT);
     
                 if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK && $_FILES['Image']['size'] > 0) {
                     $image_temp = file_get_contents($_FILES['Image']['tmp_name']);
                     $image = $image_temp;
                 } else {
                     $error_add_message = "Error: Profile image upload failed or file is too large/empty."; 
                 }
                 
                 if (empty($error_add_message)) {
                     $sql_insert = "INSERT INTO account_management_page (profile, fullname, username, email, password) VALUES (?, ?, ?, ?, ?)";
                     $stmt_insert = $conn->prepare($sql_insert);
                     $types = "sssss";
                     $params = [&$image, &$fullname, &$username, &$email, &$hashed];
                     array_unshift($params, $types);
                     $bind_names = $params; 
                     
                     if (call_user_func_array([$stmt_insert, 'bind_param'], $bind_names)) {
                         if ($stmt_insert->execute()) {
                             header("Location: account_page.php?added=1");
                             exit;
                         } else {
                             $error_add_message = "Error adding record: " . $stmt_insert->error;
                              echo ( $error_add_message);
                         }
                     } else {
                         $error_add_message = "failed for insertion.";
                         echo ( $error_add_message);
                     }
                     $stmt_insert->close();
                 }
            }
        }
}

$sql = "SELECT user_id, profile, fullname, username, email FROM account_management_page";
$result = $conn->query($sql);



if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM account_management_page WHERE user_id = '$delete_id'");
    header("Location: account_page.php?deleted=1");
    exit();
}
?>





<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owners Information – Vehicle RMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/logccmadi.png">
    </head>
<body>
<div class="sidebar">
    <center><img src="assets/logccmadi.png" width="60px">
    <h1>CCMADI</h1>
    <h2>Vehicle RRMS</h2></center>
    <a href="dashboard.php">Dashboard</a>
    <a href="account_page.php" class="active">Account Management Page</a>
    <a href="vehicles.php">Vehicle Records</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php" onclick="return confirmLogout()">Logout</a>
</div>


                        <div class="container">
                            <div class="card_edit" id="popupForm" style="display:none;"> 
                                <center><h2>Edit Owner Information</h2></center><br>

                                <form method="post" action="account_page.php" enctype="multipart/form-data" onsubmit="return checkEditPassword()">
                                    <input type="hidden" name="user_id" id="edit_id">
                                    <label for="edit_image_upload">Upload New Image (Profile) </label>
                                    <input type="file" name="Image" id="edit_image_upload" accept=".jpg, .jpeg, .png">
                                    <label for="edit_fullname">Fullname</label>
                                    <input type="text" name="fullname" id="edit_fullname" placeholder="New Fullname" required>
                                    <label for="edit_username">Username</label>
                                    <input type="text" name="username" id="edit_username" placeholder="New Username" required>
                                    <label for="edit_email">Email</label>
                                    <input type="text" name="email" id="edit_email" placeholder="New Email" required>
                                    <label for="edit_password">New Password</label>
                                    <input type="password" id="edit_password" name="password" placeholder="New Password (Optional)">
                                    <label for="edit_confirm_password">Confirm Password</label>
                                    <input type="password" id="edit_confirm_password" name="confirm_password" placeholder="Confirm New Password">
                                    <p id="error_edit"><?php if (isset($error_edit_message)) echo $error_edit_message; ?></p><br><br>
                                    <center>
                                    <button class="btn btn-add" type="submit">Update Account</button>
                                    <button class="btn btn-edit" type="button" id="editBtn2">Cancel</button>
                                    </center>
                                </form>
                            </div>
                        </div>


<div class="main">
    <h1>Account Management Page</h1>
    <div class="card">
        <h2>Add user account</h2>
        <form method="post" action="account_page.php" enctype="multipart/form-data" onsubmit="return checkPassword()">
            <label for="add_image_upload" class="fontlabel">Upload Image (Profile)</label>
            <input type="file"     name="Image"     id="add_image_upload" required>
            <label class="fontlabel">Fullname</label>
            <input type="text"     name="fullname"  placeholder=" Enter your Full Name" required>
            <label class="fontlabel">Username</label>
            <input type="text"     name="username"  placeholder="Username" required>
            <label class="fontlabel">Email Address</label>
            <input type="text"     name="email"     placeholder="Enter your Email address" required>
            <label class="fontlabel">Password</label>
            <input type="password" name="password"  id="password" placeholder="Enter your Password" required>
            <label class="fontlabel">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <p id="error"><?php if (isset($error_add_message)) echo $error_add_message; ?></p>
            <button class="btn btn-add" type="submit">Add user</button>
        </form>
    </div>



    <div class="card">
       <h2>User Accounts</h2>
       <table class="table">
            <tr>
                <th>User ID</th>
                <th>Profile</th>
                <th>Fullname</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
             </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td align="center"><?= htmlspecialchars($row['user_id']) ?></td>
                <td align="center">
                <img src="data:image/jpeg;base64,<?= base64_encode($row['profile']) ?>" width="60" height="60" style="border-radius: 50%;">
                </td>
                <td align="center"><?= htmlspecialchars($row['fullname']) ?></td>
                <td align="center"><?= htmlspecialchars($row['username']) ?></td>
                <td align="center"><?= htmlspecialchars($row['email']) ?></td>
                <td align="center">
                    <button class="btn btn-edit editBtn" 
                        data-id="<?= htmlspecialchars($row['user_id']) ?>"
                        data-fullname="<?= htmlspecialchars($row['fullname']) ?>"
                        data-username="<?= htmlspecialchars($row['username']) ?>"
                        data-email="<?= htmlspecialchars($row['email']) ?>">
                        Edit
                    </button>
                   
                    <a href="account_page.php?delete=<?= $row['user_id'] ?>" 
                       class="btn btn-delete"
                       onclick="return confirm('Delete this account?')">
                       Delete
                    </a>
                </td>
             </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>