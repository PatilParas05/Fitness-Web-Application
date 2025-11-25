<?php
session_start();
include('./conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        header("Location: index.php?error=All_fields_required");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?error=Invalid_email");
        exit();
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO tbl_contact (name, email, message) VALUES (:name, :email, :message)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);
        $stmt->execute();
        
        header("Location: index.php?message=Message_sent_successfully");
        exit();
    } catch (PDOException $e) {
        error_log("Contact Form Error: " . $e->getMessage());
        header("Location: index.php?error=Failed_to_send_message");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>