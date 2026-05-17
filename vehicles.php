<?php
session_start();
require "connection.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {

    $plate_no      = $_POST['plate_no'];
    $model         = $_POST['model'];
    $color         = $_POST['color'];
    $owner_name    = $_POST['owner'];
    $date_register = $_POST['date_register'];
    $expiry_date   = $_POST['expiry_date'];
    $payment       = $_POST['payment'];
    $owners_id     = $_POST['owners_id'];

    $sql = "INSERT INTO vehicle_records 
            (plate_no, model, color, owner, date_register, expiry_date, payment, owners_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssii",
        $plate_no,
        $model,
        $color,
        $owner_name,
        $date_register,
        $expiry_date,
        $payment,
        $owners_id
    );

    if ($stmt->execute()) {
        header("Location: vehicles.php?added=1");
        exit();
    }

    echo "Error: " . $stmt->error;
    $stmt->close();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_owner'])) {
    
    $register_id      = $_POST['register_id'];
    $plate_no         = $_POST['plate_no'];
    $model            = $_POST['model'];
    $color            = $_POST['color'];
    $owner_name       = $_POST['owner'];
    $date_register    = $_POST['date_register'];
    $expiry_date      = $_POST['expiry_date'];
    $payment          = $_POST['payment'];

    $sql_renew = "UPDATE vehicle_records 
                   SET plate_no=?, model=?, color=?, owner=?, date_register=?, expiry_date=?, payment=? 
                   WHERE register_id=?";

    $stmt = $conn->prepare($sql_renew);
    $stmt->bind_param(
        "ssssssii",
        $plate_no,
        $model,
        $color,
        $owner_name,
        $date_register,
        $expiry_date,
        $payment,
        $register_id
    );

    if ($stmt->execute()) {
        header("Location: vehicles.php?updated=1");
        exit;
    }

    $stmt->close();
}


if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM vehicle_records WHERE register_id = '$delete_id'");
    header("Location: vehicles.php?deleted=1");
    exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Records – Vehicle RMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/logccmadi.png">
</head>
<body>
<style>

#alignment{
   position: fixed;
   margin-top:5px;
}
.card_edit{
     height: 70%;
}
#btngap{
    gap:10px;
    display: flex;
}

</style>

<div class="sidebar">
    <center>
        <img src="assets/logccmadi.png" alt="ccmadi" width="60px">
        <h1>CCMADI</h1>
        <h2>Vehicle RRMS</h2>
    </center>

    <a href="dashboard.php">Dashboard</a>
    <a href="account_page.php">Account Management Page</a>
    <a href="vehicles.php" class="active">Vehicle Records</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>



<!-- POPUP FORM -->
<div class="container"> 
    <div class="card_edit" id="popupForm"> 
        <h2>Registration Renewal</h2> 
         
        <form method="post" action="vehicles.php">
            <input type="hidden" name="renew_owner" value="1">
            <input type="hidden" name="register_id" id="edit_register_id">

            <label for="edit_plate_no" class="fontlabel">Plate Number</label>
            <input type="text" name="plate_no" id="edit_plate_no" placeholder="Plate no." required> 
            <label for="edit_model" class="fontlabel">Model</label>
            <input type="text" name="model" id="edit_model" placeholder="Model" required> 
            <label for="edit_color" class="fontlabel">Color</label>
            <input type="text" name="color" id="edit_color" placeholder="Colors" required> 
            <label for="edit_owner_name" class="fontlabel">Owner Name</label>
            <input type="text" name="owner" id="edit_owner_name" placeholder="Owner Name" required>
            <label for="edit_date_registration" class="fontlabel">Date Register</label> 
            <input type="date" name="date_register" id="edit_date_registration" required> 
            <label for="edit_expiry_date" class="fontlabel">Expiry Date </label> 
            <input type="date" name="expiry_date" id="edit_expiry_date" required> 
             <label for="edit_payment" class="fontlabel">Payment </label> 
            <input type="text" name="payment" id="edit_payment" placeholder="Payment" required> 
            <center><button class="btn btn-add" type="submit">Renew</button> 
            <button class="btn btn-edit" id="closeEdit" type="button" >Cancel</button>
      </center>
        </form> 
        
            
            
    </div> 
</div>


<div class="main">
    <h1>Manage Vehicle Records</h1>
    <!-- ADD VEHICLE -->
    <div class="card">
        <h2>Add New Vehicle</h2>

        <form method="post">
            <input type="hidden" name="add_vehicle" value="1">

            <label class="fontlabel">Vehicle Plate Number</label>
            <input type="text" name="plate_no" placeholder="Plate Number" required>
            <label class="fontlabel">Vehicle Model</label>
            <input type="text" name="model" placeholder="Model" required>
            <label class="fontlabel">Vehicle Color</label>
            <input type="text" name="color" placeholder="Color" required>

            <label class="fontlabel">Select Owner</label>
            <select name="owners_id" id="ownerSelect" required>
                <option value="">Select Owner</option>

                <?php
                $owners = $conn->query("SELECT owners_id, fullname FROM owner_info ORDER BY fullname DESC");
                while ($o = $owners->fetch_assoc()):
                ?>
                    <option value="<?= $o['owners_id'] ?>"><?= $o['fullname'] ?></option>
                <?php endwhile; ?>

            </select>

            <input type="hidden" name="owner" id="ownerName">

            <label class="fontlabel">Date Registered</label>
            <input type="date" name="date_register" required>

            <label class="fontlabel">Expiry Date</label>
            <input type="date" name="expiry_date" required>
            <label class="fontlabel">Payment</label>
            <input type="number" name="payment" placeholder="Payment" required>
            <button class="btn btn-add">Add Vehicle</button>
        </form>
    </div>

    <div class="card">
        <h2>Registered Vehicles</h2>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Plate No</th>
                <th>Model</th>
                <th>Color</th>
                <th>Owner</th>
                <th>Date Registered</th>
                <th>Expiry Date</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>

            <?php
            $records = $conn->query("SELECT * FROM vehicle_records ORDER BY register_id DESC");
            while ($row = $records->fetch_assoc()):
            ?>

            <tr>
                <td><?= $row['register_id'] ?></td>
                <td><?= $row['plate_no'] ?></td>
                <td><?= $row['model'] ?></td>
                <td><?= $row['color'] ?></td>
                <td><?= $row['owner'] ?></td>
                <td><?= $row['date_register'] ?></td>
                <td><?= $row['expiry_date'] ?></td>
                <td><?= $row['payment'] ?></td>
                <td id="btngap">
                    <button class="btn btn-edit editBtn"
                        data-register_id="<?= $row['register_id'] ?>"
                        data-plate_no="<?= $row['plate_no'] ?>"
                        data-model="<?= $row['model'] ?>"
                        data-color="<?= $row['color'] ?>"
                        data-owner="<?= $row['owner'] ?>"
                        data-date_register="<?= $row['date_register'] ?>"
                        data-expiry_date="<?= $row['expiry_date'] ?>"
                        data-payment="<?= $row['payment'] ?>"
                    >Renew</button>
                    <a href="vehicles.php?delete=<?= $row['register_id'] ?>" 
                       class="btn btn-delete"
                       onclick="return confirm('Delete this record?')">
                       Delete
                    </a>
                </td>
            </tr>

            <?php endwhile; ?>
        </table>
    </div>
</div>



<!--JAVASCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("popupForm");

    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", function() {
            
            document.getElementById("edit_register_id").value = this.dataset.register_id ;
            document.getElementById("edit_plate_no").value = this.dataset.plate_no;
            document.getElementById("edit_model").value = this.dataset.model;
            document.getElementById("edit_color").value = this.dataset.color;
            document.getElementById("edit_owner_name").value = this.dataset.owner;
            document.getElementById("edit_date_registration").value = this.dataset.date_register;
            document.getElementById("edit_expiry_date").value = this.dataset.expiry_date;
            document.getElementById("edit_payment").value = this.dataset.payment;
            
            popup.style.display = "block";
        });
    });

    document.getElementById("closeEdit").onclick = () => {
        popup.style.display = "none";
    };

    document.getElementById("ownerSelect").addEventListener("change", function() {
    let selectedText = this.options[this.selectedIndex].text;
    document.getElementById("ownerName").value = selectedText;
});
});
</script>

</body>
</html>
