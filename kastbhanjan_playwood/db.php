<?php
$host = 'localhost';
$db_name = 'playwood_db';
$username = 'root'; // Change if needed
$password = ''; // Change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
