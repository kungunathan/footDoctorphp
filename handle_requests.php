<?php
session_start();
include('db.php'); // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['action'], $_POST['patient_id'])) {
    $action = $_POST['action'];
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_SESSION['user_id'];

    if ($action == 'accept') {
        // Update the patient request to accepted
        $update_stmt = $pdo->prepare("UPDATE patient_requests SET status = 'accepted' WHERE id = :patient_id AND doctor_id = :doctor_id");
    } elseif ($action == 'reject') {
        // Update the patient request to rejected
        $update_stmt = $pdo->prepare("UPDATE patient_requests SET status = 'rejected' WHERE id = :patient_id AND doctor_id = :doctor_id");
    }

    $update_stmt->bindParam(':patient_id', $patient_id);
    $update_stmt->bindParam(':doctor_id', $doctor_id);
    $update_stmt->execute();

    header("Location: doctor_dashboard.php");
    exit();
}
?>

### **3. Improve `viewPatientDetails()` Function**

Update the `viewPatientDetails()` JavaScript function in `doctor_dashboard.php` to navigate properly:

```php
<script>
function viewPatientDetails(patientId) {
    window.location.href = 'view_patient.php?patient_id=' + patientId;
}
</script>
