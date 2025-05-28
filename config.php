<?php
    // $dbhost = "localhost";
    // $dbuser = "root";
    // $dbpass = "";
    // $dbname = "it_signature";

$dbhost = "sql209.infinityfree.com";
$dbuser = "if0_38891070";
$dbpass = "ACLjmxbu4Q1HdP";
$dbname = "if0_38891070_it_signature";

try {
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?> 