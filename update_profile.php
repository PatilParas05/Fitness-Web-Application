<?php
session_start();
// The include path has been corrected to 'conn.php'
include 'conn.php'; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

$userID = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contactNumber = $_POST['contact_number'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $height = $_POST['height'] ?? null;
    $birthday = $_POST['birthday'] ?? null;

    // A more robust check for required fields, allowing optional ones to be null
    if (empty($fullName) || empty($email)) {
        header("Location: profile.php?error=Full_name_and_email_are_required.");
        exit();
    }
    
    try {
        $stmt = $conn->prepare("UPDATE `tbl_user` SET `full_name` = :full_name, `email` = :email, `contact_number` = :contact_number, `weight` = :weight, `height` = :height, `birthday` = :birthday WHERE `tbl_user_id` = :user_id");
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_number', $contactNumber);
        $stmt->bindParam(':weight', $weight, PDO::PARAM_STR);
        $stmt->bindParam(':height', $height, PDO::PARAM_STR);
        $stmt->bindParam(':birthday', $birthday);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Success: redirect to profile page with a success message
            header("Location: profile.php?message=Profile_updated_successfully!");
        } else {
            // Failure: redirect to profile page with an error message
            header("Location: profile.php?error=Failed_to_update_profile.");
        }
    } catch (PDOException $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        header("Location: profile.php?error=Database_error.");
    }
} else {
    header("Location: profile.php?error=Invalid_request_method.");
}
exit();
