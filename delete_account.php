<?php
session_start();
include('db.php'); // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Handle account deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete associated data before deleting the user
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete patient's medical records if role is patient
        if ($user_role == 'patient') {
            $stmt = $pdo->prepare("DELETE FROM medical_records WHERE patient_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        }

        // Delete the user's chats
        $stmt = $pdo->prepare("DELETE FROM chats WHERE patient_id = :user_id OR doctor_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Delete the patient's or doctor's bills
        $stmt = $pdo->prepare("DELETE FROM bills WHERE patient_id = :user_id OR doctor_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Delete the patient or doctor request (if applicable)
        $stmt = $pdo->prepare("DELETE FROM patient_requests WHERE patient_id = :user_id OR doctor_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Delete user from the users table
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Commit transaction
        $pdo->commit();

        // Log the user out and redirect to the homepage
        session_destroy();
        header("Location: index.php"); // You can change this to any page you want after account deletion
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of an error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Foot Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Are you sure you want to delete your account?</h2>
    <p>This action cannot be undone. All your data will be permanently removed.</p>
    <form method="POST" action="">
        <button type="submit" class="btn btn-danger">Yes, Delete My Account</button>
        <a href="login.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
