<?php
session_start();
include('db.php'); // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['patient_id'])) {
    header("Location: doctor_dashboard.php");
    exit();
}

$patient_id = $_GET['patient_id'];

// Fetch patient details
$patient_stmt = $pdo->prepare("SELECT * FROM patients WHERE id = :patient_id AND doctor_id = :doctor_id");
$patient_stmt->bindParam(':patient_id', $patient_id);
$patient_stmt->bindParam(':doctor_id', $_SESSION['user_id']);
$patient_stmt->execute();
$patient = $patient_stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo "Patient not found or you do not have permission to view this patient.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - Foot Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Patient Details</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($patient['name']); ?></h5>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                <p class="card-text"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                <p class="card-text"><strong>Medical Records:</strong></p>
                <!-- You can add more patient-specific details here -->
            </div>
        </div>
        <a href="doctor_dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
