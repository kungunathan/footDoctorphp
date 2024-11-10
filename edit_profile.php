<?php
session_start();
include('db.php'); // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

// Check if user exists
if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $profile_picture = $user['profile_picture']; // Keep the current profile picture unless it's updated

    // Validate form data
    if (empty($name) || empty($email) || empty($phone_number)) {
        echo "All fields are required.";
        exit;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Profile picture upload handling
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/profile_pictures/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        } else {
            echo "Failed to upload profile picture.";
            exit;
        }
    }

    // Update the user details in the database
    $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone_number = :phone_number, profile_picture = :profile_picture WHERE id = :user_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':profile_picture', $profile_picture);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        // Redirect to the appropriate dashboard based on user role
        if ($user['role'] === 'doctor') {
            header("Location: doctor_dashboard.php");
        } else {
            header("Location: patient_dashboard.php");
        }
        exit(); // Ensure no further code is executed
    } else {
        echo "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Foot Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Foot Doctor</a>
  <div class="ml-auto">
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="container mt-5">
    <h2>Edit Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="profile_picture" class="form-label">Profile Picture</label>
            <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="img-thumbnail mt-2" style="width: 150px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
    <a href="profile.php" class="btn btn-secondary mt-3">Back to Profile</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
