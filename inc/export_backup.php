<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

$date = date('Y-m-d_H-i-s');
$backupFile = "backup_{$database}_{$date}.sql";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  $sqlScript = "-- Backup of database `$database`\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
  $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
  
  foreach ($tables as $table) {
    // Get table structure
    $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $sqlScript .= "-- ----------------------------\n";
    $sqlScript .= "-- Table structure for `$table`\n";
    $sqlScript .= "-- ----------------------------\n";
    $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
    $sqlScript .= $createTable['Create Table'] . ";\n\n";

    // Get table data
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0) {
      $columns = array_map(fn($col) => "`$col`", array_keys($rows[0]));
      $sqlScript .= "-- ----------------------------\n";
      $sqlScript .= "-- Dumping data for table `$table`\n";
      $sqlScript .= "-- ----------------------------\n";
      foreach ($rows as $row) {
        $values = array_map([$pdo, 'quote'], array_values($row));
        $sqlScript .= "INSERT INTO `$table` (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ");\n";
      }
      $sqlScript .= "\n";
    }
  }

  $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

  // Output as download
  header('Content-Type: application/sql');
  header("Content-Disposition: attachment; filename=\"$backupFile\"");
  header('Pragma: no-cache');
  header('Expires: 0');
  echo $sqlScript;
} catch (Exception $e) {
  echo "❌ Backup failed: " . $e->getMessage();
}
