<?php
// Include the database connection
include('db.php');
session_start();

// Assuming the user_id is stored in the session after successful login
$user_id = $_SESSION['user_id'];

// Function to check if a column exists in a table
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0; // Returns true if column exists
}

// Fetch doctors assigned to the patient
if (columnExists($pdo, 'doctors', 'user_id')) {
    $query = "SELECT * FROM doctors WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
} else {
    $doctors = [];
    $error_doctors = "The required column 'user_id' is missing in the 'doctors' table.";
}

// Fetch medical records for the patient
if (columnExists($pdo, 'medical_records', 'user_id')) {
    $query = "SELECT * FROM medical_records WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $medical_records = $stmt->fetchAll();
} else {
    $medical_records = [];
    $error_medical = "The required column 'user_id' is missing in the 'medical_records' table.";
}

// Fetch bills for the patient
if (columnExists($pdo, 'bills', 'user_id')) {
    $query = "SELECT * FROM bills WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $bills = $stmt->fetchAll();
} else {
    $bills = [];
    $error_bills = "The required column 'user_id' is missing in the 'bills' table.";
}

// Add doctor logic
if (isset($_POST['add_doctor'])) {
    $doctor_id = $_POST['doctor_id'];

    // Check if the doctor is already assigned to the patient
    $check_query = "SELECT * FROM patient_doctors WHERE patient_id = :patient_id AND doctor_id = :doctor_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':patient_id', $user_id);
    $check_stmt->bindParam(':doctor_id', $doctor_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $error_message = "This doctor is already assigned to you.";
    } else {
        // Add the doctor to the patient-doctor relationship table
        $insert_query = "INSERT INTO patient_doctors (patient_id, doctor_id) VALUES (:patient_id, :doctor_id)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->bindParam(':patient_id', $user_id);
        $insert_stmt->bindParam(':doctor_id', $doctor_id);

        if ($insert_stmt->execute()) {
            $success_message = "Doctor successfully added to your list.";
        } else {
            $error_message = "There was an error adding the doctor. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles for Sidebar */
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background-color: #f8f9fa;
            padding-top: 20px;
            border-right: 1px solid #ddd;
            width: 250px; /* Set fixed width for sidebar */
        }

        .sidebar a {
            color: #333;
            text-decoration: none;
            padding: 10px;
            display: block;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #007bff;
            color: white;
        }

        /* Custom Styles for Content Area */
        .content-area {
            flex: 1;
            padding-left: 30px; /* Space for content */
        }

        .section-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .section-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .list-group-item {
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .alert-danger {
            font-weight: bold;
        }

        footer {
            margin-top: 30px;
        }

        /* Flexbox layout to align sidebar and content */
        .main-container {
            display: flex;
            flex-wrap: nowrap;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Foot Doctor</a>
            <div class="d-flex">
                <a class="btn btn-outline-primary" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Container with Sidebar and Content -->
    <div class="main-container">
        <!-- Sidebar Menu -->
        <div class="sidebar">
            <h4 class="text-center">Patient Menu</h4>
            <a href="patient_dashboard.php">Dashboard</a>
            <a href="patient_dashboard.php#doctors">Your Doctors</a>
            <a href="patient_dashboard.php#medical-records">Medical Records</a>
            <a href="patient_dashboard.php#bills">Bills</a>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Doctors Section -->
            <div id="doctors" class="section-content">
                <h2 class="section-title">Your Doctors</h2>
                <?php if (isset($error_doctors)): ?>
                    <div class="alert alert-danger"><?php echo $error_doctors; ?></div>
                <?php elseif (count($doctors) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($doctors as $doctor): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($doctor['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No doctors assigned. Please contact your healthcare provider.</p>
                <?php endif; ?>
                
                <h3>Add a Doctor</h3>
                <form action="patient_dashboard.php" method="POST">
                    <div class="mb-3">
                        <label for="doctor_id" class="form-label">Doctor ID</label>
                        <input type="text" class="form-control" id="doctor_id" name="doctor_id" required>
                    </div>
                    <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                </form>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success mt-3"><?php echo $success_message; ?></div>
                <?php elseif (isset($error_message)): ?>
                    <div class="alert alert-danger mt-3"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>

            <!-- Medical Records Section -->
            <div id="medical-records" class="section-content">
                <h2 class="section-title">Your Medical Records</h2>
                <?php if (isset($error_medical)): ?>
                    <div class="alert alert-danger"><?php echo $error_medical; ?></div>
                <?php elseif (count($medical_records) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($medical_records as $record): ?>
                            <li class="list-group-item">
                                <strong>Description:</strong> <?php echo htmlspecialchars($record['description']); ?> <br>
                                <strong>Date:</strong> <?php echo htmlspecialchars($record['date']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No medical records found. Please contact your doctor.</p>
                <?php endif; ?>
            </div>

            <!-- Bills Section -->
            <div id="bills" class="section-content">
                <h2 class="section-title">Your Bills</h2>
                <?php if (isset($error_bills)): ?>
                    <div class="alert alert-danger"><?php echo $error_bills; ?></div>
                <?php elseif (count($bills) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($bills as $bill): ?>
                            <li class="list-group-item">
                                <strong>Amount:</strong> $<?php echo htmlspecialchars($bill['amount']); ?> <br>
                                <strong>Description:</strong> <?php echo htmlspecialchars($bill['description']); ?> <br>
                                <strong>Status:</strong> <?php echo htmlspecialchars($bill['status']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No bills found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
