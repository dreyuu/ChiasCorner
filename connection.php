<?php
require 'vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname  = $_ENV['DB_NAME'];

$connect = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Database connection settings
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname  = "chias_corner";


 // Load the Composer autoloader
// Include JWT library


// Load env
