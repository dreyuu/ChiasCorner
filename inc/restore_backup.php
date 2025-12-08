<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["status" => "error", "message" => "Invalid request method"]);
  exit;
}

if (!isset($_FILES['backupFile']) || $_FILES['backupFile']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(["status" => "error", "message" => "No file uploaded or upload error"]);
  exit;
}

$fileTmpPath = $_FILES['backupFile']['tmp_name'];
$sqlContent = file_get_contents($fileTmpPath);

try {
  if (stripos($sqlContent, "CREATE TABLE") === false) {
    echo json_encode(["status" => "error", "message" => "Invalid backup file — no CREATE TABLE statements found"]);
    exit;
  }

  $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Disable foreign key checks
  $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
  $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `$table`;");
  }

  // Split SQL by semicolon safely
  $statements = array_filter(array_map('trim', explode(";", $sqlContent)));
  foreach ($statements as $stmt) {
    if ($stmt !== '') {
      $pdo->exec($stmt);
    }
  }

  // Re-enable FK checks
  $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

  echo json_encode(["status" => "success", "message" => "Database successfully restored!"]);
} catch (PDOException $e) {
  echo json_encode(["status" => "error", "message" => "Restore failed: " . $e->getMessage()]);
}
