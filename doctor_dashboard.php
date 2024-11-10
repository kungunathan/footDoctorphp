<?php
// Include the database connection
include('db.php');
session_start();

// Assuming the doctor is logged in with their user_id
$user_id = $_SESSION['user_id']; 

// Function to check if a column exists in a table
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0; // Returns true if column exists
}

// Fetch the doctor's profile details
if (columnExists($pdo, 'doctors', 'user_id')) {
    $query = "SELECT * FROM doctors WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $doctor = $stmt->fetch();
} else {
    $doctor = [];
    $error_doctor = "Doctor profile information is missing.";
}

// Fetch patients assigned to the doctor
if (columnExists($pdo, 'patient_doctors', 'doctor_id')) {
    $query = "SELECT p.name, p.email, p.phone_number, pd.patient_id FROM patient_doctors pd JOIN patients p ON pd.patient_id = p.user_id WHERE pd.doctor_id = :doctor_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doctor_id', $user_id);
    $stmt->execute();
    $patients = $stmt->fetchAll();
} else {
    $patients = [];
    $error_patients = "No patients assigned yet.";
}

// Fetch the earnings from bills
if (columnExists($pdo, 'bills', 'doctor_id')) {
    $query = "SELECT SUM(amount) AS total_earnings FROM bills WHERE doctor_id = :doctor_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doctor_id', $user_id);
    $stmt->execute();
    $earnings = $stmt->fetch();
} else {
    $earnings = [];
    $error_earnings = "Unable to calculate earnings.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
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
            <h4 class="text-center">Doctor Menu</h4>
            <a href="doctor_dashboard.php">Dashboard</a>
            <a href="doctor_dashboard.php#patients">My Patients</a>
            <a href="doctor_dashboard.php#earnings">Earnings</a>
            <a href="doctor_dashboard.php#profile">Profile</a>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- My Patients Section -->
            <div id="patients" class="section-content">
                <h2 class="section-title">My Patients</h2>
                <?php if (isset($error_patients)): ?>
                    <div class="alert alert-danger"><?php echo $error_patients; ?></div>
                <?php elseif (count($patients) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($patients as $patient): ?>
                            <li class="list-group-item">
                                <strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?> <br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?> <br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone_number']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No patients assigned. Please accept patient requests.</p>
                <?php endif; ?>
            </div>

            <!-- Earnings Section -->
            <div id="earnings" class="section-content">
                <h2 class="section-title">Earnings</h2>
                <?php if (isset($error_earnings)): ?>
                    <div class="alert alert-danger"><?php echo $error_earnings; ?></div>
                <?php else: ?>
                    <p><strong>Total Earnings: </strong>$<?php echo number_format($earnings['total_earnings'], 2); ?></p>
                <?php endif; ?>
            </div>

            <!-- Profile Section -->
            <div id="profile" class="section-content">
                <h2 class="section-title">Profile</h2>
                <?php if (isset($doctor['name'])): ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($doctor['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone_number']); ?></p>
                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <p><strong>Profile Picture:</strong> <img src="uploads/<?php echo htmlspecialchars($doctor['profile_picture']); ?>" alt="Profile Picture" style="width: 100px; height: 100px;"></p>
                <?php else: ?>
                    <p>No profile information found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
