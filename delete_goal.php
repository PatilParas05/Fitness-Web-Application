<?php
session_start();
include './conn.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: goals.php?error=No_goal_specified.");
    exit();
}

$userID = $_SESSION['user_id'];
$goalID = $_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM `tbl_goal` WHERE `goal_id` = :goal_id AND `tbl_user_id` = :user_id");
    $stmt->bindParam(':goal_id', $goalID);
    $stmt->bindParam(':user_id', $userID);
    
    if ($stmt->execute()) {
        header("Location: goals.php?message=Goal_deleted_successfully!");
    } else {
        header("Location: goals.php?error=Failed_to_delete_goal.");
    }
} catch (PDOException $e) {
    error_log("Error deleting goal: " . $e->getMessage());
    header("Location: goals.php?error=Database_error.");
}
exit();
