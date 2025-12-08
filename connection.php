<?php
require 'vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';  // Load the Composer autoloader
// Include JWT library
use Dotenv\Dotenv;
// Load env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Database connection settings
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname  = "chias_corner";
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname  = $_ENV['DB_NAME'];



// Create connection
$connect = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);


// Set the PDO error mode  to exception
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
