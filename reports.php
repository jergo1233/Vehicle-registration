<?php
require "connection.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$total = 0;
$active = 0;
$expired = 0;
$rows = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
 
    $start_year = $_POST['start_year']; 
    $end_year   = $_POST['end_year'];   
    
    $from = $_POST['from_date'];
    $to   = $_POST['to_date'];

    $sql = "SELECT vr.*, oi.fullname 
        FROM vehicle_records vr
        LEFT JOIN owner_info oi ON vr.owners_id = oi.owners_id
        WHERE vr.date_register BETWEEN ? AND ?
        ORDER BY vr.date_register ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $result = $stmt->get_result();
    $official_expiry_date = new DateTime($end_year . '-12-31');
    $report_end_date      = new DateTime($to);

    while ($row = $result->fetch_assoc()) {
        $total++;
        
        $registration_year = (int)date('Y', strtotime($row['date_register']));
       $row['expiry_date'] = $official_expiry_date->format('Y-m-d'); 
        
        if ($registration_year >= $start_year && 
            $registration_year <= $end_year &&
            $report_end_date <= $official_expiry_date) {
            
            $row['status'] = "Active";
            $active++;
        } else {
            $row['status'] = "Expired";
            $expired++;
        }
        
        $rows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Reports – Vehicle RMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link  rel="icon" href="assets/logccmadi.png">
</head>
<body>

<div class="sidebar">
    <center><img src="assets/logccmadi.png" alt="ccmadi" width="60px">
    <h1>CCMADI</h1>
    <h2>Vehicle RRMS</h2>
    </center>
    
    <a href="dashboard.php">Dashboard</a>
    <a href="account_page.php">Account Management Page</a>
    <a href="vehicles.php">Vehicle Records</a>
    <a href="reports.php" class="active">Reports</a>
    <a href="logout.php" onclick="return confirmLogout()">Logout</a>
</div>


<div class="main">
    <h1>Registered Vehicle Reports</h1>
    <div class="card">
        <h2>Generate Report</h2>
        <form method="post" action="reports.php">
            
            <label>Official Start Year:</label>
            <input type="number" name="start_year" min="2000" max="2100" 
                   value="<?= isset($start_year) ? $start_year : date('Y') ?>" required>

            <label>Official End Year:</label>
            <input type="number" name="end_year" min="2000" max="2100" 
                   value="<?= isset($end_year) ? $end_year : date('Y') + 1 ?>" required>
            
            <label>From Date (Report Filter):</label>
            <input type="date" name="from_date" value="<?= isset($from) ? $from : '' ?>" required>

            <label>To Date (Report Filter):</label>
            <input type="date" name="to_date" value="<?= isset($to) ? $to : '' ?>" required>

            <button class="btn btn-add">Generate</button>
        </form>
    </div>
    

    <div class="card">
        <h2>Report Summary</h2>

        <div class="summary-box">
            <p>Total Registered Vehicles:<?= $total ?></p>
            <p>Active Registrations:<?= $active ?></p>
            <p>Expired Registrations: <?= $expired ?></p>
        </div>
    </div>

    <div class="card">
        <h2>Detailed Vehicle Report</h2>

        <table class="table">
            <tr>
                <th>Report ID</th>
                <th>Plate No.</th>
                <th>Model</th>
                <th>Color</th>
                <th>Owner</th>
                <th>Date Registered</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
            <?php if ($total == 0): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= $row['plate_no'] ?></td>
                        <td><?= $row['model'] ?></td>
                        <td><?= $row['color'] ?></td>
                        <td><?= $row['fullname'] ?></td>
                        <td><?= $row['date_register'] ?></td>
                        <td><?= $row['expiry_date'] ?></td>
                        <td>
                            <?php if ($row['status'] == "Active"): ?>
                                <span class="status active">Active</span>
                            <?php else: ?>
                                <span class="status expired">Expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach;   ?>
            <?php endif; ?>

        </table>
    </div>

</div>

</body>
</html>