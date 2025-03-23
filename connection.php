<?php

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname  = "chias_corner";



// Create connection
$connect = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);


// Set the PDO error mode  to exception
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>
