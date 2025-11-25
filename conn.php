<?php

$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "beast_fitness_db"; 

$conn = null; 
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
   
    echo "<div style='color: red; text-align: center; padding: 15px; border: 1px solid red; background-color: #ffe0e0;'>
            <h2>Database Connection Error!</h2>
            <p>We are currently experiencing technical difficulties. Please try again later.</p>
          </div>";
    exit(); 
}
?>
