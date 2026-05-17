<?php
session_start();
require "connection.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_owner'])) {

    $fullname    = $_POST['owner_name'] ?? '';
    $course      = $_POST['course'] ?? '';
    $year_level  = $_POST['year_level'] ?? '';
    $address     = $_POST['address'] ?? '';
    $email       = $_POST['email'] ?? '';
    $contact     = $_POST['contact'] ?? '';
    $age         = $_POST['age'] ?? '';

    if (!isset($_SESSION['user_id'])) {
        die("Error: No user ID in session. Login required.");
    }

    $user_id = $_SESSION['user_id'];

    $sql_add = "INSERT INTO owner_info 
                (fullname, course, year_level, address, email, contact, age, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_add);
    $stmt->bind_param("ssssssii", $fullname, $course, $year_level, $address, $email, $contact, $age, $user_id
    );

    if ($stmt->execute()) {
        header("Location: dashboard.php?added=1");
        exit;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_owner'])) {

    $id         = $_POST['owners_id'];
    $fullname   = $_POST['fullname'];
    $course     = $_POST['course'];
    $year_level = $_POST['year_level'];
    $address    = $_POST['address'];
    $email      = $_POST['email'];
    $contact    = $_POST['contact'];
    $age        = $_POST['age'];

    $sql_update = "UPDATE owner_info 
                   SET fullname=?, course=?, year_level=?, address=?, email=?, contact=?, age=? 
                   WHERE owners_id=?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssssii", $fullname, $course, $year_level, 
        $address, $email, $contact, $age, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?updated=1");
        exit;
    }
    $stmt->close();
}


$sql = "SELECT owners_id, fullname, course, year_level, address, email, contact, age 
        FROM owner_info";
$result = $conn->query($sql);

//bilang ng mga user
$countUsers = $conn->query("SELECT COUNT(*) AS total FROM account_management_page");
$totalUsers = $countUsers->fetch_assoc()['total'];

//bilang ng mga reg owner
$countOwners = $conn->query("SELECT COUNT(*) AS total FROM owner_info");
$totalOwners = $countOwners->fetch_assoc()['total'];

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM owner_info WHERE owners_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: dashboard.php?deleted=1");
    exit;
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

<style>
#popupForm, #addOwnerForm {
    display: none;
}   
.btn{
    
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin:3px;
}

</style>

</head>
<body>

<div class="sidebar">
    <center><img src="assets/logccmadi.png" width="60px">
    <h1>CCMADI</h1>
    <h2>Vehicle RRMS</h2></center>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="account_page.php">Account Management Page</a>
    <a href="vehicles.php">Vehicle Records</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php" onclick="return confirm('Logout your account?')">Logout</a>
</div> 



 <div class="container">
    <div class="card_edit" id="popupForm">
        <h2>Edit Owner Information</h2>
        <form method="post" id="fontsize">
            <input type="hidden" name="edit_owner" value="1">
            <input type="hidden" name="owners_id" id="edit_id">
            <label for="edit_fullname">Fullname</label>
            <input type="text" id="edit_fullname" name="fullname" required>
            <label for="edit_course">Course</label>
            <input type="text" id="edit_course" name="course" required>
            <label for="edit_year">Year level</label>
            <input type="text" id="edit_year" name="year_level" required>
            <label for="edit_address">Address</label>
            <input type="text" id="edit_address" name="address" required>
            <label for="edit_email">Email</label>
            <input type="email" id="edit_email" name="email" required>
            <label for="edit_contact">Contact</label>
            <input type="text" id="edit_contact" name="contact" required>
            <label for="edit_age">Age</label>
            <input type="number" id="edit_age" name="age" required>
            <center><button class="btn btn-add" type="submit">Update Owner</button>
           <button class="btn btn-edit" type="button" id="closeEdit">Cancel</button></center>
            
        </form>
        

    </div>
</div>


<div class="con">
    <div class="containfortotal">
        <h2 style="color:white;">Total <br>Accounts:</h2><br><br><br>
        <h1 style="color:white;"><?= $totalUsers ?></h1>
    </div>

    <div class="containfortotal">
        <h2 style="color:white;">Total Registered <br> Owner:</h2><br><br>
        <h1 style="color:white;"><?= $totalOwners ?></h1>
    </div>
</div>




<div class="main">
    <div id="add">
        <h1>Owners Information</h1>
        <button id="btnowner">Add Owner</button>
    </div>

    <!-- ADD OWNER POPUP -->
    <div class="card" id="addOwnerForm">
        <h2>Add Owner Information</h2>

        <form method="post">
            <input type="hidden" name="add_owner" value="1">
            <label class="fontlabel">Owner Name</label>
            <input type="text" name="owner_name" placeholder="Enter your Full Name" required>
            <label class="fontlabel">Course</label>
            <input type="text" name="course" placeholder="Enter your Course" required>
            <label class="fontlabel">Year Level</label>
            <input type="text" name="year_level" placeholder="Enter your Year Level" required>
            <label class="fontlabel">Address</label>
            <input type="text" name="address" placeholder="Enter your Address" required>
            <label class="fontlabel">Email</label>
            <input type="email" name="email" placeholder="Enter your Email address" required>
            <label class="fontlabel">Contact</label>
            <input type="text" name="contact" placeholder="Contact Number" required>
            <label class="fontlabel">Age</label>
            <input type="number" name="age" placeholder="Enter your Age" required>

            <button class="btn btn-add">Add Owner</button>
        </form>
        <button  class="btn btn-edit" id="closeAdd">Cancel</button>
    </div> <br>

  
 
   

    <!-- OWNERS TABLE -->

    <div class="card">
        <h2>Registered Owners</h2>
        <table class="table" border="1" width="100%">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Address</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Age</th>
                <th>Actions</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['owners_id'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['course'] ?></td>
                <td><?= $row['year_level'] ?></td>
                <td><?= $row['address'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['contact'] ?></td>
                <td><?= $row['age'] ?></td>
                <td>
                    <button class="btn btn-edit editBtn"
                        data-id="<?= $row['owners_id'] ?>"
                        data-fullname="<?= $row['fullname'] ?>"
                        data-course="<?= $row['course'] ?>"
                        data-year="<?= $row['year_level'] ?>"
                        data-address="<?= $row['address'] ?>"
                        data-email="<?= $row['email'] ?>"
                        data-contact="<?= $row['contact'] ?>"
                        data-age="<?= $row['age'] ?>"
                    >Edit</button>

                    <a href="dashboard.php?delete=<?= $row['owners_id'] ?>"
                     onclick="return confirm('Delete this owner?')">
                     <button class="btn btn-delete">Delete</button>
                     </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>



<script>
document.addEventListener("DOMContentLoaded", () => {

    document.getElementById("btnowner").onclick = () => {
        document.getElementById("addOwnerForm").style.display = "block";
    };

    document.getElementById("closeAdd").onclick = () => {
        document.getElementById("addOwnerForm").style.display = "none";
    };

    const popup = document.getElementById("popupForm");

    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", function() {

            document.getElementById("edit_id").value = this.dataset.id;
            document.getElementById("edit_fullname").value = this.dataset.fullname;
            document.getElementById("edit_course").value = this.dataset.course;
            document.getElementById("edit_year").value = this.dataset.year;
            document.getElementById("edit_address").value = this.dataset.address;
            document.getElementById("edit_email").value = this.dataset.email;
            document.getElementById("edit_contact").value = this.dataset.contact;
            document.getElementById("edit_age").value = this.dataset.age;
            popup.style.display = "block";
        });
    });

    document.getElementById("closeEdit").onclick = () => {
        popup.style.display = "none";
    };
});
</script>

</body>
</html>
